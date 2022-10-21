# README #
### Installation by composer ###
```
composer require ik/magento-2-payment-quickpay
```

### Installation by copying ###
* Install quickpay-php-client by composer
```
composer require quickpay/quickpay-php-client 1.1.0
```
* Copy source files in folder app\code\HW\QuickPay\


### Run commands after installation ###
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:clean
```
