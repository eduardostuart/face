<?php

namespace Face\Tests\Facades;

use Face\Tests\AbstractTestCase;
use GrahamCampbell\TestBenchCore\FacadeTrait;
use Face\Facades\Face;
use Face\FacePlusPlus;

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
        return 'face';
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
        return FacePlusPlus::class;
    }
}
