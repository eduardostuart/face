<p align="center"><img src=".github/face-logo.png"></p>

<p align="center">
    <a href="https://circleci.com/gh/eduardostuart/face">
        <img src="https://circleci.com/gh/eduardostuart/face.svg?style=shield&circle-token=7c0f8d59ceab88bb5ca8d50064401b664589961e">
    </a>
</p>

## Introduction

Face is a laravel package

## License

Face is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

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


## Credits

1. Vector Face icon created by [Antonis Makriyannis](https://thenounproject.com/search/?q=face%20recognition&i=143017).

