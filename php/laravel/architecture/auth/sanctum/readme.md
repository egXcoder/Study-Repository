# Laravel Sanctum

Laravel Sanctum is an official Laravel package for API authentication, designed to be simple and lightweight. 

Usage:
- SPA Authentication (Session + CSRF) [Explained here](./spa.md)
- API Token Authentication (mobile apps, Postman, etc.) [Explained here](./api.md)


## Install

`composer require laravel/sanctum`

`php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` config/sanctum.php and migrations

`php artisan migrate`