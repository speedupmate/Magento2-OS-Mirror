/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function (
    $,
    checkoutData,
    uiRegistry
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            vertexMessage: {},

            /**
             * @returns {self}
             */
            updateAddress: function () {
                var self = this,
                    config = window.checkoutConfig,
                    validationConfig  = config.vertexAddressValidationConfig,
                    billingData = checkoutData.getBillingAddressFromData(),
                    componentValidation = uiRegistry.get(
                        /*eslint max-len: ["error", { "ignoreStrings": true }]*/
                        'checkout.steps.billing-step.payment.payments-list.before-place-order.billingAdditional.billing-address-messages'
                    );

                if (!validationConfig.isAddressValidationEnabled ||
                    !componentValidation ||
                    billingData === null ||
                    this.selectedAddress() && !this.isAddressFormVisible() ||
                    validationConfig.countryValidation.indexOf(billingData.country_id) === -1
                ) {
                    return this._super();
                }

                $(document).on('afterValidateBilling', function (e, resp, vertexMessage, continueToNext) {
                    if (!continueToNext && vertexMessage.type === 0) {
                        self.vertexMessage = {};
                        self._super();

                        return;
                    }

                    if (continueToNext) {
                        self.vertexMessage = vertexMessage;
                    }
                });

                if (Object.values(self.vertexMessage).length > 0) {
                    componentValidation.removeMessage();
                    this.vertexMessage = {};

                    return self._super();
                }

                componentValidation.addressValidation();
            }
        });
    };
});
