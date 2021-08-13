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

## 1.2.0-RC1

###### What’s new
- We've added extra form fields to allow merchants to select the sender's from name in SMS messages.

###### Improvements
- We updated the structure and default sort order of our SMS Sends Report grid.
- In phone number validation, all error codes now resolve to an error message.

## 1.1.1

###### Bug fixes
- We've added some extra code to prevent customers from submitting telephone numbers without a country code.
- We fixed the positioning of the tooltip that is displayed alongside each SMS message textarea in the admin.

## 1.1.0

###### Bug fixes
- Our mixin for `Magento_Ui/js/form/element/abstract` now returns an object. [External contribution](https://github.com/dotmailer/dotmailer-magento2-extension-sms/pull/2)
- Our `telephoneValidatorAddress` mixin now returns the correct widget type. [External contribution](https://github.com/dotmailer/dotmailer-magento2-extension-sms/pull/3)

## 1.0.0
  
###### What’s new
- SMS notifications for new order confirmation, order update, new shipment, shipment update and new credit memo.
- SMS sender cron script to process and send queued SMS.
- Phone number validation in the customer account and at checkout.
- 'SMS Sends' report.
