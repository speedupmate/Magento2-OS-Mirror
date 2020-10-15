# dotdigital Engagement Cloud for Magento 2
## Chat module

### Requirements

- This module requires the `Dotdigitalgroup_Email` module v4.3.0+ [Note: the 4.1.0 version shipped with Magento 2.3.4 can also be used]

### Activation

- To enable the module, run:
 ```
 composer require dotmailer/dotmailer-magento2-extension-chat
 bin/magento setup:upgrade
 ```
- Ensure you have set valid API credentials in **Configuration > dotdigital > Account Settings**
- Turn on the chat bubble by enabling chat in **Configuration > dotdigital > Chat Settings**

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
