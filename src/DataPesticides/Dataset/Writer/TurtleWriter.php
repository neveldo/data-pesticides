<?php

namespace Neveldo\DataPesticides\Dataset\Writer;

use Neveldo\DataPesticides\Dataset\Triple;

/**
 * Class TurtleWriter
 * Allow to write RDF triples into an RDF file at Turtle Format (.ttl)
 * Read more about the Turtle serialization : https://www.w3.org/TR/turtle/
 * @package Neveldo\DataPesticides\Dataset\Writer
 */
class TurtleWriter implements WriterInterface
{
    /**
     * @var string
     */
    private $filepath;

    /**
     * @var resource
     */
    private $file = null;

    /**
     * @var array
     */
    private $namespaces = [
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
    ];

    /**
     * TurtleWriter constructor.
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function open()
    {
        if ($this->file !== null) {
            $this->close();
        }

        if (file_exists($this->filepath)
            && !is_writable($this->filepath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" is not writable.', $this->filepath));
        }

        $this->file = fopen($this->filepath, 'w');

        // Write RDF namespaces at the top of the dataset
        foreach($this->namespaces as $namespace => $uri) {
            fwrite($this->file, sprintf("@prefix %s: <%s> .\n", $namespace, $uri));
        }

        fwrite($this->file, "\n");
    }

    /**
     * Close the file and free resources
     */
    public function close()
    {
        if ($this->file !== null) {
            fclose($this->file);
            $this->file = null;
        }
    }

    /**
     * Write a set of Triple objects into the dataset
     * @param Triple[] $triples
     */
    public function write(array $triples)
    {
        foreach($triples as $triple) {

            $subject = $triple->getSubject();
            if (!$this->isNamespaced($subject)) {
                $subject = '<' . $subject . '>';
            }

            $predicate = $triple->getPredicate();
            if (!$this->isNamespaced($predicate)) {
                $predicate = '<' . $predicate . '>';
            }

            $object = $triple->getObject();

            switch($triple->getType()) {
                case Triple::TYPE_STRING:
                    $object = '"' . $object . '"';
                    break;
                case Triple::TYPE_RESOURCE:
                    if (!$this->isNamespaced($subject)) {
                        $object = "<" . $object . ">";
                    }
                    break;
                case Triple::TYPE_DATE:
                    $object = '"' . $object . '"^^xsd:date';
                    break;
                case Triple::TYPE_DATETIME:
                    $object = '"' . $object . '"^^xsd:dateTime';
                    break;
            }

            fwrite($this->file, sprintf("%s %s %s .\n", $subject, $predicate, $object));
        }
        fwrite($this->file, "\n");
    }

    /**
     * Return true if the URI is namesacped (ie : rdfs:label), false if not
     * @param $uri
     * @return bool
     */
    private function isNamespaced($uri)
    {
        foreach($this->namespaces as $namespace => $namespaceUri) {
            if (strpos($uri, $namespace . ':') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register namespaces to be used within the dataset, for instance :
     * [
     *   'myns' => 'http://www.example.com/mynamespace#',
     *   ...
     * ]
     * @param array $namespaces
     * @return $this
     */
    public function registerNamespaces(array $namespaces)
    {
        $this->namespaces = array_merge($this->namespaces, $namespaces);
        return $this;
    }

    /**
     * @return string dataset filepath
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

}