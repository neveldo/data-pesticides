<?php

namespace Neveldo\DataPesticides\Controller;

use Neveldo\DataPesticides\Triplestore\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ApiController
 * Data API for needs of the data-visualisation
 * @package Neveldo\DataPesticides\Controller
 */
class ApiController extends Controller
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Client sparql endpoint client
     */
    private $endpointClient;

    /**
     * @var array authorised GET parameters on API queries
     */
    private $authorizedOptions = ['id'];

    /**
     * ApiController constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->endpointClient = new Client($this->app['sparql_endpoint_url'], 'text/csv');
        $this->endpointClient->registerNamespaces($this->app['rdf_namepaces']);
    }

    /**
     * Return data as a JSON collection to the client
     * @param Request $request
     * @param $action
     * @return Response
     */
    public function apiAction(Request $request, $action)
    {
        // Check if the API method exists
        $method = 'api' . ucfirst($action);
        if (!method_exists($this, $method)) {
            throw new NotFoundHttpException('This API method is not available');
        }

        $response = new Response();
        $response->setPublic();

        $options = array_intersect_key($request->query->all(), array_flip($this->authorizedOptions));

        $cache = new FilesystemAdapter('datapesticides_api', 0, $this->app['app.rootdir'] . '/var/cache');
        $cachekey = $method . '?' . http_build_query($options);

        // Return a 304 response to the client if the content have not been modified
        $lastModified = $cache->getItem('lastmodified_' . $cachekey);
        if ($lastModified->isHit() && !$request->query->has('refresh')) {
            $response->setLastModified($lastModified->get());

            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $data = $cache->getItem($cachekey);

        // Retrieve the data from the triplestore if there are not file-cached
        if (!$data->isHit() || $request->query->has('refresh')) {
            set_time_limit(800);

            $data->set($this->{$method}($request->query->all()));
            $cache->save($data);

            $lastModified->set(new \DateTime());
            $cache->save($lastModified);
        }

        $response->setContent($data->get());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Return the list of all years of data
     * @param array $options
     * @return string
     */
    protected function apiGetYears(array $options)
    {
        $query =
            "SELECT distinct ?year
            WHERE {
              ?statement a dpo:StationStatement .
              ?statement dpo:year ?yearDate .
              BIND(year(?yearDate) as ?year) .
            }
            ORDER BY ?year";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data, false);
        $data = array_column($data, 0);

        return json_encode($data);
    }

    /**
     * Return the collection of pesticides stations with their label, coords, etc
     * @param array $options
     * @return string
     */
    protected function apiGetStations(arra $options)
    {
        $query =
            "SELECT 
              (replace(STR(?station), 'http://www.data-pesticides.fr/data/', '') as ?key)
              (CONCAT(
                '{\"label\":\"', ?label, '\",',
                '\"code\":\"', STR(?code), '\",',
                '\"latitude\":', STR(?latitude), ',',
                '\"longitude\":', STR(?longitude), ',',
                '\"relatedDepartment\":\"', replace(STR(?department), 'http://www.data-pesticides.fr/data/', ''), '\",',
                '\"departmentLabel\":\"', ?departmentLabel, '\"}'
              ) as ?data)
            WHERE {
              ?station a dpo:Station ;
                rdfs:label ?label ;
                dpo:latitude ?latitude ;
                dpo:code ?code ;
                dpo:longitude ?longitude ;
                dpo:relatedDepartment ?department .
              ?department rdfs:label ?departmentLabel .
            }";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data);

        return json_encode($data);
    }

    /**
     * Return the collection of french departments
     * @param array $options
     * @return string
     */
    protected function apiGetDepartments(array $options)
    {
        $query =
            "SELECT 
              (replace(STR(?department), 'http://www.data-pesticides.fr/data/', '') as ?key)
              (CONCAT(
                '{\"label\":\"', ?label, '\",',
                '\"insee\":\"', ?insee, '\"}'
              ) as ?data)
            WHERE {
              ?department a dpo:Department ;
                rdfs:label ?label ;
                dpo:insee ?insee .
            }";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data);

        return json_encode($data);
    }

    /**
     * Return the collection of pesticide families
     * @param array $options
     * @return string
     */
    protected function apiGetFamilies(array $options)
    {
        $query =
            "SELECT 
              (replace(STR(?family), 'http://www.data-pesticides.fr/data/', '') as ?key)
              (CONCAT(
                '{\"label\":\"', STR(MAX(?label)), '\"}'
              ) as ?data)
            WHERE {
              ?family a dpo:PesticideFamily ;
                rdfs:label ?label .
              ?pesticide a dpo:Pesticide ;
               dpo:relatedFamily ?family .
              ?statement a dpo:StationStatement ;
               dpo:relatedPesticide ?pesticide
            }
            GROUP BY ?family";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data);
        asort($data);

        // Remove this family from the list as there is only one station with data ...
        unset($data['pesticide-family-autres-elements-mineraux']);

        return json_encode($data);
    }

    /**
     * Return the total pesticides concentrations indexed by station and year
     * @param array $options
     * @return string
     */
    protected function apiGetTotalConcentrationsByStation(array $options)
    {
        $query =
            "SELECT 
            (replace(STR(?station), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT(
                '\"', 
                STR(year(?year)), 
                '\":{\"allFamilies\":',
                STR(round(1000*?concentrationTotal)/1000),
                '}'
              ); separator=\",\")
              , '}'
            ) as ?data) 
            WHERE {
              {
                SELECT ?station (SUM(?concentration) as ?concentrationTotal) ?year
                WHERE {
                  ?station a dpo:Station .
                  ?statement a dpo:StationStatement ;
                    dpo:year ?year ;
                    dpo:relatedStation ?station ;
                    dpo:averageConcentration ?concentration .
                }
                GROUP BY ?station ?year
              }
            }
            GROUP BY ?station";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data, true);
        $metadata = $this->getMetadata($data, ['allFamilies']);

        return json_encode(['metadata' => $metadata, 'data' => $data]);
    }

    /**
     * Return the list of stations where concentrations exceed the maximum authorized values (for specific pesticides
     * or for the total), with the related abnormal pesticides, indexed by station and year
     * @param array $options
     * @return string
     */
    protected function apiGetPesticidesInExcessByStation(array $options)
    {
        // Retrieve the stations where concentrations exceed the maximum authorized values for specific pesticides
        $query =
            "SELECT 
            (replace(STR(?station), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT(
                '\"', 
                STR(year(?year)), 
                '\":{\"pesticidesInExcess\":{', 
                ?pesticidesInExcess, 
                '}}'
              ); separator=','),
              '}'
            ) as ?data) 
            WHERE {
              {
                SELECT ?station ?year
                (GROUP_CONCAT(CONCAT(
                   '\"', 
                   ?labelPestide, 
                   '\":{\"concentration\":', 
                   STR(?concentration), 
                   ',', 
                   '\"code\":\"', 
                   STR(?code), '\"}'
                ); separator=',') as ?pesticidesInExcess)
                WHERE {
                  ?station a dpo:Station .
                  ?statement a dpo:StationStatement ;
                  dpo:year ?year ;
                  dpo:relatedStation ?station ;
                  dpo:averageConcentration ?concentration ;
                  dpo:relatedPesticide ?pesticide .
                  FILTER(?concentration >= 0.1 || (?concentration >= 0.03 && ?pesticide IN(
                    dpd:pesticide-1103, 
                    dpd:pesticide-1173, 
                    dpd:pesticide-1197, 
                    dpd:pesticide-1748, 
                    dpd:pesticide-1749, 
                    dpd:pesticide-1198, 
                    dpd:pesticide-91198
                  ))) .
                  ?pesticide rdfs:label ?labelPestide ;
                    dpo:code ?code .
                }
                GROUP BY ?station ?year
              }
            }
            GROUP BY ?station";

        $pesticides = $this->endpointClient->query($query);
        $pesticides = $this->csvToCollection($pesticides);

        // Retrieve the stations where concentrations exceed the maximum authorized values for the total concentration
        $query =
            "SELECT 
            (replace(STR(?station), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT('\"', STR(year(?year)), '\":', STR(?concentration)); separator=','),
              '}'
            ) as ?data)
            WHERE {
              ?station a dpo:Station .
              ?statement a dpo:StationStatementTotal ;
              dpo:year ?year ;
              dpo:relatedStation ?station ;
              dpo:totalConcentration ?concentration ;
              FILTER(?concentration >= 0.5) .
            }
            GROUP BY ?station";

        $totalConcentrations = $this->endpointClient->query($query);
        $totalConcentrations = $this->csvToCollection($totalConcentrations);

        $stationInExcess = [];

        // Merge both queries results
        foreach ($totalConcentrations as $station => $stationData) {
            foreach ($stationData as $year => $value) {
                if (!isset($stationInExcess[$year])) {
                    $stationInExcess[$year] = [];
                }
                $stationInExcess[$year][$station] = true;
            }
        }

        foreach ($pesticides as $station => $stationData) {
            foreach ($stationData as $year => $value) {
                if (!isset($stationInExcess[$year])) {
                    $stationInExcess[$year] = [];
                }
                $stationInExcess[$year][$station] = true;
            }
        }

        $counts = [];
        foreach ($stationInExcess as $year => $stations) {
            $counts[$year] = count($stations);
        }

        ksort($counts);

        return json_encode([
            'counts' => $counts,
            'pesticides' => $pesticides,
            'totalConcentrations' => $totalConcentrations
        ]);
    }

    /**
     * Return total pesticides concentrations indexed by department and year
     * @param array $options
     * @return string
     */
    protected function apiGetTotalConcentrationsByDepartment(array $options)
    {
        $query =
            "SELECT
            (replace(STR(?department), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT(
                '\"', 
                STR(year(?year)), 
                '\":{\"allFamilies\":', 
                STR(round(1000*?concentrationTotal)/1000),
                '}'
              ); separator=\",\"),
              '}'
            ) as ?data) 
            WHERE {
              {
                SELECT ?department ?year (AVG(?concentrationTotal) as ?concentrationTotal)
                WHERE {
                  {
                    SELECT ?department ?station (SUM(?concentration) as ?concentrationTotal) ?year
                    WHERE {
                      ?station a dpo:Station .
                      ?station dpo:relatedDepartment ?department .
                      ?statement a dpo:StationStatement .
                      ?statement dpo:year ?year .
                      ?statement dpo:relatedStation ?station .
                      ?statement dpo:averageConcentration ?concentration .
                    }
                    GROUP BY ?department ?station ?year
                  }
                }
                GROUP BY ?department ?year
              }
            }
            GROUP BY ?department";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data);
        $metadata = $this->getMetadata($data, ['allFamilies']);

        return json_encode(['metadata' => $metadata, 'data' => $data]);
    }

    /**
     * Return pesticides concentrations indexed by pesticide family, station and year
     * @param array $options
     * @return string
     */
    protected function apiGetConcentrationsByFamilyAndStation(array $options)
    {
        $query =
            "SELECT
            (replace(STR(?station), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT('\"', STR(year(?year)), '\":{', ?concentrationTotalByFamily, '}'); separator=\",\"),
              '}'
            ) as ?data) 
            WHERE {
              {
                SELECT 
                  ?station 
                  ?year 
                  (GROUP_CONCAT(CONCAT(
                    '\"', 
                    replace(STR(?family), 'http://www.data-pesticides.fr/data/', ''), '\":', 
                    STR(round(1000*?concentrationTotal)/1000)
                  ); separator=\",\") as ?concentrationTotalByFamily)
                WHERE {
                  {
                    SELECT ?station ?family ?year (SUM(?concentration) as ?concentrationTotal)
                    WHERE {
                      ?station a dpo:Station .
                      ?statement a dpo:StationStatement ;
                        dpo:year ?year ;
                        dpo:relatedStation ?station ;
                        dpo:averageConcentration ?concentration ;
                        dpo:relatedPesticide ?pesticide .
                      ?pesticide dpo:relatedFamily ?family .
                    }
                    GROUP BY ?station ?family ?year
                  }
                }
                GROUP BY ?station ?year
              }
            }
            GROUP BY ?station";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data, true);

        $metadata = $this->getMetadata($data, array_keys(json_decode($this->apiGetFamilies([]), true)));

        return json_encode(['metadata' => $metadata, 'data' => $data]);
    }

    /**
     * Return pesticides concentrations indexed by pesticide family, department and year
     * @param array $options
     * @return string
     */
    protected function apiGetConcentrationsByFamilyAndDepartment(array $options)
    {
        $query =
            "SELECT
            (replace(STR(?department), 'http://www.data-pesticides.fr/data/', '') as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT('\"', STR(year(?year)), '\":{', ?concentrationTotalByFamily, '}'); separator=\",\"),
              '}'
            ) as ?data) 
            WHERE {
              {
                SELECT 
                  ?department 
                  ?year 
                  (GROUP_CONCAT(CONCAT(
                    '\"', 
                    replace(STR(?family), 'http://www.data-pesticides.fr/data/', ''), '\":', 
                    STR(round(1000*?concentrationTotal)/1000)
                  ); separator=\",\") as ?concentrationTotalByFamily)
                WHERE {
                  {
                    SELECT ?department ?family ?year (AVG(?concentrationTotal) as ?concentrationTotal)
                    WHERE {
                      {
                        SELECT ?department ?station ?family ?year (SUM(?concentration) as ?concentrationTotal)
                        WHERE {
                          ?station a dpo:Station ;
                            dpo:relatedDepartment ?department .
                          ?statement a dpo:StationStatement ;
                            dpo:year ?year ;
                            dpo:relatedStation ?station ;
                            dpo:averageConcentration ?concentration ;
                            dpo:relatedPesticide ?pesticide .
                          ?pesticide dpo:relatedFamily ?family .
                        }
                        GROUP BY ?department ?station ?family ?year
                      }
                    }
                    GROUP BY ?department ?family ?year
                  }
                }
                GROUP BY ?department ?year
              }
            }
            GROUP BY ?department";

        $data = $this->endpointClient->query($query);
        $data = $this->csvToCollection($data);

        $metadata = $this->getMetadata($data, array_keys(json_decode($this->apiGetFamilies([]), true)));

        return json_encode(['metadata' => $metadata, 'data' => $data]);
    }

    /**
     * Return global country data indexed by year
     * @param array $options
     * @return string
     */
    protected function apiGetCountryData(array $options)
    {
        // Get total concentrations by year
        $query =
            "SELECT
            (?country as ?key)
            (CONCAT('{', GROUP_CONCAT(CONCAT(
                '\"', 
                STR(year(?year)), 
                '\":{\"allFamilies\":', 
                STR(round(1000*?concentrationTotal)/1000), '}'
                ); separator=\",\"), '}') as ?data) 
            WHERE {
              {
                SELECT ?country ?year (AVG(?concentrationTotal) as ?concentrationTotal)
                WHERE {
                  {
                    SELECT ?country ?station (SUM(?concentration) as ?concentrationTotal) ?year
                    WHERE {
                      ?station a dpo:Station .
                      BIND('country-fra' as ?country) .
                      ?statement a dpo:StationStatement .
                      ?statement dpo:year ?year .
                      ?statement dpo:relatedStation ?station .
                      ?statement dpo:averageConcentration ?concentration .
                    }
                    GROUP BY ?country ?station ?year
                  }
                }
                GROUP BY ?country ?year
              }
            }
            GROUP BY ?country";

        $total = $this->endpointClient->query($query);
        $total = $this->csvToCollection($total);

        $years = json_decode($this->apiGetYears([]), true);

        // Query template used to retrieve top 5 most polluted station and bottom 5
        $topMainQueryTemplate =
            "SELECT 
              (?year as ?key)
              (CONCAT('{', GROUP_CONCAT(CONCAT(
                '\"', 
                replace(STR(?station), 'http://www.data-pesticides.fr/data/', ''), 
                '\":', 
                STR(round(1000*?concentration)/1000)); separator=','), 
                '}'
              ) as ?data)
            WHERE {
              %subqueries%
            }
            GROUP BY ?year";

        $topSubQueryTemplate =
            "{
              SELECT ?station (\"%year%\" as ?year) (SUM(?concentration) as ?concentration)
              WHERE {
                ?station a dpo:Station .
                ?statement a dpo:StationStatement .
                ?statement dpo:year \"%year%-01-01T12:00:00.000Z\"^^xsd:dateTime . 
                ?statement dpo:relatedStation ?station .
                ?statement dpo:averageConcentration ?concentration .
              }
              GROUP BY ?station
              ORDER BY %order%(SUM(?concentration))
              LIMIT 5
              }";

        // Get top 5 most polluted stations by year
        $query = [];
        foreach ($years as $year) {
            $query[] = strtr($topSubQueryTemplate, ['%year%' => $year, '%order%' => 'DESC']);
        }
        $query = str_replace('%subqueries%', implode(' UNION ', $query), $topMainQueryTemplate);
        $top5 = $this->endpointClient->query($query);
        $top5 = $this->csvToCollection($top5);

        // Get top 5 least polluted stations by year
        $query = [];
        foreach ($years as $year) {
            $query[] = strtr($topSubQueryTemplate, ['%year%' => $year, '%order%' => 'ASC']);
        }
        $query = str_replace('%subqueries%', implode(' UNION ', $query), $topMainQueryTemplate);
        $bottom5 = $this->endpointClient->query($query);
        $bottom5 = $this->csvToCollection($bottom5);

        // Get average pesticides concentration by family
        $query =
            "SELECT
            (?country as ?key)
            (CONCAT(
              '{',
              GROUP_CONCAT(CONCAT('\"', STR(year(?year)), '\":{', ?concentrationTotalByFamily, '}'); separator=\",\"),
              '}'
            ) as ?data) 
            WHERE {
              {
                SELECT 
                  ?country 
                  ?year 
                  (GROUP_CONCAT(CONCAT(
                    '\"', 
                    replace(STR(?family), 
                    'http://www.data-pesticides.fr/data/', ''), 
                    '\":', 
                    STR(round(1000*?concentrationTotal)/1000)
                  ); separator=\",\") as ?concentrationTotalByFamily)
                WHERE {
                  {
                    SELECT ?country ?family ?year (AVG(?concentrationTotal) as ?concentrationTotal)
                    WHERE {
                      {
                        SELECT ?country ?station ?family ?year (SUM(?concentration) as ?concentrationTotal)
                        WHERE {
                          ?station a dpo:Station .
                          BIND('country-fra' as ?country)
                          ?statement a dpo:StationStatement ;
                            dpo:year ?year ;
                            dpo:relatedStation ?station ;
                            dpo:averageConcentration ?concentration ;
                            dpo:relatedPesticide ?pesticide .
                          ?pesticide dpo:relatedFamily ?family .
                        }
                        GROUP BY ?country ?station ?family ?year
                      }
                    }
                    GROUP BY ?country ?family ?year
                  }
                }
                GROUP BY ?country ?year
              }
            }
            GROUP BY ?country";

        $families = $this->endpointClient->query($query);
        $families = $this->csvToCollection($families);

        // Get stations counts by year
        $query =
            "SELECT (year(?year) as ?key) (CONCAT('{\"value\":', STR(count(distinct ?station)), '}') as ?stationsCount) 
            WHERE {
              ?station a dpo:Station .
              ?statement a dpo:StationStatement ;
                dpo:year ?year ;
                dpo:relatedStation ?station ;
            }
            GROUP BY ?year
            ORDER BY ?year";

        $stationsCount = $this->endpointClient->query($query);
        $stationsCount = $this->csvToCollection($stationsCount);

        // Get pesticide analyzes counts by year
        $query =
            "SELECT (year(?year) as ?key) (CONCAT('{\"value\":', STR(SUM(?analyzesCount)), '}') as ?analyzesCount) 
            WHERE {
              ?statement a dpo:StationStatement ;
                dpo:year ?year ;
                dpo:relatedStation ?station ;
                dpo:analyzesCount ?analyzesCount .
            }
            GROUP BY ?year";

        $analyzesCount = $this->endpointClient->query($query);
        $analyzesCount = $this->csvToCollection($analyzesCount);

        return json_encode([
            'total' => $total,
            'top5' => $top5,
            'bottom5' => $bottom5,
            'families' => $families,
            'stationsCount' => $stationsCount,
            'analyzesCount' => $analyzesCount,
        ]);
    }

    /**
     * Proxy API to query sandre XML data API and returns JSON data
     * @param $options
     * @return string
     */
    protected function apiGetSandreData(array $options)
    {
        if (!isset($options['id']) || !is_numeric($options['id'])) {
            return json_encode([]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://id.eaufrance.fr/gpr/" . (int) $options['id'] . ".xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $output = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            return json_encode([]);
        }

        return json_encode(simplexml_load_string($output));
    }

    /**
     * Return metadata for a collection of results, such as min value, max, quartiles, ...
     * @param $data the data collection to handle
     * @param array $families array of families name to handle
     * @return array metadata info
     */
    protected function getMetadata($data, array $families)
    {
        $metadata = [];

        foreach ($families as $family) {
            $familyData = [];

            // Index data by year / entity id (can be a department or a station)
            foreach ($data as $id => $row) {
                foreach ($row as $year => $familiesData) {
                    if (isset($familiesData[$family])) {
                        $familyData[$year][$id] = $familiesData[$family];
                    }
                }
            }

            // Compute min, max, count and median for the pesticide family
            foreach ($familyData as $year => $row) {
                asort($familyData[$year]);
                $sortedValues = array_values($familyData[$year]);
                $sortedEntities = array_keys($familyData[$year]);

                $total = count($sortedValues);

                $quartiles = [
                    $sortedValues[ceil($total * 0.25) - 1],
                    $sortedValues[ceil($total * 0.50) - 1],
                    $sortedValues[ceil($total * 0.75) - 1],
                ];
                $quartiles = array_unique($quartiles);
                sort($quartiles);

                $slices = [
                    ['max' => $quartiles[0]]
                ];

                if (($nbQuartiles = count($quartiles)) > 1) {
                    for ($i = 1; $i < $nbQuartiles; $i++) {
                        $slices[] = ['min' => $quartiles[$i - 1], 'max' => $quartiles[$i]];
                    }
                }

                if ($sortedValues[$total - 1] > $quartiles[$nbQuartiles - 1]) {
                    $slices[] = ['min' => $quartiles[$nbQuartiles - 1]];
                }

                $metadata[$family][$year] = [
                    'min' => $sortedValues[0],
                    'max' => $sortedValues[$total - 1],
                    'minEntity' => $sortedEntities[0],
                    'maxEntity' => $sortedEntities[$total - 1],
                    'count' => $total,
                    'median' => $sortedValues[ceil($total * 0.50) - 1],
                    'slices' => $slices
                ];
            }
        }
        return $metadata;
    }

    /**
     * Return an array of data from the CSV dataset return by a sparql query
     * The CSV should contains only two columns : the key, and value
     * @param string $csvData
     * @param bool $shuffled shuffle the result collection.
     * @param bool $json true if the value column contains json data
     * @return array
     */
    protected function csvToCollection($csvData, $json = true, $shuffled = false)
    {
        $data = [];
        $separator = "\r\n";

        $line = strtok($csvData, $separator);
        $firstLine = true;
        while ($line !== false) {
            if (!$firstLine) {
                $line = str_getcsv($line);

                if ($json) {
                    $data[$line[0]] = json_decode($line[1], true);
                } else {
                    $data[$line[0]] = $line[1];
                }
            }

            $firstLine = false;
            $line = strtok($separator);
        }

        if ($shuffled) {
            $data = $this->shuffleCollection($data);
        }

        return $data;
    }

    /**
     * Return an array where the values have been shuffled (it preserve the original keys)
     * @param array $collection
     * @return array
     */
    function shuffleCollection(array $collection)
    {

        $keys = array_keys($collection);
        shuffle($keys);
        $shuffledCollection = [];
        foreach ($keys as $key) {
            $shuffledCollection[$key] = $collection[$key];
        }
        return $shuffledCollection;
    }


}