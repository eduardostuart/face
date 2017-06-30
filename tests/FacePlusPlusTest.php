<?php

namespace Tests;

use Mockery as m;
use GuzzleHttp\ClientInterface;
use PHPUnit_Framework_TestCase;

class FacePlusPlusTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test()
    {
    }
}
