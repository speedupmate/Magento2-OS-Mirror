/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'mage/translate',
    'uiRegistry',
    'uiComponent',
    'Vertex_AddressValidation/js/action/address-validation-request',
    'Vertex_AddressValidation/js/model/checkout/shipping/address-resolver',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList'
], function (
    $,
    $t,
    registry,
    Component,
    addressValidationRequest,
    addressResolver,
    fullScreenLoader,
    checkoutData,
    errorProcessor,
    messageContainer
) {
    'use strict';

    return Component.extend({
        validationConfig: window.checkoutConfig.vertexAddressValidationConfig || {},
        resolver: addressResolver,
        isAddressValid: false,
        message: null,
        defaults: {
            listens: {
                addressData: 'addressUpdated'
            },
            imports: {
                addressData: '${ $.provider }:shippingAddress'
            }
        },

        /**
         * Reset validation after address update
         */
        addressUpdated: function () {
            this.isAddressValid = false;
            this.updateAddress = false;

            if (this.message) {
                this.message.clear();
                this.message.showSuccessMessage = false;
            }
        },

        /**
         * @returns {Object}
         */
        initialize: function () {
            this._super();
            this.message = registry.get(this.parentName);

            return this;
        },

        /**
         * @returns {Object}
         */
        getFormData: function () {
            return checkoutData.getShippingAddressFromData();
        },

        /**
         * Triggers a request to the address validation builder and adds the response
         */
        addressValidation: function () {
            var deferred = $.Deferred();
            this.isAddressValid = false;
            fullScreenLoader.startLoader();

            addressValidationRequest(this.getFormData())
                .done(function (response) {
                    this.isAddressValid = true;
                    if (this.handleAddressDifferenceResponse(response) === true) {
                        deferred.resolve();
                    }
                }.bind(this)).fail(function (response) {
                    errorProcessor.process(response, messageContainer);
                }).always(function () {
                    fullScreenLoader.stopLoader();
                });

            return deferred;
        },

        /**
         * Get the message with the differences
         *
         * @param {Object} response
         */
        handleAddressDifferenceResponse: function (response) {
            var difference = this.resolver.resolveAddressDifference(response, this.getFormData());
            var showSuccessMessage = this.validationConfig.showValidationSuccessMessage || false;

            if (difference === true && showSuccessMessage) {
                this.message.setSuccessMessage($t('The address is valid'));
            } else if (difference.length === 0) {
                this.message.setWarningMessage($t('We did not find a valid address'));
            } else if (difference !== true) {
                this.message.setWarningMessage($t('The address is not valid'), difference);
            }
            return difference;
        },

        /**
         * Get the update message
         */
        updateVertexAddress: function () {
            this.resolver.resolveAddressUpdate();

            this.message.setSuccessMessage($t('The address was updated'));
            this.isAddressValid = true;
        }
    });
});
