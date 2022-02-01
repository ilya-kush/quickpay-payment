# README #

### Installation ###

* Install quickpay-php-client by composer
```
composer require quickpay/quickpay-php-client >=1.1.*
```
* Copy source files in folder app\code\HW\QuickPay\
* Run commands
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:clean
```
