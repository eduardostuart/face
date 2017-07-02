<?php

namespace Face;

use GuzzleHttp\Client;
use Face\FaceCollection;
use Illuminate\Http\Request;
use InvalidArgumentException;
use GuzzleHttp\Psr7\Response;

class FacePlusPlus
{
    /**
     * Http Client
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Custom parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     * Guzzle custom configuration options
     * @var array
     */
    protected $guzzle;

    /**
     * Face++ Api Key
     *
     * @var String
     */
    protected $apiKey;

    /**
     * Face++ Api Secret
     * @var String
     */
    protected $apiSecret;

    public function __construct($apiKey = null, $apiSecret = null, $guzzle = [])
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->guzzle = $guzzle;
    }

    /**
     * Http Client instance
     *
     * @return GuzzleHttp\Client
     */
    public function httpClient()
    {
        if (is_null($this->httpClient)) {
            $httpClient = new Client(array_replace_recursive(
                ['headers' => $this->getDefaultHeaders()],
                $this->guzzle
            ));
        }

        return $httpClient;
    }

    /**
     * Default guzzle http headers
     *
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Get configuration keys
     *
     * @throws InvalidArgumentException if keys are empty
     * @return array
     */
    protected function getConfig()
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new InvalidArgumentException('You must specify the Face++ api key and secret');
        }

        return [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
        ];
    }

    /**
     * Build form params
     *
     * @param  array  $params
     * @return array
     */
    protected function buildParams(array $params = [])
    {
        return array_merge($this->getConfig(), $params);
    }

    /**
     * Build request url
     *
     * @param  String $resource
     * @return String
     */
    protected function buildUrl($resource)
    {
        return 'https://api-us.faceplusplus.com/facepp/v3/' . ltrim($resource, '/');
    }

    /**
     * Get which type of image we should send.
     * Types can be: url, base64 or file
     *
     * @param  String $image
     * @return String
     */
    protected function getImageInputType($image)
    {
        if (preg_match('/^http/i', $image)) {
            return 'image_url';
        }

        if (base64_decode($image, true) !== false) {
            return 'image_base64';
        }

        return 'image_file';
    }

    /**
     * Make http request
     *
     * @param  String $resource
     * @param  array  $params
     * @return array
     */
    protected function request($method, $resource, $params = [])
    {
        $url = $this->buildUrl($resource);

        $reponse = $this->httpClient()->request($method, $url, [
            'form_params' => $this->buildParams($params)
        ]);

        return $this->buildResponse($response);
    }

    /**
     * Build response
     *
     * @param  Response $response
     * @return array
     */
    protected function buildResponse(Response $response)
    {
        $items = json_decode($response->getBody(), true);

        return new FaceCollection($items);
    }

    /**
     * Detect face
     *
     * @param  String $image
     * @return array
     */
    public function detectFaces($image)
    {
        return $this->request('POST', 'detect', [$this->getImageInputType($image) => $image]);
    }

    /**
     * Compare two images
     *
     * @param  String $imageA
     * @param  String $imageB
     * @return array
     */
    public function compare($imageA, $imageB)
    {
        return $this->request('POST', 'compare', [
            $this->getImageInputType($imageA) . '1' => $imageA,
            $this->getImageInputType($imageB) . '2' => $imageB
        ]);
    }

    /**
     * Create new face set
     *
     * @param  String  $name
     * @param  String  $outerId
     * @param  String  $tags
     * @param  String  $faceTokens
     * @param  String  $userData
     * @param  boolean $forceMerge
     * @return array
     */
    public function createFaceSet(
        $name = null,
        $outerId = null,
        $tags = null,
        $faceTokens= null,
        $userData = null,
        $forceMerge = false
    ) {
        $params = [];
        $params['display_name'] = empty($name) ? null : $name;
        $params['outer_id'] = empty($outerId) ? null : $outerId;
        $params['tags'] = empty($tags) ? null : $tags;
        $params['face_tokens'] = empty($faceTokens) ? null : $faceTokens;
        $params['user_data'] = empty($userData) ? null : $userData;
        $params['force_merge'] = $forceMerge ? 1 : 0;

        return $this->request('POST', 'faceset/create', $this->buildParams($params));
    }

    /**
     * Add new face into an existing FaceSet
     *
     * @param String $faceSetId
     * @param String $image
     */
    public function addFace($faceSetId, $image)
    {
        $response = $this->detectFaces($image);

        $tokens = $this->getFaceTokens($response['faces']);

        return $this->request('POST', 'faceset/addface', [
            'faceset_token' => $faceSetId,
            'face_tokens' => $tokens
        ]);
    }

    /**
     * Get list of face tokens
     *
     * @param  array  $faces
     * @param  integer $limit
     * @return String
     */
    protected function getFaceTokens($faces, $limit = 5)
    {
        $tokens = [];

        foreach ($faces as $face) {
            if (count($tokens) >= $limit) {
                break;
            }

            $tokens[] = $face['face_token'];
        }

        return implode(',', $tokens);
    }

    /**
     * Get informations about a FaceSet
     *
     * @param  String $faceSetId
     * @return array
     */
    public function getFaceSet($faceSetId)
    {
        return $this->request('POST', 'faceset/getdetail', [
            'faceset_token' => $faceSetId,
        ]);
    }

    /**
     * Search for a face
     *
     * @param  String $faceSetId
     * @param  String $image
     * @return array
     */
    public function search($faceSetId, $image)
    {
        return $this->request('POST', 'search', [
            'faceset_token' => $faceSetId,
            $this->getImageInputType($image) => $image,
        ]);
    }

    /**
     * Get all FaceSet
     *
     * @param  integer $start
     * @param  String  $tags
     * @return array
     */
    public function allFaceSets($start = 1, $tags = null)
    {
        $params['start'] = $start;

        if (!empty($tags)) {
            $params['tags'] = $tags;
        }

        return $this->request('POST', 'faceset/getfacesets', $params);
    }
}
