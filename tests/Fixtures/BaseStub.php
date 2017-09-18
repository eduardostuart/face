<?php

namespace Face\Tests\Fixtures;

use Mockery;
use Face\Providers\FacePlusPlusProvider;

abstract class BaseStub extends FacePlusPlusProvider
{
    public $http;

    public function httpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        $this->http = Mockery::mock('StdClass');

        return $this->http;
    }
}
