<?php

namespace Neveldo\DataPesticides\Dataset\Writer;

use Neveldo\DataPesticides\Dataset\Triple;

/**
 * Interface TurtleWriterInterface
 * Allow to write RDF triples into a dataset
 * @package Neveldo\DataPesticides\Dataset\Writer
 */
interface WriterInterface
{
    /**
     * Open the file  in order to write RDF triples
     * @throws \InvalidArgumentException if the file does not exist or can't be open
     * @return mixed
     */
    public function open();

    /**
     * Close the file and free resources
     */
    public function close();

    /**
     * Write an array of triples into the destination file
     * @param Triple[] $triples
     */
    public function write(array $triples);

    /**
     * @param array $namespaces
     */
    public function registerNamespaces(array $namespaces);

    /**
     * @return string dataset filepath
     */
    public function getFilepath();
}