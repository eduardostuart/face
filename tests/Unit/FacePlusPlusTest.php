<?php

namespace Face\Tests\Unit;

use Mockery as m;
use Face\Models\Face;
use Face\Providers\Album;
use Face\Models\FaceAlbum;
use Face\Models\SearchItem;
use GuzzleHttp\Psr7\Request;
use Face\Models\SearchResult;
use Face\Tests\BaseUnitTestCase;
use Illuminate\Support\Collection;
use Face\Collections\FaceCollection;
use Face\Collections\AlbumCollection;
use GuzzleHttp\Exception\ClientException;
use Face\Tests\Fixtures\FacePlusPlusProviderStub;

class FacePlusPlusTest extends BaseUnitTestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = [
            'api_key' => '1234',
            'api_secret' => 'secret',
        ];
    }

    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function face_plus_plus_can_detect_faces()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/detect', $this->buildRequestOptions([
                        'image_url' => 'http://face.something/myface.jpg',
                    ]))
                    ->andReturn(json_encode([
                        'faces' => [[
                            'face_rectangle' => '123',
                            'face_token' => 'face_id-123',
                            'attributes' => 'attrs',
                            'user_id' => 'user123',
                        ]],
                    ]));

        $response = $provider->detect('http://face.something/myface.jpg');

        $face = $response->first();

        $this->assertInstanceOf(FaceCollection::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertInstanceOf(Face::class, $face);
        $this->assertSame('face_id-123', $face->getId());
        $this->assertSame('user123', $face->getUserId());
        $this->assertSame('attrs', $face->getAttributes());
        $this->assertSame('123', $face->getReference());
    }

    /** @test */
    public function face_plus_plus_always_should_return_a_facecollection_if_detect_faces_response_is_empty()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/detect', $this->buildRequestOptions([
                        'image_url' => 'http://face.something/myface.jpg',
                    ]))
                    ->andReturn('');

        $response = $provider->detect('http://face.something/myface.jpg');
        $this->assertInstanceOf(FaceCollection::class, $response);
        $this->assertSame(0, $response->count());
    }

    /** @test */
    public function face_plus_plus_should_detect_face_attributes()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/detect', $this->buildRequestOptions([
                        'image_url' => 'http://face.something/myface.jpg',
                        'return_attributes' => implode(',', ['gender', 'age', 'smiling',
                            'headpose', 'facequality', 'blur',
                            'eyestatus', 'ethnicity',
                        ]),
                    ]))
                    ->andReturn(json_encode([
                        'faces' => [[
                            'face_rectangle' => '123',
                            'face_token' => 'face_id-123',
                            'attributes' => ['some' => 'attribute'],
                            'user_id' => 'user123',
                        ]],
                    ]));

        $response = $provider->attributes('http://face.something/myface.jpg');
        $this->assertInstanceOf(FaceCollection::class, $response);
        $this->assertSame(1, $response->count());
        $this->assertInstanceOf(Face::class, $response->first());
        $this->assertSame(['some' => 'attribute'], $response->first()->getAttributes());
    }

    /** @test */
    public function face_plus_plus_always_should_return_a_facecollection_if_face_attributes_response_is_empty()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/detect', $this->buildRequestOptions([
                        'image_url' => 'http://face.something/myface.jpg',
                        'return_attributes' => implode(',', ['gender', 'age', 'smiling',
                            'headpose', 'facequality', 'blur',
                            'eyestatus', 'ethnicity',
                        ]),
                    ]))
                    ->andReturn('');

        $response = $provider->attributes('http://face.something/myface.jpg');
        $this->assertInstanceOf(FaceCollection::class, $response);
        $this->assertSame(0, $response->count());
    }

    /** @test */
    public function face_plus_plus_can_get_a_face_detail()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/face/getdetail', $this->buildRequestOptions([
                        'face_token' => '1234',
                    ]))
                    ->andReturn(json_encode([
                        'face_rectangle' => '123',
                        'face_token' => '1234',
                        'attributes' => 'attrs',
                        'user_id' => 'user123',
                    ]));

        $response = $provider->getFace('1234');
        $this->assertInstanceOf(Face::class, $response);
        $this->assertSame('1234', $response->getId());
        $this->assertSame('user123', $response->getUserId());
        $this->assertSame('attrs', $response->getAttributes());
        $this->assertSame('123', $response->getReference());
    }

    /** @test */
    public function face_plus_plus_can_search_for_faces()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/search', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                        'image_url' => 'http://face.something/myface.jpg',
                    ]))
                    ->andReturn(json_encode([
                        'results' => [[
                            'confidence' => '123',
                            'face_token' => '1111',
                        ]],
                    ]));

        $response = $provider->search('http://face.something/myface.jpg', '1234');
        $this->assertInstanceOf(SearchResult::class, $response);
        $this->assertSame(1, $response->getTotal());
        $this->assertInstanceOf(Collection::class, $response->getResults());

        $first = $response->getResults()->first();

        $this->assertInstanceOf(SearchItem::class, $first);
        $this->assertSame('123', $first->getConfidence());
        $this->assertSame('1111', $first->getId());
    }

    /** @test */
    public function face_plus_plus_should_return_search_result_if_response_is_empty()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/search', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                        'image_url' => 'http://face.something/myface.jpg',
                    ]))
                    ->andReturn('');

        $response = $provider->search('http://face.something/myface.jpg', '1234');
        $this->assertInstanceOf(SearchResult::class, $response);
        $this->assertSame(0, $response->getTotal());
        $this->assertInstanceOf(Collection::class, $response->getResults());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function face_plus_plus_should_throw_an_exception_if_searching_without_album_id()
    {
        $this->provider()->search('http://face.something/myface.jpg', null);
    }

    /** @test */
    public function face_plus_plus_can_create_a_new_album()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/create', $this->buildRequestOptions([
                        'display_name' => 'album name',
                        'face_tokens' => '1234',
                    ]))
                    ->andReturn(json_encode([
                        'faceset_token' => '122',
                    ]));

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/getdetail', $this->buildRequestOptions([
                        'faceset_token' => '122',
                    ]))
                    ->andReturn(json_encode([
                        'faceset_token' => '122',
                        'display_name' => 'album name',
                        'tags' => 'tag, tag1, tag2',
                        'face_tokens' => 'tttt',
                    ]));

        $response = $provider->createAlbum('album name', ['1234']);
        $this->assertInstanceOf(FaceAlbum::class, $response);
        $this->assertSame('album name', $response->getName());
        $this->assertSame('122', $response->getId());
        $this->assertSame('tag, tag1, tag2', $response->getTags());
        $this->assertSame(['tttt'], $response->getFaces());
    }

    /** @test */
    public function face_plus_plus_should_return_null_if_create_album_fails()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/create', $this->buildRequestOptions([
                        'display_name' => 'album name',
                        'face_tokens' => '1234',
                    ]))
                    ->andReturn('');
        $this->assertNull($provider->createAlbum('album name', ['1234']));
    }

    /** @test */
    public function face_plus_plus_can_update_an_album()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/update', $this->buildRequestOptions([
                        'display_name' => 'new album name',
                        'faceset_token' => '1234',
                    ]));

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/getdetail', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                    ]))
                    ->andReturn(json_encode([
                        'faceset_token' => '1234',
                        'display_name' => 'new album name',
                        'tags' => 'tag, tag1, tag2',
                        'face_tokens' => 'tttt',
                    ]));

        $response = $provider->updateAlbum('1234', 'new album name');
        $this->assertInstanceOf(FaceAlbum::class, $response);
        $this->assertSame('new album name', $response->getName());
        $this->assertSame('1234', $response->getId());
        $this->assertSame('tag, tag1, tag2', $response->getTags());
        $this->assertSame(['tttt'], $response->getFaces());
    }

    /** @test */
    public function face_plus_plus_can_remove_an_album()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/delete', $this->buildRequestOptions([
                        'check_empty' => 0,
                        'faceset_token' => '1234',
                    ]))
                    ->andReturn('');

        $response = $provider->removeAlbum('1234');
        $this->assertTrue($response);
    }

    /** @test */
    public function face_plus_plus_should_return_false_if_album_doesnt_exist_on_faceplusplus()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/delete', $this->buildRequestOptions([
                        'check_empty' => 0,
                        'faceset_token' => '1234',
                    ]))
                    ->andThrow(
                        new ClientException('Error', new Request('post', 'test'))
                    );

        $this->assertFalse($provider->removeAlbum('1234'));
    }

    /** @test */
    public function face_plus_plus_can_get_a_list_of_albums()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/getfacesets', $this->buildRequestOptions([]))
                    ->andReturn(json_encode([
                        'facesets' => [[
                            'faceset_token' => '1234',
                            'display_name' => 'album',
                            'tags' => '1234,1234',
                            'face_tokens' => 'token',
                        ]],
                    ]));

        $response = $provider->albums();
        $this->assertInstanceOf(AlbumCollection::class, $response);
        $this->assertSame(1, $response->count());
        $first = $response->first();
        $this->assertInstanceOf(FaceAlbum::class, $first);
        $this->assertSame('1234', $first->getId());
        $this->assertSame('album', $first->getName());
        $this->assertSame('1234,1234', $first->getTags());
        $this->assertSame(['token'], $first->getFaces());
    }

    /** @test */
    public function face_plus_plus_can_add_a_face_into_an_album()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/addface', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                        'face_tokens' => '123,12,321',
                    ]))
                    ->andReturn('');

        $response = $provider->addIntoAlbum('1234', ['123', '12', '321']);
        $this->assertTrue($response);
    }

    /** @test */
    public function face_plus_plus_can_remove_a_face_from_an_album()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/removeface', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                        'face_tokens' => '123,12,321',
                    ]))
                    ->andReturn('');

        $response = $provider->removeFaceFromAlbum('1234', ['123', '12', '321']);
        $this->assertTrue($response);
    }

    /** @test */
    public function face_plus_plus_should_return_false_if_face_or_album_does_not_exist()
    {
        $provider = $this->provider();

        $provider->http
                    ->shouldReceive('request')
                    ->once()
                    ->with('POST', 'http://facepluplus.api/faceset/removeface', $this->buildRequestOptions([
                        'faceset_token' => '1234',
                        'face_tokens' => '123,12,321',
                    ]))
                    ->andThrow(
                        new ClientException('Error', new Request('post', 'test'))
                    );
        $provider->removeFaceFromAlbum('1234', ['123', '12', '321']);
    }

    protected function buildRequestOptions($params = [], $header = [])
    {
        return [
            'form_params' => array_merge($this->config, $params),
            'headers' => array_merge([
                'Accept' => 'application/json',
            ], $header),
        ];
    }

    protected function provider()
    {
        $provider = new FacePlusPlusProviderStub($this->config);
        $provider->http = m::mock('StdClass');

        return $provider;
    }
}
