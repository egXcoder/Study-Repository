# PHP


## install any php extenstion
- `apt install php8.1-curl`
- `apt install php8.1-mysql`
- `php -m`

## enable/disable php extensions
- `phpenmod curl` enable curl module
- `phpdismod curl` disable curl module


## multiple php versions
- `sudo update-alternatives --config php` .. choose the default one


## composer
- `composer install` .. install packages exact versions in composer.lock
- `composer update` .. install packages and update to latest version allowed