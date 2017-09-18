<?php

namespace Face\Contracts;

interface SearchItem
{
    /**
     * Indicates the similarity of two faces.
     * .
     *
     * @param float
     */
    public function setConfidence($value);

    /**
     * Set search result item id.
     *
     * @param string
     */
    public function setId($value);
}
