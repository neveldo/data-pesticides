<?php

namespace Neveldo\DataPesticides\Triplestore;

/**
 * Class Client
 * Triplestore Client
 * @package Neveldo\DataPesticides\Triplestore
 */
class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $sparqlEndpoint;

    /**
     * @var string
     */
    private $responseFormat;

    /**
     * @var array
     */
    private $namespaces = [
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
    ];

    /**
     * Client constructor.
     * @param $sparqlEndpoint
     * @param string $responseFormat
     */
    public function __construct($sparqlEndpoint, $responseFormat = "text/csv")
    {
        $this->sparqlEndpoint = $sparqlEndpoint;
        $this->responseFormat = $responseFormat;
    }

    /**
     * Execute a query
     * @param $statement (ex : "select * where {?s ?p ?o}")
     * @return false|string
     */
    public function query($statement)
    {
        $prefixes = "";
        foreach($this->namespaces as $prefix => $uri) {
            $prefixes .= "prefix " . $prefix . ": <" . $uri . ">\n";
        }
        return $this->rawExec('query=' . urlencode($prefixes.$statement));
    }

    /**
     * Execute an update
     * @param $statement (ex : "CLEAR GRAPH <http://mygraph>")
     * @return false|string
     */
    public function update($statement)
    {
        return $this->rawExec('update=' . urlencode($statement));
    }

    /**
     * Load a file into a graph of the triplestore
     * @param $filepath
     * @param $destinationGraph
     * @return false|string
     */
    public function loadFile($filepath, $destinationGraph)
    {
        return $this->update(sprintf('LOAD <file://%s> INTO GRAPH <%s>', $filepath, $destinationGraph));
    }

    /**
     * Clear a graph from the triplestore
     * @param $graph
     * @return mixed
     */
    public function clearGraph($graph)
    {
        return $this->update(sprintf('CLEAR GRAPH <%s>', $graph));
    }

    /**
     * Run the query against the sparql endpoint
     * @param $queryString (for instane : "query=select%20*%20where%20%7B%3Fs%20%3Fp%20%3Fo%7D"
     * @return false|string
     */
    public function rawExec($queryString)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->sparqlEndpoint);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Accept: " . $this->responseFormat]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec ($curl);
        $error = curl_error($curl);

        if ($error !== '') {
            Throw new \Exception($error);
        }

        curl_close ($curl);

        return $response;
    }

    /**
     * Register namespaces to be used within the queries, for instance :
     * [
     *   'myns' => 'http://www.example.com/mynamespace#',
     *   ...
     * ]
     * @param array $namespaces
     * @return $this
     */
    public function registerNamespaces(array $namespaces)
    {
        $this->namespaces = array_merge($this->namespaces, $namespaces);
        return $this;
    }
}