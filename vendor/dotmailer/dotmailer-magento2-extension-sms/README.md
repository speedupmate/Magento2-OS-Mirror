# dotdigital Engagement Cloud for Magento 2
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)

## SMS module
  
### Overview
This module provides support for Transactional SMS notifications to Magento merchants. It automates SMS notifications on new order confirmation, order update, new shipment, shipment update and new credit memo.
  
### Requirements
- An active dotdigital Engagement Cloud account with the SMS pay-as-you-go service enabled.
- Available from Magento 2.3+
- Requires dotdigital extension versions:
  - dotdigitalgroup Email 4.10.0+
  
### Activation
- To enable the module, run:
```
composer require dotmailer/dotmailer-magento2-extension-sms
bin/magento setup:upgrade
```
- Ensure you have set valid API credentials in **Configuration > dotdigital > Account Settings**
- Head to **Configuration > dotdigital > Transactional SMS** for configuration.

## Credits
This module features an option to enable international telephone number validation. Our supporting code uses a version of the [International Telephone Input](https://github.com/jackocnr/intl-tel-input) JavaScript plugin. We've also borrowed some components from this [MaxMage Magento module](https://github.com/MaxMage/international-telephone-input). Kudos and thanks!

