<?php

namespace Face\Tests\Integration;

use Face\Facades\Face;
use Face\Tests\AbstractTestCase;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;

class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    /** @test */
    public function is_face_injectable()
    {
        $this->assertIsInjectable(Face::class);
    }
}
