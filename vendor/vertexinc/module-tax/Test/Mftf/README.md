# Vertex Tax Links for Magento 2 Acceptance Tests

## Configuration

**NOTE**: Invoice calls are made against the Calculation WSDL provided.  Do not provide the WSDL of a live service. 
Failure to heed this advice may result in incorrect entries in your Vertex Tax Journal.

Please add the following keys to your MFTF `.credentials` file:

* `vertex_config_calculation_wsdl`
* `vertex_config_address_validation_wsdl`
* `vertex_config_trusted_id`
* `vertex_seller_company_code`

Seller location data is otherwise assumed by Vertex for running the test scenarios.

## Tax Assist Rules

Vertex MFTF tests are run against an instance of the Vertex Tax Calculation engine.  Please ensure that the following
tax assist rules are created in your Vertex account:

### VFF-T2

Name: VFF-T2  
Phase: Pre-Calculation  
Description: 
> If the value of Flex Code 1 is mapped to the Magento Product Category Gear and the value of Electronic is passed, then 
>override the physical origin to be 2720 N River Rd, River Grove, IL 60171.

Condition:
```
IF ( Flex.input.FlexCode1 = "Electronic" )
SET physicalOrigin.streetAddress
  TO "2720 N River Rd"
SET physicalOrigin.city
  TO "River Grove"
SET physicalOrigin.mainDivision
  TO "IL"
SET physicalOrigin.postalCode
  TO "60171"
```

### VFF-T3

Name: VFF-T3  
Phase: Pre-Calculation  
Description: 
> If the value of Flex Code 1 is mapped to the Magento Product Eco Collection and the value of 1 is passed (1 = Yes), 
>then override the physical origin to be 215 S Gilbert St, Danville, IL 61832.

Condition:
```
IF ( Flex.input.FlexCode1 = "1" )
SET physicalOrigin.streetAddress
  TO "215 S Gilvert St"
SET physicalOrigin.city
  TO "Danville"
SET physicalOrigin.mainDivision
  TO "IL"
SET physicalOrigin.postalCode
  TO "61832"
```

## Test Case Values

In the Mftf\Data directory you will find a file named `generateTestCaseValues.php`.  This file calculates the various
totals that will be utilized throughout the testing files and stores them in the `VertexTestCaseValuesData` file.

We do this as all data values in MFTF should be hardcoded, and as we are working with tax data the percentages are 
likely to change in the future.  This file allows us to modify the percentage and update all tests at once, thanks to
the data file.
