<?php

namespace Face\Facades;

use Face\Contracts\Factory;
use Illuminate\Support\Facades\Facade;

class Face extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
