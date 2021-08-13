# Vertex Tax Links for Magento 2

The Vertex Tax Links for Magento 2 module allows merchants to utilize Vertex Tax Links or Vertex Cloud to provide the relevant tax calculations for US-based sales.

## Public API

Vertex Tax Links provides a few key places for integration and customization.  These are all marked with the `@api` annotation.

**Note**: Anything not marked with the `@api` annotation may be modified or removed in future versions, unless specified in below list of public API access points

* `\Vertex\Tax\Api\LogEntryRepositoryInterface` 
  * Use this class to interface with the default Log Entry datastore (the database), or provide a preference to store 
logs in an external system.
* `\Vertex\Tax\Api\Data\LogEntryInterface`  
  * This interface represents a Log Entry.
* `\Vertex\Tax\Api\Data\LogEntrySearchResultsInterface`  
  * This interface represents a list of results for a search against the `LogEntryRepositoryInterface`
* `\Vertex\Tax\Api\InvoiceInterface::record`  
  * Use this method to record an invoice in Vertex  
  * Intercept this method to modify the Invoice's request or response  
* `\Vertex\Tax\Api\QuoteInterface::request`  
  * Use this method to request a tax quotation from Vertex  
  * Intercept this method to modify the quotation's request or response
* `\Vertex\Tax\Api\TaxAreaLookupInterface::lookup`
  * Use this method to perform address validation or look up possible tax areas for an address
* `vertex_customer_code` extension attribute on `\Magento\Customer\Api\Data\CustomerInterface`  
  Use this field and `\Magento\Customer\Api\CustomerRepositoryInterface` to load/save Vertex Customer Codes

## Adding new Flexible Fields

Flexible Fields are a feature of the Vertex Tax Engine designed to allow the passing of additional data that is not 
built into the API.  This additional data can then be used to create additional rules in the Vertex Tax Engine.

Vertex Tax Links for Magento 2 comes with several useful flexible field processors - including processors designed to 
allow for the sending of custom (EAV) attributes for Customers and Products.  In some cases these processors may not be
enough to send all the necessary data to the Vertex Tax Engine.  In such a scenario, it will become necessary to create
new flexible field processors.

There are three interfaces used for creating Flexible Field processors:

* `Vertex\Tax\Model\FlexField\Processor\ProcessorInterface`  
Implement this interface to provide information about what data your processor makes available for inclusion in 
 flexible fields.  Its method, `getAttributes` should return an array of 
 `Vertex\Tax\Model\FlexField\FlexFieldProcessableAttribute` objects indexed by their "attribute code."  The Invoice and
 TaxCalculation processor interfaces extend from this interface, so you should not need to include it in your implements
 statement.
* `Vertex\Tax\Model\FlexField\Processor\InvoiceFlexFieldProcessorInterface`  
Implement this interface to declare that your processor should be used for Vertex Invoice calls, and to provide the 
 data it makes available from a relevant Magento object: an Invoice, Order, or CreditMemo.  To work in all scenarios, 
 your processor MUST be able to retrieve the proper data from each object type.
* `Vertex\Tax\Model\FlexField\Processor\TaxCalculationFlexFieldProcessorInterface`  
Implement this interface to declare that your processor should be used for Vertex Quotation calls, and to provide the
 data it makes available from a Magento `QuoteDetailsItemInterface`.
 
If your flexible field modifies the amount of tax the buyer is responsible for, it must implement both the Invoice 
 and the TaxCalculation processor interfaces to work correctly in all cases.  If your processor does not result in the
 same data being passed during Invoice and Quotation calls, it will result in a scenario in which the amount of tax 
 recorded in the Vertex Tax Journal is different from the amount received from the buyer.
 
### Making Vertex Tax Links aware of your processor

Once you have your base class in place, make the Vertex module aware of it by adding it to the `processors` argument 
 for the object `Vertex\Tax\Model\FlexField\Processor\FlexFieldAttributeProcessor`.  Please view the global `di.xml` 
 file for the Vertex module to see how we do this for the default processors.
 
To summarize what you'll find there - the `processors` argument is a string indexed array.  Each index is a unique name
 identifying the processor, and the value is another string indexed array, expected to contain two keys with values:
 
* `sort-order` (number) - Where this processors is in the sort order.  The last processor declaring an attribute code is
 the processor that handles that attribute code.
* `processor` (object) - The processor you wrote

Once you have specified the new item(s) in your module's global di.xml file, any attributes your processor makes 
 available will begin to appear in the flexible field drop downs in the admin configuration section.
 
### Declaring Attributes

One of the methods you will need to implement is the `getAttributes` method from the `ProcessorInterface`.  As above 
 states, the resultant array must be indexed by the attribute code.  This attribute code should be unique, unless you
 are overriding an existing flex field processor's implementation.  In addition to being unique, this string *should*
 be all ASCII for ease of use.  Other than that, there are no requirements to what you specify in this string - though
 you may need to use it later to determine what data to fetch. 
 
In addition to the attribute code, the `FlexFieldProcessableAttribute` object you must return will need to have a 
label, an option group, a type, and a processor assigned to it.

* Label - A human readable label.  Should be passed through the localization layer if possible. (e.g. `__($label)
  ->render())`
* Option Group - A human readable label for the group this attribute belongs to.  Should be passed through the 
  localization layer.  Example groups are like objects.  "Product", "Customer."  Basically - where is the data coming 
  from.
* Type - One of the three type constants from `FlexFieldProcessableAttribute` - `TYPE_CODE`, `TYPE_DATE`, or 
 `TYPE_NUMERIC`.  Code represents a string.  Date a date object (will be passed to Vertex as `YYYY-MM-DD`, but when the
  value is fetched must be an instance of a `DateTimeInterface`).  Numeric represents a decimal or integer.
* Processor - A string representing the class name of the processor

### Fetching data for the Flexible Field

Depending on which interfaces you implement, you will need to implement methods for fetching your flexible field data
 using a variety of objects.  Objects you may be given include:
 
* `InvoiceItemInterface`
* `OrderItemInterface`
* `CreditmemoItemInterface`
* _and_ `QuoteDetailsItemInterface`

These items will be provided to your processor nearly exactly as Magento provides them to us during the various 
 events and interceptors used to fetch them.  In some cases, they may contain additional data that Magento would not 
 have normally passed during the event (such as some extension attributes), that we have added to improve quality of 
 development.  In some cases, extension attributes that you expect to exist may not exist if Vertex does not utilize
 them by default - simply because Magento does not include them in several cases.  You will need to fetch such 
 attributes yourself.
 
The one object that is very special in these regards is the `QuoteDetailsItemInterface`.  If you have not worked with
 the Magento Tax module before, you are unlikely to know what this object is.
 
#### QuoteDetailsItemInterface

This object belongs to the Magento_Tax module and is a container object for anything that can have tax charged 
 against it.  Vertex Tax Links adds several extension attributes to this object that can be used to retrieve additional 
 information:
 
* store_id
* quote_id
* product_id
* quote_item_id
* customer_id

If you would like to create additional extension attributes for use with your own processor, you can define them like
 normal in the `extension_attributes.xml` file.  You can then load the value of these extension attributes by 
 creating a plugin for `Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItem()`

## Testing

Vertex Tax Links comes with Unit, Integration, and Functional Acceptance tests.

The unit and integration tests may be ran from within Magento as part of Magento's unit and integration test suites.

### Functional Acceptance Tests

Vertex comes with functional acceptance tests that utilize the Magento Functional Testing Framework.

Running these tests requires MFTF to be setup.  Please refer to [Introduction to the Magento Functional Testing 
Framework](https://devdocs.magento.com/mftf/docs/introduction.html) 
for more information on setting up this environment.

After an MFTF environment is setup, the following steps must be taken:

#### Vertex Settings

Vertex Tax Links must be configured with a Trusted ID and company address that are already set up in Vertex to 
calculate and record taxes in Pennsylvania.

#### MFTF Settings

The path of the Test/Mftf folder within Vertex must be specified in the MFTF environment variable 
`CUSTOM_MODULE_PATHS`.  This variable should contain the full path on the system for the Mftf directory.

For example: (where `/var/www/example.org` is the root of a Magento 2 installation)

> `/var/www/example.org/vendor/vertex/module-tax/Test/Mftf`

Once configuration is complete, the tests may be run as specified in [Step 7 of the MFTF Getting Started notes](https://devdocs.magento.com/mftf/docs/getting-started.html#step-7-run-a-simple-test).

## Architecture

![Vertex Tax Links for Magento 2 ArchitectureI](https://i.imgur.com/kYmWfAi.png)

The core functionality of Vertex Tax Links for Magento 2 intercepts the tax request from the Magento software and relays it through a Vertex Tax Links-compatible service, such as Vertex Cloud.

This module uses a variety of models to convert a Magento Quote, Order, or Invoice object to a compatible Vertex request object.

### Request/Response logging

Vertex requests and responses are logged, by default to the database, by using the repository class `Vertex\Tax\Model\Repository\LogEntryRepository`, the data class/model `Vertex\Tax\Model\Data\LogEntry`, and the resource models `Vertex\Tax\Model\ResourceModel\LogEntry` and `Vertex\Tax\Model\ResourceModel\LogEntry\Collection`.

To replace or interact with this log, please utilize the `@api` annotated service contracts present in the `Vertex\Tax\Api` namespace.
