<?php

namespace Face\Models;

class FaceAlbum extends AbstractModel
{
    /**
     * Album name.
     *
     * @var string
     */
    protected $name;

    /**
     * Album tags.
     *
     * @var string
     */
    protected $tags;

    /**
     * List of faces.
     *
     * @var array
     */
    protected $faces = [];

    /**
     * Album name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->name) ? null : $this->name;
    }

    /**
     * Get album tags.
     *
     * @return string
     */
    public function getTags()
    {
        return empty($this->tags) ? null : $this->tags;
    }

    /**
     * Get all faces inside this album.
     *
     * @return array
     */
    public function getFaces()
    {
        return empty($this->faces) ? [] : (array) $this->faces;
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
            'id' => $this->getId(),
            'name' => $this->getName(),
            'tags' => $this->getTags(),
            'faces' => $this->getFaces(),
        ];
    }
}
