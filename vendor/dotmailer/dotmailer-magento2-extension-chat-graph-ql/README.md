# dotdigital ChatGraphQl
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)

## About this module

**Dotdigitalgroup_ChatGraphQl** supports our [dotdigital Chat](https://github.com/dotmailer/dotmailer-magento2-extension-chat) module.
It provides type and resolver information for Magento to generate endpoints for:
- fetching chat configuration data from the instance
- sending customer data to update the chat profile

## Requirements

- This module requires the `Dotdigitalgroup_Chat` module v1.0.0+

## Endpoints

**Queries**
```
query GetChatData {
        chatData {
            is_enabled
            api_space_id
            cookie_name
        }
    }
```

**Mutations**
```
mutation UpdateChatProfile(
        $profileId: String!,
        $email: String,
        $firstname: String,
        $lastname: String
    ) {
        updateChatProfile(
            profileId: $profileId,
            email: $email,
            firstname: $firstname,
            lastname: $lastname
        )
    }
```
