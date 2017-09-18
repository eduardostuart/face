<?php

namespace Face\Models;

use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractModel implements Jsonable, Arrayable
{
    /**
     * Item identification.
     *
     * @var string
     */
    protected $id;

    /**
     * Raw value.
     *
     * @var array
     */
    protected $raw;

    /**
     * Set raw value.
     *
     * @param Face\Models\AbstractModel
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Get raw value.
     *
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Get model id.
     *
     * @param Face\Models\AbstractModel
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get model id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Map array of attributes.
     *
     * @param array $data
     *
     * @return Face\Models\AbstractModel
     */
    public function map(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{Str::camel($key)} = $value;
        }

        return $this;
    }
}
