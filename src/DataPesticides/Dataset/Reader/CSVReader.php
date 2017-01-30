<?php

namespace Neveldo\DataPesticides\Dataset\Reader;

/**
 * Class CSVReader
 * allow to read a CSV line by line
 * @package Neveldo\DataPesticides\Dataset\Reader
 */
class CSVReader implements ReaderInterface
{

    /**
     * @var string file path
     */
    private $filepath;

    /**
     * @var resource file
     */
    private $file = null;

    /**
     * @var string CSV cell delimiter
     */
    private $delimiter = ',';

    /**
     * @var string csv value enclosure
     */
    private $enclosure = '"';

    /**
     * @var string CSV value escape char
     */
    private $escape = "\\";

    /**
     * CSVReader constructor.
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Open the file  in order to iterate on the CSV lines
     * @throws \InvalidArgumentException if the file does not exist or can't be open
     * @return mixed
     */
    public function open()
    {
        if ($this->file !== null) {
            $this->close();
        }

        if (!file_exists($this->filepath)
        || !is_readable($this->filepath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" doesn\'t exist or is not readable.', $this->filepath));
        }

        $this->file = fopen($this->filepath, 'r');
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
     * Return the next line of the CSV file as array
     * of false if there is no new line
     * @return array|false
     */
    public function read()
    {
        if ($this->file === null) {
            return false;
        }

        $line = fgets($this->file);

        if ($line === false) {
            return false;
        }

        return str_getcsv(
            $line,
            $this->delimiter,
            $this->enclosure,
            $this->escape
        );
    }

    /**
     * Rewind to the beginning of the CSV file
     */
    public function rewind()
    {
        if ($this->file !== null) {
            rewind($this->file);
        }
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    /**
     * @param string $escape
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;
    }
}