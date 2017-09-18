<?php

namespace Face\Contracts;

interface Factory
{
    /**
     * Get Face provder implementation.
     *
     * @param string $driver
     *
     * @return Face\Contracts\FaceProvider
     */
    public function driver($driver = null);
}
