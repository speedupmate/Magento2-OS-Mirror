/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'uiRegistry',
    'Magento_Checkout/js/model/quote'
], function (registry, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validationConfig: window.checkoutConfig.vertexAddressValidationConfig,
            shippingData: null,
            addressValidator: null,

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                registry.get(
                    'checkout.steps.shipping-step.shippingAddress' +
                    '.before-shipping-method-form.shippingAdditional' +
                    '.address-validation-message.validator',
                    function (validator) {
                        this.addressValidator = validator;
                    }.bind(this)
                );

                this.shippingData = quote.shippingAddress();
                return this;
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var superResult = this._super();

                // Proceed with saving the address
                if (!this.validationConfig.isAddressValidationEnabled ||
                    !superResult ||
                    this.addressValidator.isAddressValid ||
                    !quote.shippingAddress().isEditable() ||
                    this.validationConfig.countryValidation.indexOf(this.shippingData.countryId) === -1
                ) {
                    this.addressValidator.message.clear();
                    return superResult;
                }

                // Run address validation
                if (superResult) {
                    this.addressValidator.addressValidation().done(function () {
                        if (!this.validationConfig.showValidationSuccessMessage) {
                            this.setShippingInformation();
                        }
                    }.bind(this));

                    return false;
                }
                return superResult;
            }
        });
    };
});
