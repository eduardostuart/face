<?php

namespace Face\Models;

use Illuminate\Support\Collection;

class SearchResult extends AbstractModel
{
    /**
     * Search results.
     *
     * @var array
     */
    protected $results = [];

    /**
     * Total results.
     *
     * @var int
     */
    protected $total;

    /**
     * Total of results.
     *
     * @return int
     */
    public function getTotal()
    {
        return intval($this->total) < 1 ? 0 : $this->total;
    }

    /**
     * Search results.
     *
     * @return Illuminate\Support\Collection
     */
    public function getResults()
    {
        if (! ($this->results instanceof Collection)) {
            return new Collection($this->results);
        }

        return $this->results;
    }

    /**
     * Set search results.
     *
     * @param Face\Models\SearchResult
     */
    public function setResults($results, callable $map)
    {
        if (! ($results instanceof Collection)) {
            $results = new Collection($results);
        }

        $this->results = $results->transform(function ($item) use ($map) {
            return $map((new SearchItem())->setRaw($item), $item);
        });

        $this->setTotal($results->count());

        return $this;
    }

    /**
     * Total results.
     *
     * @param int $total
     *
     * @return Face\Models\SearchResult
     */
    public function setTotal($total)
    {
        $this->total = $total;

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
            'total' => $this->getTotal(),
            'results' => $this->getResults(),
        ];
    }
}
