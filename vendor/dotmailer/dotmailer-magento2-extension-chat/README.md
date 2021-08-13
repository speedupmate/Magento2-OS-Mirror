# dotdigital Engagement Cloud for Magento 2
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)
## Chat module

### Requirements

- This module requires the `Dotdigitalgroup_Email` module v4.10.0+

### Activation

- To enable the module, run:
 ```
 composer require dotmailer/dotmailer-magento2-extension-chat
 bin/magento setup:upgrade
 ```
- Ensure you have set valid API credentials in **Configuration > dotdigital > Account Settings**
- Turn on the chat bubble by enabling chat in **Configuration > dotdigital > Chat Settings**

## 1.4.0

###### Improvements
- We’ve replaced comapi.com URLs with their updated dotdigital.com equivalents.
- The `launchTimeout` delay in the widget embed code has been removed (there is already a delay without this).

###### Bug fixes
- The Chat API space id is now re-saved whenever the chat settings change, regardless of whether it was set before; this resolves a possible issue when configuring Chat across multiple websites. 
- We fixed a possible bug to do with checking for an API space id in browser local storage.

## 1.0.3

###### Improvements
- We've added a Content Security Policy whitelist for specific domains used by this module. [External contribution](https://github.com/dotmailer/dotmailer-magento2-extension-chat/pull/1)

## 1.0.2

###### Bug fixes
- Access tokens for the Chat API are now refreshed when they expire.
- We resolved some access control issues relating to non-admin user accounts.

## 1.0.1

###### What's new
- We've changed the _Chat_ menu item to _dotdigital Chat_ in the Marketing menu.

## 1.0.0

###### What’s new
- Engagement Cloud Chat is now available for Magento merchants. Existing clients can enable chat via **Engagement Cloud > Chat Settings** to start using this new channel.  
