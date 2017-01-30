<?php

namespace Neveldo\DataPesticides\Dataset;

use Cocur\Slugify\Slugify;
use Neveldo\DataPesticides\Dataset\TriplesConverter\TriplesConverterInterface;
use Neveldo\DataPesticides\Dataset\Reader\ReaderInterface;
use Neveldo\DataPesticides\Dataset\Writer\WriterInterface;
use Neveldo\DataPesticides\Triplestore\Client;

/**
 * Class Loader
 * Load a dataset into a triplestore
 * @package Neveldo\DataPesticides\Dataset
 */
class Loader implements LoaderInterface
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var TriplesConverterInterface
     */
    private $triplesConverter;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var Client
     */
    private $triplestoreClient;

    public function __construct(
        ReaderInterface $reader,
        WriterInterface $writer,
        TriplesConverterInterface $triplesConverter,
        Client $triplestoreClient
    ) {
        $this->triplesConverter = $triplesConverter;
        $this->reader = $reader;
        $this->writer = $writer;
        $this->triplestoreClient = $triplestoreClient;
    }

    /**
     * Run the load of the dataset
     * @param $graphPrefix The graph prefix to build the full graph URI in which the triples will be imported
     * @param bool $replaceGraph if true, the method will drop the existing graph before loading the
     * new one
     * @return array load report infos
     */
    public function load($graphPrefix, $replaceGraph = true)
    {
        $this->reader->open();
        $this->writer->open();
        $triplesCount = 0;

        $first = true;
        while(false !== ($row = $this->reader->read())) {

            // Ignore first head line
            if ($first) {
                $first = false;
                continue;
            }

            $triples = $this->triplesConverter->convert($row);
            $this->writer->write($triples);

            $triplesCount += count($triples);
        }

        $this->reader->close();
        $this->writer->close();

        $slugifier = new Slugify();
        $destGraph = $graphPrefix . $slugifier->slugify(pathinfo($this->writer->getFilepath())['filename']);

        $deletedTriples = 0;
        if ($replaceGraph) {
            // Delete previous existing graph
            try {
                $response = $this->triplestoreClient->clearGraph($destGraph);
            } catch(\Exception $e) {
                return ['error' => $e->getMessage()];
            }

            preg_match("/mutationCount=(\\d+)/", $response, $matches);
            $deletedTriples = $matches[1];
        }

        // Load data
        try {
            $response = $this->triplestoreClient->loadFile(
                $this->writer->getFilepath(),
                $destGraph
            );
        } catch(\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        preg_match("/mutationCount=(\\d+)/", $response, $matches);
        $insertedTriples = $matches[1];

        return [
            'rdf_triples' => $triplesCount,
            'deleted_triples' => $deletedTriples,
            'inserted_triples' => $insertedTriples,
            'destination_file' => $this->writer->getFilepath(),
            'destination_graph' => $destGraph,
            'error' => null,
        ];
    }

}