<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;

use Neveldo\DataPesticides\Dataset\Triple;
use Cocur\Slugify\Slugify;

/**
 * Class PesticideTriplesConverter
 * Handle triples conversion for pesticides dataset
 * @package Neveldo\DataPesticides\Dataset\Formatter
 */
class PesticideTriplesConverter implements TriplesConverterInterface
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

        $entity = 'dpd:pesticide-' . $this->slugifier->slugify($data[0]);

        $triples[] = new Triple(
            $entity,
            'rdf:type',
            'dpo:Pesticide',
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

        if (isset($data[2]) && $data[2] !== null && $data[2] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:secondaryLabel',
                $data[2]
            );
        }

        // Handle pesticide family name
        if (isset($data[3]) && $data[3] !== null && $data[3] !== '') {

            // Sanitize pesticide family labels
            if ($data[3] === 'Carbamate') {
                $data[3] = 'Carbamates';
            }

            if ($data[3] === 'Indices') {
                $data[3] = 'Inconnu';
            }

            $pesticideFamilyEntity = 'dpd:pesticide-family-' . $this->slugifier->slugify($data[3]);

            $triples[] = new Triple(
                $entity,
                'dpo:relatedFamily',
                $pesticideFamilyEntity,
                Triple::TYPE_RESOURCE
            );

            $triples[] = new Triple(
                $pesticideFamilyEntity,
                'rdf:type',
                'dpo:PesticideFamily',
                Triple::TYPE_RESOURCE
            );

            $triples[] = new Triple(
                $pesticideFamilyEntity,
                'rdfs:label',
                $data[3]
            );
        }

        // Handle pesticide role
        if (isset($data[4]) && $data[4] !== null && $data[4] !== '') {

            $rolePesticideEntity = 'dpd:pesticide-role-' . $this->slugifier->slugify($data[4]);

            $triples[] = new Triple(
                $entity,
                'dpo:relatedRole',
                $rolePesticideEntity,
                Triple::TYPE_RESOURCE
            );
        }

        if (isset($data[5]) && $data[5] !== null && $data[5] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:status',
                $data[5]
            );
        }

        // end of usage date
        if (isset($data[10]) && $data[10] !== null && $data[10] !== '') {
            if (preg_match('#[0-9]{2}/[0-9]{2}/[0-9]{4}#', $data[10])) {
                $date = \DateTime::createFromFormat('d/m/Y', $data[10]);
            } else {
                $date = \DateTime::createFromFormat('Y', $data[10]);
            }

            if ($date) {
                $date->setTime(12, 0, 0);
                $triples[] = new Triple(
                    $entity,
                    'dpo:endOfUsageDate',
                    $date->format('Y-m-d\TH:i:s\Z'),
                    Triple::TYPE_DATETIME
                );
            }
        }

        if (isset($data[12]) && $data[12] !== null && $data[12] !== '') {
            $triples[] = new Triple(
                $entity,
                'dpo:normalValue',
                (float) str_replace(',', '.', $data[12]),
                Triple::TYPE_DOUBLE
            );
        }

        return $triples;
    }
}