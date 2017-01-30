<?php

namespace Neveldo\DataPesticides\Triplestore;

/**
 * Interface ClientInterface
 * Triplestore Client interface
 * @package Neveldo\DataPesticides\Triplestore
 */
interface ClientInterface
{
    /**
     * Execute a query
     * @param $statement (ex : "select * where {?s ?p ?o}")
     * @return false|string
     */
    public function query($statement);

    /**
     * Execute an update
     * @param $statement (ex : "CLEAR GRAPH <http://mygraph>")
     * @return false|string
     */
    public function update($statement);

    /**
     * Load a file into a graph of the triplestore
     * @param $filepath
     * @param $destinationGraph
     * @return false|string
     */
    public function loadFile($filepath, $destinationGraph);

    /**
     * Clear a graph from the triplestore
     * @param $graph
     * @return mixed
     */
    public function clearGraph($graph);

    /**
     * Run the query against the sparql endpoint
     * @param $queryString (for instane : "query=select%20*%20where%20%7B%3Fs%20%3Fp%20%3Fo%7D"
     * @return false|string
     */
    public function rawExec($queryString);

    /**
     * Register namespaces to be used within the queries, for instance :
     * [
     *   'myns' => 'http://www.example.com/mynamespace#',
     *   ...
     * ]
     * @param array $namespaces
     * @return $this
     */
    public function registerNamespaces(array $namespaces);
}