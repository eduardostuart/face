<?php

namespace Face\Providers;

use Face\Models\Face;
use Face\Models\SearchResult;
use InvalidArgumentException;
use Face\Contracts\SearchItem;
use Face\Contracts\FaceProvider;
use Face\Models\FaceAlbum as Album;
use GuzzleHttp\Exception\ClientException;

class FacePlusPlusProvider extends BaseProvider implements FaceProvider
{
    /**
     * Face++ API base url.
     *
     * @return string
     */
    public function getBaseApiUrl()
    {
        if (empty($baseUrl = $this->config('base_url'))) {
            return 'https://api-us.faceplusplus.com/facepp/v3';
        }

        return $baseUrl;
    }

    /**
     * Detect face from url, file or base64 image.
     *
     * @param string|Illuminate\Http\UploadedFile $input
     *
     * @return \Face\Collections\FaceCollection
     */
    public function detect($input)
    {
        $inputType = $this->getInputTypeFieldName($input);

        $response = $this->request('POST', 'detect', [$inputType => $input]);

        if (! isset($response['faces'])) {
            return $this->faceCollection([]);
        }

        return $this->mapFaces((array) $response['faces']);
    }

    /**
     * Get face attributes (emotions, gender, smile intensity, etc).
     *
     * @param string|Illuminate\Http\UploadedFile $input
     *
     * @return \Face\Collections\FaceCollection
     */
    public function attributes($input)
    {
        $inputType = $this->getInputTypeFieldName($input);

        $response = $this->request('POST', 'detect', [
            $inputType => $input,
            'return_attributes' => $this->getAttributeList(),
        ]);

        if (! isset($response['faces'])) {
            return $this->faceCollection([]);
        }

        return $this->mapFaces((array) $response['faces']);
    }

    /**
     * Get a single face.
     *
     * @param string $faceId
     *
     * @return \Face\Models\Face
     */
    public function getFace($faceId)
    {
        $response = $this->request('POST', 'face/getdetail', [
            'face_token' => $faceId,
        ]);

        return $this->mapFace($response);
    }

    /**
     * Search for a face.
     *
     * @param mixed $input
     * @param array $extraAttrs
     *
     * @return \Face\Models\SearchResult
     */
    public function search($input, $albumId, $extraAttrs = [])
    {
        if (is_null($albumId)) {
            throw new InvalidArgumentException('Album id is required');
        }

        $inputType = $this->getInputTypeFieldName($input);

        $data = array_merge([
            'faceset_token' => $albumId,
            $inputType => $input,
        ], $extraAttrs);

        $response = $this->request('POST', 'search', $data);

        if (! isset($response['results'])) {
            return new SearchResult();
        }

        $result = (new SearchResult())
            ->setRaw($response)
            ->setResults($response['results'], function (SearchItem $searchItem, $item) {
                return $searchItem
                        ->setConfidence($item['confidence'])
                        ->setId($item['face_token']);
            });

        return $result;
    }

    /**
     * Create new face album.
     *
     * @param string $name       album name
     * @param array  $images     list of images
     * @param array  $extraAttrs $optional attributes
     *
     * @return \Face\Models\FaceAlbum
     */
    public function createAlbum($name, $images = [], $extraAttrs = [])
    {
        $data = ['display_name' => $name];

        if (count($images) > 0) {
            $data['face_tokens'] = implode(',', array_map('trim', $images));
        }

        $data = array_filter(array_merge($data, $extraAttrs));

        $response = $this->request('POST', 'faceset/create', $data);

        if (isset($response['faceset_token'])) {
            return $this->album($response['faceset_token']);
        }
    }

    /**
     * Update album.
     *
     * @param string $albumId
     * @param string $name
     * @param array  $extraAttrs
     *
     * @return \Face\Providers\Album
     */
    public function updateAlbum($albumId, $name, $extraAttrs = [])
    {
        $this->request('POST', 'faceset/update', array_merge([
            'display_name' => $name,
            'faceset_token' => $albumId,
        ], $extraAttrs));

        return $this->album($albumId);
    }

    /**
     * Remove an album.
     *
     * @param string $albumId
     *
     * @return bool
     */
    public function removeAlbum($albumId)
    {
        try {
            $response = $this->request('POST', 'faceset/delete', [
                'check_empty' => 0,
                'faceset_token' => $albumId,
            ]);
        } catch (ClientException $e) {
            return false;
        }

        return ! isset($response['error_message']);
    }

    /**
     * Get list of albums.
     *
     * @return \Illuminate\Support\Collection|\Face\Models\FaceAlbum
     */
    public function albums()
    {
        $response = $this->request('POST', 'faceset/getfacesets');

        if (! isset($response['facesets'])) {
            return $this->mapAlbums([]);
        }

        return $this->mapAlbums($response['facesets']);
    }

    /**
     * Get a single album.
     *
     * @param mixed $albumId
     *
     * @return \Face\Models\FaceAlbum
     */
    public function album($albumId)
    {
        $response = $this->request('POST', 'faceset/getdetail', [
            'faceset_token' => $albumId,
        ]);

        return $this->mapAlbum($response);
    }

    /**
     * Add a face into an album.
     *
     * @param string $albumId
     * @param mixed  $input
     *
     * @return bool
     */
    public function addIntoAlbum($albumId, $input)
    {
        if (is_array($input)) {
            $input = implode(',', $input);
        }

        $response = $this->request('POST', 'faceset/addface', [
            'faceset_token' => $albumId,
            'face_tokens' => $input,
        ]);

        return ! (isset($response['failure_detail']) && count($response['failure_detail']) > 0);
    }

    /**
     * Remove specifi face from album.
     *
     * @param string       $albumId
     * @param string|array $faceIds
     *
     * @return bool
     */
    public function removeFaceFromAlbum($albumId, $faceIds)
    {
        if (is_array($faceIds)) {
            $faceIds = implode(',', $faceIds);
        }

        try {
            $this->request('POST', 'faceset/removeface', [
                'faceset_token' => $albumId,
                'face_tokens' => $faceIds,
            ]);
        } catch (ClientException $e) {
            return false;
        }

        return true;
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
        return array_merge($params, $this->config(['api_key', 'api_secret']));
    }

    /**
     * Get request response as array.
     *
     * @param mixed $response
     *
     * @return string
     */
    protected function getRequestResponse($response)
    {
        return json_decode(parent::getRequestResponse($response), true);
    }

    /**
     * Get proper input type field name.
     *
     * @param mixed $inputType
     *
     * @return string
     */
    protected function getInputTypeFieldName($inputType)
    {
        return 'image_'.$this->getTypeOfInput($inputType);
    }

    /**
     * Map a single album.
     *
     * @param array $data
     *
     * @return \Face\Models\FaceAlbum
     */
    protected function mapAlbum(array $data)
    {
        return (new Album())->setRaw($data)->map([
            'id' => isset($data['faceset_token']) ? $data['faceset_token'] : null,
            'name' => isset($data['display_name']) ? $data['display_name'] : null,
            'tags' => isset($data['tags']) ? $data['tags'] : null,
            'faces' => isset($data['face_tokens']) ? $data['face_tokens'] : null,
        ]);
    }

    /**
     * Map a single face.
     *
     * @param array $data
     *
     * @return \Face\Models\Face
     */
    protected function mapFace(array $data)
    {
        return (new Face())->setRaw($data)->map([
            'reference' => isset($data['face_rectangle']) ? $data['face_rectangle'] : null,
            'id' => isset($data['face_token']) ? $data['face_token'] : null,
            'attributes' => isset($data['attributes']) ? $data['attributes'] : null,
            'user_id' => isset($data['user_id']) ? (string) $data['user_id'] : null,
        ]);
    }

    /**
     * Get the list of attributes.
     *
     * @return string
     */
    protected function getAttributeList()
    {
        return implode(',', ['gender', 'age', 'smiling',
            'headpose', 'facequality', 'blur',
            'eyestatus', 'ethnicity',
        ]);
    }
}
