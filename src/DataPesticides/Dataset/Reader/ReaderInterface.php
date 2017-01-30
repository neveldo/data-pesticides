<?php

namespace Neveldo\DataPesticides\Dataset\Reader;

/**
 * Interface ReaderInterface
 * Allow to read the content of a dataset line by line
 * @package Neveldo\DataPesticides\Dataset\Reader
 */
interface ReaderInterface
{
    /**
     * Open the file  in order to iterate on the dataset lines
     * @throws \InvalidArgumentException if the file does not exist or can't be open
     * @return mixed
     */
    public function open();

    /**
     * Close the file and free resources
     */
    public function close();

    /**
     * Return the next line of the dataset as array
     * of false if there is no new line
     * @return array|false
     */
    public function read();

    /**
     * Rewind to the beginning of the dataset
     */
    public function rewind();
}