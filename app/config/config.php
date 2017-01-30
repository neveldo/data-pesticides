<?php

return [
    /**
     * SPARQL endpoint URL used for initializing Triplestore\Client
     */
    'sparql_endpoint_url' => 'http://localhost:9999/blazegraph/namespace/datapesticides/sparql',

    /**
     * Graph prefix used to defined destination graphs when importing triples into the triplestore
     * (by using Triplestore\Client::loadGraph() method)
     */
    'graph_prefix' => 'http://www.data-pesticides.fr/graph/',

    /**
     * Namespaces to be used within the turtle datasets and when querying data through the client
     */
    'rdf_namepaces' => [
        'dpo' => 'http://www.data-pesticides.fr/ontology/',
        'dpd' => 'http://www.data-pesticides.fr/data/',
    ],

    /**
     * Host of the application (used in the warm-up API command)
     */
    'host' => 'http://local.data-pesticides.fr'
];