<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;

/**
 * Class RoleTriplesConverter
 * Handle triples conversion for roles dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class RoleTriplesConverter implements TriplesConverterInterface
{
    /**
     * @var Slugify
     */
    private $slugifier;

    public function __construct()
    {
        $this->slugifier = new Slugify();
    }
    /**
     * take as argument a misc array from a dataset of pesticides
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

        $entity = 'dpd:pesticide-role-' . $this->slugifier->slugify($data[0]);

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:PesticideRole',
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
                'rdfs:label',
                $data[1]
            );
        }

        return $triples;
    }
}