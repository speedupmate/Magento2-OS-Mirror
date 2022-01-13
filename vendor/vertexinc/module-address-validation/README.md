# Vertex Address Validation for Magento 2

The Vertex Address Validation for Magento 2 module allows merchants to utilize Vertex Address Validation to ensure customer billing and shipping addresses are accurate for US Customers
 
## Public API

Vertex Address Validation provides one method as part of the public API.

Note: Anything not marked with the `@api` annotation may be modified or removed in future versions, unless specified in the below list of public API access points.

* `Vertex\AddressValidation\Api\AddressManagementInterface::getValidAddress`
  * Use this method to send a potentially invalid US address to Vertex and receive back the address Vertex believes it should be.
  
## Request/Response logging

At this time, the Vertex Address Validation module requires the Vertex Tax Links module to be installed in order to provide logging.  Please view the Vertex Tax Links README.md file for more information on how logging works.
