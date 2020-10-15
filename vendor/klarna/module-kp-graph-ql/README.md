# Klarna_KpGraphQl module

The Klarna KpGraphQl module provides the possibility to create a Klarna Payments session via GraphQL.

## Usage

Please refer to the [Magento GraphQl checkout tutorial](https://devdocs.magento.com/guides/v2.3/graphql/tutorials/checkout/index.html) for 
the general approach. 
To be able to list Klarna Payments in your frontend, you are required to create a Klarna session. This needs to happen at any stage *before* you want to show available payment methods!

The mutation to create a Klarna session looks like this:

```graphql
mutation {
  createKlarnaPaymentsSession(input: {cart_id: "VaP2GtqImwsTEXEf7azrh4vopebXp8gl"}) {
    client_token
    payment_method_categories {
      identifier
      name
    }
  }
}
```

The input is the masked cart id, the successful output will look like this:

```graphql
{
  "data": {
    "createKlarnaPaymentsSession": {
      "client_token": "1yJhbGciOiJSUzI1NiIsImtpZCI6IjgyMzA1ZWJjLWI4MTEtMzYzNy1hYTRjLTY2ZWNhMTg3NGYzZCJ9.ewogICJzZXNzaW9uX2lkIiA6ICJkY2FiZDBmNy1iNTc2LTE5MzUtODQxMC1jNjM0YjE1OGU2YTciLAogICJiYXNlX3VybCIgOiAiaHR0cHM6Ly9rbGFybmEtcGF5bWVudHMtbmEucGxheWdyb3VuZC5rbGFybmEuY29tL3BheW1lbnRzIiwKICAiZGVzaWduIiA6ICJrbGFybmEiLAogICJsYW5ndWFnZSIgOiAiZW4iLAogICJwdXJjaGFzZV9jb3VudHJ5IiA6ICJVUyIsCiAgInRyYWNlX2Zsb3ciIDogZmFsc2UsCiAgImVudmlyb25tZW50IiA6ICJwbGF5Z3JvdW5kIiwKICAibWVyY2hhbnRfbmFtZSIgOiAiTjEwMDAyMiIsCiAgInNlc3Npb25fdHlwZSIgOiAiUEFZTUVOVFMiLAogICJjbGllbnRfZXZlbnRfYmFzZV91cmwiIDogImh0dHBzOi8vbmEucGxheWdyb3VuZC5rbGFybmFldnQuY29tIiwKICAiZXhwZXJpbWVudHMiIDogWyBdCn0.Qpjp1BfnDLr698A0W3vfW7--6GrDv-gT0mnmLVivAPK40Sxbcmf3eWzL-KR7YfaDVjgaOF3Xgs64pWs6Yg-RM01daVtwfkd84VK8ihQuTe8R2BUeG2l8-c_SV5lNyDxXRJV4AEvZwaqkS5WIFO2GDDUNM6q6OhX9SdxX116BKna72gSh4seXxFqGjCB91gUmtC1MFCLZpnRqjzMgDQUUajVY6ggYuBxN22ybKQaHTXSGrZZxcy0Q3hVD-FN4Wg04acdb8SgmYeLvnsLXZMsnWdaoslQAglIgJ-VyxarWzX_aCCft67kHR9fTfU055DHEcxqdb5GpOXh5ZALEgm0Dqw",
      "payment_method_categories": [
        {
          "identifier": "pay_later",
          "name": "Pay later in 30 days"
        },
        {
          "identifier": "pay_over_time",
          "name": "Buy now, pay later"
        }
      ]
    }
  }
}
```

This retrieved data will be needed for two things:

1. The client_token is required for your frontend init call (*Klarna.Payments.init()*)
2. The identifiers act as the payment method code. Together with the authorization_token, which will be 
returned to you on the frontend authorize call (*Klarna.Payments.authorize()*), they must be used when you 
are setting the payment method on the cart. Hence the mutation **setPaymentMethodOnCart** should look like this:

```graphql
mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "3WxC8gQn4Fbo55yqVLSiUFJ9fmEwnlxG"
      payment_method: {
          code: "klarna_pay_later"
          klarna: {
            authorization_token: "e9abc610-6748-256f-a506-355626551326"
        }
      }
  }) {
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
```

## Error handling

Any errors on the Klarna side will be exposed in the response, eg:

```graphql
{
  "errors": [
    {
      "message": "Client error: `POST https://api-na.playground.klarna.com/payments/v1/sessions` resulted in a `401 Unauthorized` response:\n<html>\r\n<head><title>401 Authorization Required</title></head>\r\n<body>\r\n<center><h1>401 Authorization Required</h1></cen (truncated...)\n",
    }
  ]
}
```

Any errors on the merchant environment will also appear in the same manner, eg:

```graphql
{
  "errors": [
    {
      "message": "Could not find a cart with ID VaP2GtqImwsTEXEf7azrh4vapebXp8gl"
    }
  ]
}
```
