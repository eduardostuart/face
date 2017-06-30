<?php

namespace Face\Facades;

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
        return 'face';
    }
}
