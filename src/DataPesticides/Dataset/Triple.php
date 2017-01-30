<?php

namespace Neveldo\DataPesticides\Dataset;

/**
 * Class Triple
 * Store a RDF triple
 * @package Neveldo\DataPesticides\Dataset
 */
class Triple
{
    const TYPE_RESOURCE = 'resource';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'http://www.w3.org/2001/XMLSchema#date';
    const TYPE_DATETIME = 'http://www.w3.org/2001/XMLSchema#dateTime';
    const TYPE_INT = 'http://www.w3.org/2001/XMLSchema#int';
    const TYPE_DOUBLE = 'http://www.w3.org/2001/XMLSchema#double';

    /**
     * @var string Triple subjec
     */
    private $subject;

    /**
     * @var string triple predicate
     */
    private $predicate;

    /**
     * @var string Triple object
     */
    private $object;

    /**
     * @var string object datatype
     */
    private $type;

    /**
     * Triple constructor.
     * @param string $subject Triple subject
     * @param string $predicate Triple predicate
     * @param string $object Triple object
     * @param string $type Triple datatype
     */
    public function __construct($subject, $predicate, $object, $type = self::TYPE_STRING)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
        $this->type = $type;
    }

    /**
     * @return string get triple subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string get triple predicate
     */
    public function getPredicate()
    {
        return $this->predicate;
    }

    /**
     * @return string get triple object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string gettriple datatype
     */
    public function getType()
    {
        return $this->type;
    }
}