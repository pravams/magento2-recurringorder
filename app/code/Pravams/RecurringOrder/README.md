# magento2-extensions
Magento 2 custom extensions that enhance magento 2 features:

## Recurring Order

This Recurring Order extension for Magento makes it easier for customers to create repeat orders by creating subscription orders based on a frequency for offline payment methods.  With this Recurring Order extension, customers can use it to create a subscription for the product. And the order will be placed automatically every week based on the frequency that they select.

* Frontend:-

    * You can add any number of products for subscription
    * Subscription can be customized to any frequency which can be daily, weekly, monthly, etc
    * The subscription can be created for any shipping method
    * The subscription is currently supported for simple products only
    * The subscription can be created for offline payment methods only
 
* Admin Backend:-

    * View the subscription details created by the customer from Magento Admin.
    * Ability to make the subscription inactive, active, or delete it.
    * View the orders placed through the subscription.

## Steps to Install

* Copy app/code/Pravams/RecurringOrder into your magento installation app/code folder
* run the below commands one by one:
```bash
php bin/magento setup:upgrade
```
```bash
php bin/magento setup:di:compile
```
```bash
php bin/magento cache:flush
```

## Test Details
This module has been tested using this tool <https://github.com/magento/magento-coding-standard>. Environment details are:-
* Magento 2.4.8
* Ubuntu 22.04.4 LTS
* PHP 8.4.6
* mysql Ver 8.4.5
* Apache/2.4.52
* Opensearch 2.19.1
* Composer version 2.7.9



