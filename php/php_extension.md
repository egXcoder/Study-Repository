# PHP Extensions


## install any php extenstion
- `apt install php8.1-curl`
- `apt install php8.1-mysql`
- `php -m`


## enable/disable php extensions
- `phpenmod curl` enable curl module
- `phpdismod curl` disable curl module


## is there any issue if i installed and enabled php extenstions that i am not going to use?
✅ Pros (or no real issues)

- They sit idle if unused → If your code never calls their functions, they won’t run, so no extra CPU overhead.
- Common practice → Many distros (like XAMPP, WAMP, Ubuntu PHP packages) enable a bunch of extensions by default.
- Easier compatibility → Sometimes future packages you install may need those extensions. Having them enabled avoids errors.

⚠️ Possible downsides

- Memory usage
    Each extension loads into PHP’s memory space. Usually this is very small (a few hundred KB to a couple MB per extension), but if you enable many unneeded ones, it adds up.

- Security surface
    If an extension has a vulnerability, having it enabled increases your attack surface (even if you don’t use it directly, sometimes it might expose new functions).

    Example: enabling php_curl lets you make HTTP requests, enabling php_imap lets you parse emails. If you don’t need them, it’s safer not to have them.


## in laravel, is there a place to define which php extenstions laravel project require?

it’s common and recommended practice to declare PHP extensions your project depends on in composer.json.

- Laravel itself does it → The default Laravel composer.json already includes extensions like ext-mbstring, ext-bcmath, ext-curl, ext-pdo, etc.

- Many PHP packages do it → For example, guzzlehttp/guzzle may require ext-curl, doctrine/dbal needs ext-pdo.

- Composer will stop installation if a required extension is missing, instead of failing later at runtime.

- If someone else clones your project (or you deploy to production), they immediately know which extensions are needed.

```json
// composer.json

"require": {
    "php": "^8.1",
    "ext-bcmath": "*",
    "ext-curl": "*",
    "ext-mbstring": "*",
    "ext-openssl": "*",
    "ext-pdo": "*",
    "ext-tokenizer": "*",
    "ext-xml": "*",
    "laravel/framework": "^10.0",
    ...
}

```