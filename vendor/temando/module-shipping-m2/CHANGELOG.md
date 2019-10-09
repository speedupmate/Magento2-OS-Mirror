## [1.6.1] - 2019-08-16
###Fixed

- Ready At date/time is displayed relative to shipping location on dispatches
- Pickup location opening hours are displayed relative to location
- Return types allow WSDL to generate correctly
- Remove incorrect layout update in product attribute mapping layout
- Integration tests now run to success
- Configurable products correctly inherit product attribute values
- Product attribute mappings are not refreshed on a failed delete
- Allow saving of empty product attribute mappings
- Remove product attribute types from mapping that can't be sent to the platform
- Validate product attribute type values can be sent to the platform
- Multiple response relationships are able to be mapped

## [1.6.0] - 2019-08-05
### Added

- Improve MFTF test coverage
- Batch processing improvements
- Checkout performance improvements
- Moved locations configuration to platform
- Update shipping address via PATCH request
- Moved carriers configuration to platform
- Bundled product shipping
- Add customer group and site to order request
- Product attribute to shipping attribute mapping

### Changed

- Reorder dimensions in product shipping options
- PHP version constraint

### Fixed

- Allow orders to reship if cancelled from the platform

## [1.5.3] - 2019-05-20
### Fixed

- Event stream cron job
- Retrieving shipping labels from the platform

## [1.5.2] - 2019-05-17
### Fixed

- Validate API URL host name in module configuration


## [1.5.1] - 2019-02-15
### Fixed

- Display rates for selected collection point or pickup location in checkout

## [1.5.0] - 2019-01-18
### Added

- Provide access to *Experience Portal* from module configuration
- Cancel shipments
- Print pickup packing slips with barcode

### Changed

- Separate order qualification ("collect rates") from order creation at the platform

### Fixed

- Handle missing declared export value gracefully
- Prevent infinite loop during totals re-collect
- Allow multiple sets of opening hours per day for Click & Collect locations

## [1.4.6] - 2018-11-06
### Fixed

- Static test violations

## [1.4.5] - 2018-11-01
### Fixed

- Select delivery locations in checkout

## [1.4.4] - 2018-10-25
### Fixed

- Incorporate MFTF schema changes
- Improve error handling

## [1.4.3] - 2018-10-23
### Fixed

- Support multi-master setup for Click & Collect feature

## [1.4.2] - 2018-10-10
### Fixed

- Display location opening hours in pickup ready email

## [1.4.1] - 2018-09-28
### Fixed

- Exclude pickup orders from shipment batch processing
- Location filter in pickups grid
- Admin orders, including rates in non-default stores
- Prevent empty references to remote orders in database

## [1.4.0] - 2018-08-30
### Added

- Click & Collect: Enable customers to collect items from a pickup location

## [1.3.14] - 2019-08-20
### Fixed

- Add context to configurable object whitelist

## [1.3.13] - 2019-08-16
### Fixed

- Fix rendering of tooltips on order ship

## [1.3.12] - 2019-08-05
### Fixed

- Rounding pre-filled package weights
- Display duties and taxes on quotes where applicable
- Link to return shipment when solving errors for return shipment dispatches
- PHP version constraint

## [1.3.11] - 2019-05-16
### Fixed

- Validate API URL host name in module configuration

## [1.3.10] - 2019-02-11
### Fixed

- Read error details from platform response
- Show experiences configuration link in admin menu

## [1.3.9] - 2019-01-18
### Fixed

- Allow tracking URLs on package level
- Support free shipping via cart price rules
- Validate API URL protocol in module configuration

## [1.3.8] - 2018-12-19
### Fixed

- Batch Processing
  - Replace invocation of M2 REST API during batch creation
  - Improve error highlighting during batch creation
  - Show info message on batch view page if no documentation is available
- Event Synchronization
  - Fix item quantities when the same SKU was shipped in separate packages
  - Keep events in the queue for distributed event consumption

## [1.3.7] - 2018-10-17
### Fixed

- Use collection point address during batch processing
- Decrease number of API requests during shipment synchronization
- Improve error handling while reading shipping cost from webservice

## [1.3.6] - 2018-10-10
### Fixed

- Display collection point address in batch details page if applicable
- Remove attribute group assignments for product dimensions attributes
- Add translation capabilities to activation notice

## [1.3.5] - 2018-09-28
### Fixed

- Select individual shipments for dispatch
- Display activation notice on RMA Shipment page if API credentials are not configured

## [1.3.4] - 2018-09-13
### Fixed

- Manifest order with instant payment methods, e.g. PayPal Express
- Display tracking popup for multi-package shipments
- Enhance dispatch details page
  - Add pickup request number
  - Add pickup charges
  - Add carrier notices

## [1.3.3] - 2018-08-28
### Fixed

- [`#17363`](https://github.com/magento/magento2/issues/17363) Improve sanity check before accessing shipping method property

## [1.3.2] - 2018-08-17
### Fixed

- Improve performance
- Display collection point experiences only after a delivery location was chosen

## [1.3.1] - 2018-08-07
### Fixed

- Consider virtual products in multi shipping checkout
- Prevent exception when unable to display a shipment's capability value

### Added

- Delivery Options for UPS
    - Adult Signature
    - Direct Delivery

### Changed

- Improved error messages for dispatch errors
- Improved error messages during booking a shipment with _Magento Shipping_

## [1.3.0] - 2018-07-23
### Added

- Bulk Booking of Shipments

## [1.2.9] - 2018-07-13
### Fixed

- Consider partially shipped orders during shipment synchronization
- Download shipping labels to database if shipment was created through auto-processing or shipment synchronization
- UI display issues:
  - Enhance dispatch error messages
  - Add customer reference number
  - Add _Delivery Availability_ capability
  - Add UPS _High Value Report_ documentation

## [1.2.8] - 2018-06-15
### Fixed

- Limit shipping methods after collection point selection

## [1.2.7] - 2018-06-08
### Fixed

- Comparison of value-added shipping services in multishipping checkout
- Confirmation messages in grid delete actions

## [1.2.6] - 2018-05-31
### Fixed

- Connection name fallback in single-master setups

## [1.2.5] - 2018-05-17
### Added

- Display module version number in shipping method configuration

### Fixed

- Support multi-master setup ([split database performance solution](https://devdocs.magento.com/guides/v2.2/config-guide/multi-master/multi-master.html)).
- Render collection point add-on on shipment details page

## [1.2.4] - 2018-05-04
### Fixed

- Hide return shipment table heading if no rows are displayed
- Update result message if no collection points were found
- Show only regular addresses if no collection point was chosen during checkout
- Fix loading virtual orders

### Changed

- Save value-added shipping services in checkout through dedicated webapi endpoint

## [1.2.3] - 2018-04-27
### Fixed

- Checkout component loading error
- Display documentation on dispatch details page
- Display addresses from platform on shipment details page

## [1.2.2] - 2018-04-23
### Fixed

- Support apostrophes in customer addresses
- Limit shipping methods when collection point was chosen
- Display selected collection point in checkout sidebar
- Format collection point opening hours

## [1.2.1] - 2018-04-16
### Fixed

- Adapt to collection point API changes
- Display selected collection point in admin panel

## [1.2.0] - 2018-04-11
### Added

- Pre-Booked Returns: Automatically create return shipment labels for new shipments
- Auto-Processing: Automatically create shipments for incoming orders
- Collection Points: Enable customers to collect parcels from a drop point

## [1.1.3] - 2018-03-27
### Fixed

- Hide RMA return shipments tab when order was not shipped with _Magento Shipping_.

## [1.1.2] - 2018-03-08
### Fixed

- Remove selection column from return shipments grid with no mass action.

## [1.1.1] - 2018-03-02
### Added

- Server-side pagination for Dispatch grid

## [1.1.0] - 2018-02-28
### Added

- Create *Ad-hoc Return* labels with return shipment tracking (builds upon `Magento_Rma`)
- Validate that package weight is less than packaging max weight on order ship page
- Display additional details on shipment view page

### Fixed

- [`#12921`](https://github.com/magento/magento2/issues/12921) Perform type check on extension attributes during quote address updates
- Enable componentry loading in IE 11
- Use base currency in order qualification requests
- Remove duplicate navigation bar from carrier registration page

## [1.0.4] - 2017-12-06
### Fixed

- Complete error in previous release reverting zend-code v3.2.0 compatibility

## [1.0.3] - 2017-12-06
### Revert

- Establish compatibility to zend-code package v3.2.0 and up

### Fixed

- Sustain backwards compatibility in estimate-shipping-methods-by-address-id REST API call

## [1.0.2] - 2017-12-05
### Fixed

- Establish compatibility to zend-code package v3.2.0 and up

## [1.0.1] - 2017-12-05
### Changed

- Update merchant onboarding link

## [1.0.0] - 2017-12-04
### Fixed

- Consider admin token lifetime for REST API access

## [0.3.9] - 2017-12-01
### Changed

- Display fixed location value in tracking popup progress details

## [0.3.8] - 2017-12-01
### Fixed

- Change token type for REST API access

## [0.3.7] - 2017-11-25
### Fixed

- Prevent componentry JS from being minified twice

## [0.3.6] - 2017-11-21
### Fixed

- Remove duplicate timezone calculation in tracking popup
- Consider line item discount in order requests

## [0.3.5] - 2017-11-14
### Added

- Validate credentials in shipping method configuration

### Fixed

- Refresh shipping rates on address changes in checkout
- Add billing address in order requests
- Add product categories in order requests
- Show number of selected grid rows

## [0.3.4] - 2017-10-26
### Added

- Select value-added shipping services in multishipping checkout

### Fixed

- Read selected mass action IDs in grid listings
- Add page size option in grid listings

## [0.3.3] - 2017-10-19
### Changed

- Update support link in module configuration
- Display activation notice in config area

### Fixed

- Consider _Show Method if Not Applicable_ config setting
- Action button URL in locations grid

## [0.3.2] - 2017-10-02
### Changed

- Establish Magento® 2.2.0 compatibility, drop 2.1.x compatibility

### Fixed

- Select value-added shipping services in guest checkout

## [0.3.1] - 2017-09-26
### Security

- Sanitize input, escape output

## [0.3.0] - 2017-09-18
### Added

- Synchronize shipment entities created from 3rd party systems (e.g. WMS)
- Select value-added shipping services in checkout
- Display packaging details on _View Shipment_ page
- Display API entity IDs on _View Order_ and _View Shipment_ page
- Include guide to handle dispatch problems
- Set API credentials in module config section
- Delete registered carriers, locations, and containers from merchant account
- Edit registered carriers

### Changed

- Move merchant onboarding info (activation, getting started) to module config section
- Use localized endpoints after initial API authentication
- Error Logging (always log errors, add response headers)
- Extend _My Carriers_ grid columns
- Display carrier name instead of shipping method name in tracking popup

### Removed

- Tracking link in shipment confirmation email
- Merchant account registration (moved to external platform)

### Fixed

- Adapt API schema changes
