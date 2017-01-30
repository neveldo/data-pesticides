<?php

namespace Neveldo\DataPesticides\Dataset\TriplesConverter;
use Neveldo\DataPesticides\Dataset\Triple;

/**
 * Interface TriplesConverterInterface
 * Allow to convert array of data into array of RDF triples
 * @package Neveldo\DataPesticides\Dataset\TriplesConverter
 */
interface TriplesConverterInterface
{
    /**
     * take as argument a misc array and return an array of Triple
     * @param array $data
     * @return Triple[]
     */
    public function convert(array $data);
}