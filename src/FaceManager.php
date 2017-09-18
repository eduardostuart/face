<?php

namespace Face;

use Face\Contracts\Factory;
use Illuminate\Support\Manager;
use Face\Providers\FacePlusPlusProvider as FacePlusPlus;

class FaceManager extends Manager implements Factory
{
    /**
     * Create an instance of the Face++ Driver.
     *
     * @return Face\Contracts\FaceProvider
     */
    public function createFacePlusPlusDriver()
    {
        $config = $this->getDriverConfig('face_plus_plus');

        return new FacePlusPlus($config);
    }

    /**
     * Get the default provider name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']->get('face.default_provider');
    }

    /**
     * Get the driver configuration.
     *
     * @param string $driverName
     *
     * @return array
     */
    protected function getDriverConfig($driverName)
    {
        $driverName = strtolower($driverName);

        return $this->app['config']->get("face.providers.{$driverName}");
    }
}
