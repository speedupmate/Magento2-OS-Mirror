
6.3.4 / 2019-08-28
==================

  * MAGE-383: Fix issue preventing placing an order after changing billing country
  * MAGE-518: Fix issue preventing re-order when replacing cart items

6.3.3 / 2019-08-14
==================

  * Add MFTF tests and custom suite

6.3.2 / 2019-08-02
==================

  * MAGE-1177: Replace separate style files with one module source

6.3.1 / 2019-08-02
==================

  * MAGE-803 Fix issue where billing address changes weren't always used
  * MAGE-1005 Convert CSS to LESS
  * Some misc checkstyle cleanup
  * Update PHP versions supported

6.3.0 / 2019-07-02
==================

  * MAGE-484 Converted to declarative database schema

6.2.0 / 2019-06-10
==================

  * MAGE-58 Fix issues reported by static tests
  * MAGE-69 Added option and support to enable B2B payments
  * MAGE-250 Change coding standards to use Marketplace version
  * MAGE-315 Add translations
  * MAGE-398 Add css rules to swap logo and text
  * MAGE-668 Fix issue with saving organization name

6.1.1 / 2019-04-24
================

  * Remove MFTF tests for now to avoid breaking default suite

6.1.0 / 2018-12-21
================

  * MAGE-130 Add test for order creation as guest without special options
  * MAGE-131 Add test for order creation as logged in customer without special option
  * MAGE-132 Add test for order creation as guest with coupon test
  * MAGE-133 Add test for order creation as logged in customer with coupon
  * MAGE-134 Add test on invoice creation on klarna order
  * MAGE-135 Add full credit memo test
  * MAGE-136 Add partial credit memo test
  * MAGE-137 Add test for cancel order
  * MAGE-140 Add test suites
  * MAGE-213 Add Pay Over Time test for cancel order

6.0.1 / 2018-12-06
==================

  * PPI-583 Cleanup install/upgrade scripts

6.0.0 / 2018-11-12
==================

  * Artificially increment major version to stop code from updating on 2.2.4/2.2.5

5.5.4 / 2018-10-15
==================

  * PPI-559 Allow OM module to be version 4.x or 5.x
  * PPI-579 Fix system configuration to remove unused settings

5.5.3 / 2018-10-09
==================

  * PPI-557 Fix issue with running under Magento Commerce with split DB
  * PPI-577 Fix MFTF test

5.5.2 / 2018-09-27
==================

  * PPI-557 Fix checkout doesn't work after enabling Klarna
  * PPI-561 Fix composer requirements after 2.3.0 change

5.5.1 / 2018-08-31
==================

  * PPI-500 2.3.0 Compatibility updates

5.5.0 / 2018-08-24
==================

  * PI-397 Disable purchase button if payment declined
  * PPI-450 Add initial MFTF test
  * PPI-497 Fix 'elements with non-unique id' errors
  * PPI-498 Remove 'store view' span
  * PPI-499 Fix HTML tags as string in admin table
  * PPI-500 Add support for PHP 7.2 and Magento 2.3

5.4.4 / 2018-08-15
==================

  * PPI-465 Fix issue with loading of payment methods when cart is virtual
  * PPI-464 Fix issue with billing address form not loading

5.4.3 / 2018-07-26
==================

  * PPI-449 Cleanup code

5.4.2 / 2018-07-25
==================

  * PPI-449 Feedback from Magento for 2.2.6 release
  * PPI-403 Use the onboarding model
  * PPI-449 Fixed not existing column in upgrade data script

5.4.1 / 2018-07-24
==================

  * PPI-449 Fix table name

5.4.0 / 2018-07-23
==================

  * PI-385 Allow KP to be disabled at default scope but enabled at website scope
  * PPI-317 Add support for Fixed Product Tax
  * PPI-383 Fix setup scripts
  * PPI-403 Add link for Klarna on boarding - Phase 1

5.3.3 / 2018-06-26
==================

  * PPI-383 Fix duplicate logo when viewing order in admin

5.3.2 / 2018-06-08
==================

  * PPI-383 Migrate from hard-coded mapping to dynamic name & assets

5.2.3 / 2018-05-30
==================

 * PI-289 Remove exclusion as we now generate a 'min' file

5.2.0 / 2018-05-14
==================

  * PPI-357 Retrieve payment_method_categories array on update_session

5.1.3 / 2018-04-20
==================

  * Fix issue related to core module updates
  * BUNDLE-1145 Change place order flow to better handle re-enabling button

5.1.2 / 2018-04-11
==================

  * Replace uses of isPlaceOrderActionAllowed with showButton

5.1.1 / 2018-04-10
==================

  * Add dependency on magento/module-checkout back in to composer.json

5.1.0 / 2018-04-09
==================

  * Combine all CHANGELOG entries related to CBE program
  * Add Gift Wrap support

4.0.5 / 2018-02-13
==================

  * Fix code style according to Bundle Extension Program Feedback from 13FEB

4.0.4 / 2018-02-12
==================

  * Bundled Extension Program Feedback from 2018-02-12

4.0.3 / 2018-02-09
==================

  * Fix method signature
  * Fix version check for adding payment_methods category

4.0.2 / 2018-02-08
==================

  * Mark all quotes as inactive so that switch over to new payments endpoint happens

4.0.1 / 2018-02-02
==================

  * Remove title from config as it is no longer configurable

4.0.0 / 2018-02-02
==================

  * Add additional info to debug/error logs
  * Change to use payments endpoint

3.2.0 / 2018-01-24
==================

  * Allow KCO and KP to be installed at the same time
  * Normalize composer.json
  * Change conflicts to replace
  * Change User-Agent format and add additional information
  * Change session validator to just verify merchant_id and shared_secret are not blank
  * Add testing configs

3.1.2 / 2017-11-15
==================

  * Fix missing imports

3.1.1 / 2017-11-15
==================

  * Fix issues with Guzzle update
  * Remove reference to unused code
  * Minor code corrections

3.1.0 / 2017-11-13
==================

  * Move payment configuration section into 'Recommended' section
  * Add better error handling
  * Add additional logging

3.0.0 / 2017-10-30
==================

  * Fix for User-Agent not yet set
  * Change code to support Guzzle 6.x
  * Update to 3.0 of klarna/module-core

2.0.2 / 2017-10-18
==================

  * Fix issue with error message when API credentials are bad
  * Remove email sender as it creates duplicate emails
  * Update to new logos

2.0.1 / 2017-10-12
==================

  * Remove use of initialized property as it is deprecated

2.0.0 / 2017-10-04
================

  * Move all enterprise functions into other modules to support single Marketplace release

1.2.4 / 2017-10-04
==================

  * Bump version in module.xml to handle version numbers differently

1.2.3 / 2017-10-02
==================

  * Handle for payment method not being configured and not being enabled

1.2.2 / 2017-09-28
==================

  * Remove dependencies that are handled by klarna/module-core module

1.2.1 / 2017-09-25
==================

  * Move api.js loading to layout XML to fix RequireJS errors

1.2.0 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package
  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢
  * Remove require-dev section as it is handled in core module

1.1.0 / 2017-08-22
==================

  * Change klarna.js reference per KCC-668
  * Add klarnacdn to js minify exclude list

1.0.3 / 2017-08-16
==================

  * Rollback api.js change as KCC-668 appears to be stalled
  * Change data-sharing setting to only work for US market

1.0.2 / 2017-08-09
==================

  * Change to use StoreManagerInterface instead of StoreInterface
  * Change api.js to new generic location

1.0.1 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

1.0.0 / 2017-05-15
==================

  * Initial Release

