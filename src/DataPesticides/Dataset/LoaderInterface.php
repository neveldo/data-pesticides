<?php

namespace Neveldo\DataPesticides\Dataset;

/**
 * Interface LoaderInterface
 * Load a dataset into a triplestore
 * @package Neveldo\DataPesticides\Dataset
 */
interface LoaderInterface
{
    /**
     * Run the load of the dataset
     * @param $graphPrefix The graph prefix to build the full graph URI in which the triples will be imported
     * @param bool $replaceGraph if true, the method will drop the existing graph before loading the
     * new one
     * @return array
     */
    public function load($graphPrefix, $replaceGraph = true);
}