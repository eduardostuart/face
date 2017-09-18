<?php

namespace Face\Contracts;

interface FaceProvider
{
    /**
     * Detect face from url, file or base64 image.
     *
     * @param string|Illuminate\Http\UploadedFile $input
     *
     * @return Face\Collections\FaceCollection
     */
    public function detect($input);

    /**
     * Get Face by Id.
     *
     * @param mixed $faceId
     */
    public function getFace($faceId);

    /**
     * Get face attributes (emotions, gender, smile intensity, etc).
     *
     * @param string|Illuminate\Http\UploadedFile $input
     *
     * @return Illuminate\Support\Collection
     */
    public function attributes($input);

    /**
     * Search for a face.
     *
     * @param string|Illuminate\Http\UploadedFile $input
     * @param string                              $extras
     *
     * @return Illuminate\Support\Collection
     */
    public function search($input, $albumId, $extras = []);

    /**
     * Create new face album.
     *
     * @param string $name       album name
     * @param array  $images     list of images
     * @param array  $extraAttrs extra attributes
     *
     * @return Face\Providers\Album
     */
    public function createAlbum($name, $images = [], $extraAttrs = []);

    /**
     * Update an album.
     *
     * @param string $albumId
     * @param string $name
     * @param array  $extraAttrs
     *
     * @return Face\Providers\Album
     */
    public function updateAlbum($albumId, $name, $extraAttrs = []);

    /**
     * Remove an album.
     *
     * @param string $albumId
     *
     * @return bool
     */
    public function removeAlbum($albumId);

    /**
     * Get a list of albums.
     *
     * @return Illuminate\Support\Collection
     */
    public function albums();

    /**
     * Get a single album.
     *
     * @return Face\Models\FaceAlbum
     */
    public function album($albumId);

    /**
     * Add a face into an album.
     *
     * @param string       $albumId
     * @param string|array $input
     *
     * @return bool
     */
    public function addIntoAlbum($albumId, $input);

    /**
     * Remove face from album.
     *
     * @param string       $albumId
     * @param string|array $faceIds
     *
     * @return bool
     */
    public function removeFaceFromAlbum($albumId, $faceIds);
}
