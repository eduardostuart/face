<p align="center"><img src=".github/face-logo.png"></p>

<p align="center">
    <a href="https://circleci.com/gh/eduardostuart/face">
        <img src="https://circleci.com/gh/eduardostuart/face.svg?style=shield&circle-token=7c0f8d59ceab88bb5ca8d50064401b664589961e">
    </a>
    <img src="https://scrutinizer-ci.com/g/eduardostuart/face/badges/quality-score.png?b=master">
</p>

## Introduction


## Summary


1. [Installation](#installation)
1. [Configuration](#configuration)
1. [How to use](#how-to-use)
   1. [Detect api](#detect-api)
   1. [Compare api](#compare-api)
   1. [FaseSet (collection of faces)](#faceset-collection-of-faces)
   1. [Search api](#search-api)
1. [Credits](#credits)
1. [License](#license)


## Installation

To get started with Face, use Composer to add the package to your project's dependencies:

```bash
composer require eduardostuart/face
```

Once installed, you need to register the `Face Service provider` in your `config/app.php`.

```php
return [
    // ....
    Face\Providers\FaceServiceProvider::class,
]
```

If you want to use `Face Facade`, you can also add:

```php
return [
    // ....
    'Face' => Face\Facades\Face::class,
]
```

## Configuration

Laravel Face uses Face++ api. To setup credentials you'll need to publish `Face` configuration file.

```php
php artisan vendor:publish --provider="Face\Providers\FaceServiceProvider"
```

Add Face++ credentials in `face.php` or add into `.env` file.

```bash
FACEPLUS_API_KEY=xxxx
FACEPLUS_API_SECRET=xxxx
``` 

## How to use

### Detect

Detect and analyzes human faces.

```php
// ...
use Face\Facades\Face;

$results = Face::detectFaces('https://.../photo.jpg');
```

more information about [Detect API](https://console.faceplusplus.com/documents/5679127).


### Compare

Compare two faces.

```php
// ...
use Face\Facades\Face;

$results = Face::compare('https://.../photo.jpg', 'https://.../photo2.jpg');
```

more information about [Compare API](https://console.faceplusplus.com/documents/5679308)

## FaceSet (collection of faces)

Create a face collection.

```php
// ...
use Face\Facades\Face;

// $name = null;
// $outerId = null;
// $tags = null;
// $faceTokens= null;
// $userData = null;
// $forceMerge = false
// Face::createFaceSet($name, $outerId, $tags, $faceTokens, $userData, $forceMerge);

$results = Face::createFaceSet();
```


### Search

Find one or more similar faces from `FaceSet` ("collection of faces").


```php
// ...
use Face\Facades\Face;

$faceSetId = 'xxxxx';

$results = Face::search($faceSetId, 'https://.../photo.jpg');
```


more information about [Search API](https://console.faceplusplus.com/documents/5681455)

## Credits

Vector Face icon created by [Antonis Makriyannis](https://thenounproject.com/search/?q=face%20recognition&i=143017).

## License

Face is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
