# Klarna KpGraphQl api functional tests

## Preconditions
Your phpunit_graphql.xml needs to contain your shop URL and your admin username + password:

```xml
<!-- Webserver URL -->
<const name="TESTS_BASE_URL" value="https://yourshop.com"/>
<!-- Webserver API user -->
<const name="TESTS_WEBSERVICE_USER" value="admin"/>
<!-- Webserver API key -->
<const name="TESTS_WEBSERVICE_APIKEY" value="123456"/>
```

## Run
```
vendor/bin/phpunit -c dev/tests/api-functional/phpunit_graphql.xml app/code/Klarna/KpGraphQl/Test/ApiFunctional/CreateKlarnaPaymentsSessionTest.php
```

Please also refer to the documentation:
 
https://devdocs.magento.com/guides/v2.4/graphql/functional-testing.html
