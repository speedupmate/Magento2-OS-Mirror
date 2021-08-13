# dotdigital EmailGraphQl
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)

## About this module

**Dotdigitalgroup_EmailGraphQl** supports our [core module](https://github.com/dotmailer/dotmailer-magento2-extension).
It provides type and resolver information for Magento to generate endpoints for:
- fetching tracking configuration data from the instance
- fetching email capture configuration from the instance
- retrieving order data for customers and guests on the order confirmation page 
- updating the quote email address

## Requirements

- This module requires the `Dotdigitalgroup_Email` module v4.10.0+

## Endpoints

**Queries**
```
query getTrackingData {
        trackingData {
            page_tracking_enabled
            roi_tracking_enabled
            wbt_profile_id
            region_prefix
        }
    }

query isEasyEmailCaptureNewsletterEnabled {
        emailCaptureNewsletter {
            is_enabled
        }
    }

query isEasyEmailCaptureCheckoutEnabled {
        emailCaptureCheckout {
            is_enabled
        }
    }

query getOrderDetails($orderNumber: String!) {
        orderData(orderId: $orderNumber) {
            items
            total
        }
    }
```

**Mutations**
```
mutation updateQuoteEmail($email: String!, $cartId: String!) {
        updateQuoteEmail(
            email: $email,
            cartId: $cartId
        )
    }
```
