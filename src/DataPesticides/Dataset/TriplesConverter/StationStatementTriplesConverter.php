<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;

/**
 * Class StationStatementTriplesConverter
 * Handle triples conversion for station statements dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class StationStatementTriplesConverter implements TriplesConverterInterface
{
    /**
     * @var Slugify
     */
    private $slugifier;

    /**
     * @var int year related to the dataset
     */
    private $year;

    /**
     * @var string year related to the dataset (full datetime format)
     */
    private $date;

    /**
     * StationStatementTriplesConverter constructor.
     * @param string $year year related to the dataset
     */
    public function __construct($year)
    {
        $this->slugifier = new Slugify();
        $this->year = $year;
        $this->date = $this->year . '-01-01T12:00:00Z';
    }
    /**
     * take as argument a misc array from a dataset of station statements
     * and return an array of Triple
     * @param array $data
     * @return Triple[]
     */
    public function convert(array $data)
    {
        $triples = [];
        if (!isset($data[0]) || $data[0] === null || $data[0] === '' // station code
            || !isset($data[1]) || $data[1] === null || $data[1] === '' // pesticide code
        ) {
            return $triples;
        }

        $entity = 'dpd:StationStatement-' . $this->slugifier->slugify($data[0])
            . '-' . $this->slugifier->slugify($data[1]) . '-' . $this->year;

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:StationStatement',
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:relatedStation',
            'dpd:station-' . $this->slugifier->slugify($data[0]),
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:relatedPesticide',
            'dpd:pesticide-' . $this->slugifier->slugify($data[1]),
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:year',
            $this->date,
            Triple::TYPE_DATETIME
        );

        if (isset($data[2]) && $data[2] !== null && $data[2] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:analyzesCount',
                (int) $data[2],
                Triple::TYPE_INT
            );
        }

        if (isset($data[3]) && $data[3] !== null && $data[3] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:averageConcentration',
                (float) str_replace(',', '.', $data[3]),
                Triple::TYPE_DOUBLE
            );
        }

        return $triples;
    }
}