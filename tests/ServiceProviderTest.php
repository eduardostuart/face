<?php

namespace Face\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Face\Providers\FaceServiceProvider;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use Face\FacePlusPlus;

class ServiceProviderTest extends AbstractTestCase
{
    use ServiceProviderTrait;

    /** @test */
    public function is_face_plusplus_injectable()
    {
        $this->assertIsInjectable(FacePlusPlus::class);
    }
}
