# Klarna Payments MFTF Tests

## Preconditions
Setup the following `.credentials` (dev/tests/acceptance/.credentials):

- klarna_us_merchant_id=
- klarna_us_shared_secret=
- klarna_eu_merchant_id=
- klarna_eu_shared_secret=

## Run
Due to the need for configuration using credentials, all tests now run under the "KlarnaPayments" suite.  Execute this using:

- `vendor/bin/mftf run:group -r KlarnaPaymentsUS`
