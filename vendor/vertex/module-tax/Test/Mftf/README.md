# Vertex Tax Links for Magento 2 Acceptance Tests

This module contains acceptance tests for Vertex Tax Links for Magento 2.

## Configuration

Please add the following keys to your MFTF `.credentials` file:

* `vertex_config_calculation_wsdl`
* `vertex_config_address_validation_wsdl`
* `vertex_config_trusted_id`
* `vertex_config_company_code`

Seller location data is otherwise assumed by Vertex for running the test scenarios.

## Test Case Values

In the Mftf\Data directory you will find a file named `generateTestCaseValues.php`.  This file calculates the various
totals that will be utilized throughout the testing files and stores them in the `VertexTestCaseValuesData` file.

We do this as all data values in MFTF should be hardcoded, and as we are working with tax data the percentages are 
likely to change in the future.  This file allows us to modify the percentage and update all tests at once, thanks to
the data file.
