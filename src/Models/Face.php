<?php

namespace Face\Models;

class Face extends AbstractModel
{
    /**
     * Face attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Face reference, width, height, position.
     *
     * @var array
     */
    protected $reference;

    /**
     * User identification.
     *
     * @var string
     */
    protected $userId;

    /**
     * Get the user id.
     *
     * @return string
     */
    public function getUserId()
    {
        return empty($this->userId) ? null : (string) $this->userId;
    }

    /**
     * Get face attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get face reference.
     *
     * @return array
     */
    public function getReference()
    {
        return $this->reference;
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
            'attributes' => $this->getAttributes(),
            'reference' => $this->getReference(),
            'user_id' => $this->getUserId(),
        ];
    }
}
