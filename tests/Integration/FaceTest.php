<?php

namespace Face\Tests\Integration;

use Face\FaceManager;
use Face\Facades\Face;
use Face\Contracts\Factory;
use Face\Tests\AbstractTestCase;
use GrahamCampbell\TestBenchCore\FacadeTrait;

class FaceTest extends AbstractTestCase
{
    use FacadeTrait;

    /**
     * Get the facade accessor.
     *
     * @return string
     */
    protected function getFacadeAccessor()
    {
        return Factory::class;
    }

    /**
     * Get the facade class.
     *
     * @return string
     */
    protected function getFacadeClass()
    {
        return Face::class;
    }

    /**
     * Get the facade root.
     *
     * @return string
     */
    protected function getFacadeRoot()
    {
        return FaceManager::class;
    }
}
