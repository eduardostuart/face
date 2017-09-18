<?php

namespace Face\Models;

use Face\Contracts\SearchItem as Contract;

class SearchItem extends AbstractModel implements Contract
{
    /**
     * Result item confidence.
     *
     * @var float
     */
    protected $confidence;

    /**
     * Get result item confidence.
     *
     * @return float
     */
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * Set item confidence value.
     *
     * @param Face\Models\SearchItem
     */
    public function setConfidence($value)
    {
        $this->confidence = $value;

        return $this;
    }

    /**
     * Transform face into json.
     *
     * @param array $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Transform face into array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'confidence' => $this->getConfidence(),
            'id' => $this->getId(),
            'raw' => $this->getRaw(),
        ];
    }
}
