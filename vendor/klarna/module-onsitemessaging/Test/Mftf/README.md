# Klarna Onsitemessaging MFTF Tests

## Preconditions
Add the following credentials to `dev/tests/acceptance/.credentials`:

- klarna_us_data_id=
- klarna_eu_data_id=
- klarna_oc_data_id=

NOTE: Please also see the readme in the Kp module for any credentials you need to configure for it as well. The tests in this module depend on certain action groups in that module

## Run
Due to the need for configuration using credentials, all tests now run under the "KlarnaPayments" suite. And because we use one codebase for Magento Open Source and Magento Commerce we have separate suites for each Magento product. Here's how to execute the proper suite:

**Magento Open Source**
- `vendor/bin/mftf generate:suite KlarnaOnsitemssagingUS_OpenSource`
- `vendor/bin/mftf run:group -k KlarnaOnsitemssagingUS_OpenSource`

**Magento Commerce**
- `vendor/bin/mftf generate:suite KlarnaOnsitemssagingUS_Commerce`
- `vendor/bin/mftf run:group -k KlarnaOnsitemssagingUS_Commerce`
