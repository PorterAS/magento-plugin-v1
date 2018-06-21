Convert Porterbuddy
===================

Porterbuddy is a delivery provider, and the module is a shipping method for the checkout process.

## Location discovery

- browser-based geo location - works only when the site runs on HTTPS
- location based on customer IP - requires Composer geoip2/geoip2 package

## Install Geoip2 using Composer

In order to use IP-based location discovery, geoip2/geoip2 package must be installed via Composer.
To include Composer autoloader, require package
[Magento Composer Installer](https://github.com/Cotya/magento-composer-installer) that will automatically
patch app/Mage.php for you.

Sample composer.json:

```
{
    "require": {
        "magento-hackathon/magento-composer-installer": "3.1.*",
        "geoip2/geoip2": "~2.0"
    },
    "extra":{
        "magento-root-dir": "./"
    }
}
```

Remember to add vendor/ dir to .gitignore and close it from web server requests.

## Install Porterbuddy using Composer

[Magento Composer Installer](https://github.com/Cotya/magento-composer-installer) can automatically
install Porterbuddy module via symlinks or by copying files. It will also take care of future
module updates.

Sample composer.json:

```
{
    "require": {
        "magento-hackathon/magento-composer-installer": "3.1.*",
        "convert/porterbuddy": "~2.0",
        "geoip2/geoip2": "~2.0"
    },
    "repositories": {
        "porterbuddy": {
            "type": "vcs",
            "url": "git@github.com:PorterAS/magento-plugin-v1.git"
        }
    },
    "extra":{
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
