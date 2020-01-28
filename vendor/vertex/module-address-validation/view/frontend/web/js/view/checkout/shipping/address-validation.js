/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Vertex_AddressValidation/js/action/set-address-for-validation',
    'Vertex_AddressValidation/js/model/validation',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    _,
    ko,
    Component,
    setAddressActionForValidation,
    validationModel,
    checkoutData,
    fullScreenLoader
) {
    'use strict';

    return Component.extend({
        defaults: {
            messages: []
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe('messages');

            return this;
        },

        /**
         * Triggers a request to the address validation builder and adds the response
         */
        addressValidation: function () {
            var self = this;

            setAddressActionForValidation(checkoutData.getShippingAddressFromData()).done(
                function (response) {
                    var message = self.getAddressDifferenceResponse(response);

                    fullScreenLoader.stopLoader();
                    $(document).trigger('afterValidate', [
                        message,
                        window.checkoutConfig.vertexAddressValidationConfig.isAlwaysShowingTheMessage
                    ]);
                }
            );
        },

        /**
         * Retrieve messages
         *
         * @param {Object} message
         */
        getMessages: function (message) {
            this.messages.removeAll();
            this.messages.push(message);
        },

        /**
         * Removes all the messages
         */
        removeMessage: function () {
            this.messages.removeAll();
        },

        /**
         * Get the message with the differences
         *
         * @param {Object} apiResponse
         */
        getAddressDifferenceResponse: function (apiResponse) {
            var message = validationModel.resolveAddressDifference(apiResponse);

            window.localStorage.setItem('validated_shipping_address', JSON.stringify(apiResponse));
            this.getMessages(message);

            return message;
        },

        /**
         * Get the update message
         */
        updateVertexAddress: function () {
            var validAddressStorage = window.localStorage.getItem('validated_shipping_address'),
                message = validationModel.resolveShippingAddressInvalid(validAddressStorage);

            this.getMessages(message);
            window.localStorage.setItem('validated_shipping_address', JSON.stringify({}));

            return message;
        }
    });
});
