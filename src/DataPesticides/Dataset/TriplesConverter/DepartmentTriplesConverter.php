<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;

/**
 * Class DepartmentTriplesConverter
 * Handle triples conversion for departments dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class DepartmentTriplesConverter implements TriplesConverterInterface
{
    /**
     * @var Slugify
     */
    private $slugifier;

    /**
     * DepartmentTriplesConverter constructor.
     */
    public function __construct()
    {
        $this->slugifier = new Slugify();
    }
    /**
     * take as argument a misc array from a dataset of departments
     * and return an array of Triple
     * @param array $data
     * @return Triple[]
     */
    public function convert(array $data)
    {
        $triples = [];
        if (!isset($data[1]) || $data[1] === null || $data[1] === ''
        ) {
            return $triples;
        }

        $entity = 'dpd:department-' . $data[1];

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:Department',
            Triple::TYPE_RESOURCE
        );

        $triples[] = new Triple(
            $entity,
            'dpo:insee',
            $data[1]
        );

        if (isset($data[0]) && $data[0] !== null && $data[0] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:relatedRegion',
                'dpd:region-' . $data[0],
                Triple::TYPE_RESOURCE
            );
        }

        if (isset($data[5]) && $data[5] !== null && $data[5] !== '') {
            $triples[] = new Triple(
                $entity,
                'rdfs:label',
                $data[5]
            );
        }

        return $triples;
    }
}