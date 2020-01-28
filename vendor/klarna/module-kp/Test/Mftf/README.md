# Klarna Payments MFTF Tests

## Preconditions
Add the following credentials to `dev/tests/acceptance/.credentials`:

- klarna_us_merchant_id=
- klarna_us_shared_secret=
- klarna_eu_merchant_id=
- klarna_eu_shared_secret=

## Run
Due to the need for configuration using credentials, all tests now run under the "KlarnaPayments" suite. And because we use one codebase for Magento Open Source and Magento Commerce we have separate suites for each Magento product. Here's how to execute the proper suite:   

**Magento Open Source**
- `vendor/bin/mftf generate:suite KlarnaPaymentsUS_OpenSource`
- `vendor/bin/mftf run:group -k KlarnaPaymentsUS_OpenSource`

**Magento Commerce**
- `vendor/bin/mftf generate:suite KlarnaPaymentsUS_Commerce`
- `vendor/bin/mftf run:group -k KlarnaPaymentsUS_Commerce`
