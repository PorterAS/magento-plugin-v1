Convert Porterbuddy
===================

Porterbuddy is a delivery provider, and the module is a shipping method for the checkout process.


## Installation using Composer

[Magento Composer Installer](https://github.com/Cotya/magento-composer-installer) will automatically
install Porterbuddy module via symlinks or by copying files. It will also take care of future
module updates.

Sample composer.json:

```
{
    "require": {
        "convert/porterbuddy-magento": "~2.0"
    },
    "extra": {
        "magento-root-dir": "./",
        "magento-deploystrategy":"copy"
    }
}
```


## Running unit tests

- install dev composer dependencies

    Sample composer.json:
    ```
    {
        "require": {
            "phpunit/phpunit": "5.7.*",
            "phpunit/php-invoker": "1.1.x-dev",
            "phpunit/dbunit": "2.*"
        }
    }
    ```

- `vendor/bin/phpunit vendor/convert/porterbuddy/app/code/community/Convert/Porterbuddy/Test/Unit/`
