## composer
- `composer install` .. install packages exact versions in composer.lock
- `composer update` .. install packages and update to latest version allowed


### Require vs require dev


require-dev are for packages not needed on production like phpunit/faker/tinker etc..

- `composer install` .. install both require and require dev
- `composer install --no-dev` .. should run in production

```json

"require": {
    "php": "^8.1",
    "laravel/framework": "^10.0",
    "guzzlehttp/guzzle": "^7.0"
},
"require-dev": {
    "phpunit/phpunit": "^10.0",
    "fakerphp/faker": "^1.9.1",
    "laravel/tinker": "^2.8"
}


```