/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'uiRegistry',
    'Magento_Checkout/js/checkout-data'
], function (registry, checkoutData) {
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
            }
        });
    };
});
