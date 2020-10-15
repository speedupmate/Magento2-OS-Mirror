/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/checkout-data'
], function ($, registry, checkoutData) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validationConfig: window.checkoutConfig.vertexAddressValidationConfig,
            addressValidator: null,

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                registry.get(
                    'checkout.steps.billing-step.payment.payments-list' +
                    '.before-place-order.billingAdditional' +
                    '.address-validation-message.validator',
                    function (validator) {
                        this.addressValidator = validator;
                    }.bind(this)
                );
                return this;
            },

            /**
             * @returns {self}
             */
            updateAddress: function () {
                this.registerAddressInvalidationTrigger();

                var billingData = checkoutData.getBillingAddressFromData();

                if (!this.validationConfig.isAddressValidationEnabled ||
                    this.addressValidator.isAddressValid ||
                    billingData === null ||
                    this.selectedAddress() && !this.isAddressFormVisible() ||
                    this.validationConfig.countryValidation.indexOf(billingData.country_id) === -1
                ) {
                    return this._super();
                }

                this.addressValidator.addressValidation().done(function () {
                    if (!this.validationConfig.showValidationSuccessMessage) {
                        return this.updateAddress();
                    }
                }.bind(this));
            },

            /**
             * When called, register a single (mind the "one") address invalidation trigger,
             * that sets the "this.addressValidator.isAddressValid = false" for any further billing address field change.
             */
            registerAddressInvalidationTrigger: function () {
                let that = this;
                $('fieldset')
                    .find('[data-form="billing-new-address"]')
                    .one(
                        'keyup change paste',
                        'input[name^="street"]' +
                        ', input[name="postcode"]' +
                        ', input[name="city"]' +
                        ', input[name="country_id"]' +
                        ', select[name="region_id"]',
                        function () {
                            that.addressValidator.isAddressValid = false;
                        });
            }
        });
    };
});
