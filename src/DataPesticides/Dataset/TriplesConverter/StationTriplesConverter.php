<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;
use proj4php\Point;
use proj4php\Proj;
use proj4php\Proj4php;

/**
 * Class StationTriplesConverter
 * Handle triples conversion for stations dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class StationTriplesConverter implements TriplesConverterInterface
{
    /**
     * @var Slugify
     */
    private $slugifier;

    /**
     * @var Proj4php
     */
    private $proj4;

    /**
     * @var Proj
     */
    private $projL93;

    /**
     * @var Proj
     */
    private $projWGS84;

    public function __construct()
    {
        $this->slugifier = new Slugify();

        $this->proj4 = new Proj4php();
        $this->projL93    = new Proj('EPSG:2154', $this->proj4);
        $this->projWGS84  = new Proj('EPSG:4326', $this->proj4);
    }
    /**
     * take as argument a misc array from a dataset of stations
     * and return an array of Triple
     * @param array $data
     * @return Triple[]
     */
    public function convert(array $data)
    {
        $triples = [];

        if (!isset($data[0]) || $data[0] === null || $data[0] === '') {
            return $triples;
        }

        $entity = 'dpd:station-' . $this->slugifier->slugify($data[0]);

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:Station',
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:code',
            $data[0]
        );

        if (isset($data[1]) && $data[1] !== null && $data[1] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:relatedCity',
                'dpd:city-' . $data[1],
                Triple::TYPE_RESOURCE
            );
        }

        if (isset($data[2]) && $data[2] !== null && $data[2] !== '') {
            $triples[] = new Triple(
                $entity,
                'rdfs:label',
                ucwords(strtolower($data[2]))
            );
        }

        if (isset($data[3]) && $data[3] !== null && $data[3] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:relatedDepartment',
                'dpd:department-' . $data[3],
                Triple::TYPE_RESOURCE
            );
        }

        if (isset($data[5]) && $data[5] !== null && $data[5] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:altitude',
                (float) str_replace(',', '.', $data[5]),
                Triple::TYPE_DOUBLE
            );
        }

        // Handle station latitude and longitude
        if (isset($data[8]) && $data[8] !== null && $data[8] !== '' // x
            && isset($data[9]) && $data[9] !== null && $data[9] !== '' // y
        ) {

            $x = (float) str_replace(',', '.', $data[8]);
            $y = (float) str_replace(',', '.', $data[9]);

            $wgs84Coordinatges = $this->proj4->transform($this->projWGS84, new Point($x, $y, $this->projL93))->toArray();

            $triples[] = new Triple(
                $entity,
                'dpo:longitude',
                $wgs84Coordinatges[0],
                Triple::TYPE_DOUBLE
            );

            $triples[] = new Triple(
                $entity,
                'dpo:latitude',
                $wgs84Coordinatges[1],
                Triple::TYPE_DOUBLE
            );
        }

        return $triples;
    }
}