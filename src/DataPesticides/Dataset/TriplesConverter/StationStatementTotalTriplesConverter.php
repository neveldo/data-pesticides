<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;

/**
 * Class StationStatementTotalTriplesConverter
 * Handle triples conversion for station statements (total concentration of pesticides) dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class StationStatementTotalTriplesConverter implements TriplesConverterInterface
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
     * StationStatementTotalTriplesConverter constructor.
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
        if (!isset($data[1]) || $data[1] === null || $data[1] === '' // station code
        ) {
            return $triples;
        }

        $entity = 'dpd:StationStatementTotal-' . $this->slugifier->slugify($data[1])
            . '-total-' . $this->year;

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:StationStatementTotal',
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:relatedStation',
            'dpd:station-' . $this->slugifier->slugify($data[1]),
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
                'dpo:totalConcentration',
                (float) str_replace(',', '.', $data[3]),
                Triple::TYPE_DOUBLE
            );
        }

        return $triples;
    }
}