/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Checkout/js/model/quote'
], function ($, _, uiRegistry, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            vertexMessage: {},

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();

                $(document).on(
                    'afterValidate',
                    function (event, vertexMessage, stopOnSuccess) {
                    if (!stopOnSuccess && vertexMessage.type === 0) {
                        this.vertexMessage = vertexMessage;
                        /*
                         * re-trigger validation - easiest way to continue
                         * When a vertexMessage is set, validation will allow
                         *  the customer to proceed w/out another round of
                         * Vertex Address Validation
                         */
                        this.setShippingInformation();
                        // Clear message after continuation
                        this.vertexMessage = {};
                    } else {
                        this.vertexMessage = vertexMessage;
                    }
                }.bind(this));

                return this;
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var superResult = this._super(),
                    self = this,
                    config = window.checkoutConfig,
                    validationConfig = config.vertexAddressValidationConfig,
                    shippingData = quote.shippingAddress(),
                    componentValidation = uiRegistry.get(
                        'checkout.steps.shipping-step.shippingAddress' +
                        '.before-shipping-method-form.shippingAdditional' +
                        '.shipping-address-messages'
                    );

                if (!validationConfig.isAddressValidationEnabled ||
                    !componentValidation ||
                    !superResult ||
                    !quote.shippingAddress().isEditable() ||
                    validationConfig.countryValidation.indexOf(shippingData.countryId) === -1
                ) {
                    return superResult;
                }

                if (Object.values(self.vertexMessage).length > 0) {
                    componentValidation.removeMessage();
                    this.vertexMessage = {};

                    return superResult;
                }

                if (superResult) {
                    componentValidation.addressValidation();

                    return false;
                }
                this.vertexMessage = {};

                return superResult;
            }
        });
    };
});
