<?php

namespace Face\Providers;

use Closure;
use Face\Models\Face;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use Face\Models\FaceAlbum as Album;
use Face\Collections\FaceCollection;
use Face\Collections\AlbumCollection;

abstract class BaseProvider
{
    /**
     * This provider configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Http Client.
     *
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Guzzle custom configuration options.
     *
     * @var array
     */
    protected $guzzle = [];

    /**
     * Create new Face Provider instance.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = (array) $config;
    }

    /**
     * Base api url.
     *
     * @return string
     */
    abstract public function getBaseApiUrl();

    /**
     * Http Client instance.
     *
     * @return GuzzleHttp\Client
     */
    public function httpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client(array_replace_recursive(
                ['headers' => $this->getDefaultHeaders()],
                $this->guzzle
            ));
        }

        return $this->httpClient;
    }

    /**
     * Get this provider configuration.
     *
     * @param string|array $keys
     *
     * @return mixed
     */
    public function config($keys = null)
    {
        if (is_array($keys)) {
            return Arr::only($this->config, $keys);
        }

        return Arr::get($this->config, $keys);
    }

    /**
     * Default guzzle http headers.
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Build a generic http request.
     *
     * @param string $method
     * @param string $resource
     * @param array  $options
     *
     * @return array
     */
    protected function buildRequest($method, $resource, array $options = [])
    {
        $url = $this->buildUrl($resource);

        $headers = isset($options['headers']) ? (array) $options['headers'] : [];

        $options['headers'] = array_replace_recursive(
            $this->getDefaultHeaders(), $headers
        );

        return $this->getRequestResponse(
            $this->httpClient()->request($method, $url, $options)
        );
    }

    /**
     * Multipart/form-data request.
     *
     * @param string $method
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    protected function request($method, $resource, $params = [], $headers = [])
    {
        return $this->buildRequest($method, $resource, [
            'form_params' => $this->buildRequestParams($params),
            'headers' => $headers,
        ]);
    }

    /**
     * Json request.
     *
     * @param string $method
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     */
    protected function json($method, $resource, $params = [], $headers = [])
    {
        return $this->buildRequest($method, $resource, [
            'json' => $this->buildRequestParams($params),
            'headers' => $headers,
        ]);
    }

    /**
     * Get a equest response.
     *
     * @param Response $response
     *
     * @return mixed
     */
    protected function getRequestResponse($response)
    {
        if ($response instanceof Response) {
            $response = $response->getBody();
        }

        return (string) $response;
    }

    /**
     * Get which type of image we should send.
     * Types can be: url, base64 or file.
     *
     * @param string $image
     *
     * @return string
     */
    protected function getTypeOfInput($image)
    {
        if (Str::startsWith($image, 'http')) {
            return 'url';
        }

        if (base64_decode($image, true) !== false) {
            return 'base64';
        }

        return 'file';
    }

    /**
     * Build a resource url.
     *
     * @param string $resource
     *
     * @return string
     */
    protected function buildUrl($resource = '')
    {
        return rtrim($this->getBaseApiUrl(), '/').'/'.ltrim($resource, '/');
    }

    /**
     * Build request params.
     *
     * @param array $params
     *
     * @return string
     */
    protected function buildRequestParams($params = [])
    {
        return http_build_query($params, '', '&');
    }

    /**
     * Map list of faces.
     *
     * @param array $data
     *
     * @return Face\Collections\FaceCollection
     */
    protected function mapFaces(array $data)
    {
        return $this->faceCollection($data, function ($face) {
            return call_user_func([$this, 'mapFace'], $face);
        });
    }

    /**
     * Map albums.
     *
     * @param array $data
     *
     * @return Face\Collections\AlbumCollection
     */
    protected function mapAlbums(array $data)
    {
        return $this->albumCollection($data, function ($album) {
            return call_user_func([$this, 'mapAlbum'], $album);
        });
    }

    /**
     * Map a single algum.
     *
     * @param array $data
     *
     * @return Face\Models\FaceAlbum
     */
    protected function mapAlbum(array $data)
    {
        return (new Album())->setRaw($data);
    }

    /**
     * Map a single face.
     *
     * @param array $data
     *
     * @return Face\Models\Face
     */
    protected function mapFace(array $data)
    {
        return (new Face())->setRaw($data);
    }

    /**
     * Create new face collection.
     *
     * @param array   $data
     * @param Closure $transformer
     *
     * @return Face\Collections\FaceCollection
     */
    protected function faceCollection($data, $transformer = null)
    {
        $collection = new FaceCollection($data);

        if ($transformer instanceof Closure) {
            return $collection->transform($transformer);
        }

        return $collection;
    }

    /**
     * Create a new album collection.
     *
     * @param array   $data
     * @param Closure $transformer
     *
     * @return Face\Collections\AlbumCollection
     */
    protected function albumCollection($data, $transformer = null)
    {
        $collection = new AlbumCollection($data);

        if ($transformer instanceof Closure) {
            return $collection->transform($transformer);
        }

        return $collection;
    }
}
