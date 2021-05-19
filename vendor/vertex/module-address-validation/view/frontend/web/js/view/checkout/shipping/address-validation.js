/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiRegistry',
    'uiComponent',
    'Vertex_AddressValidation/js/action/address-validation-request',
    'Vertex_AddressValidation/js/model/checkout/shipping/address-resolver',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList',
    'Vertex_AddressValidation/js/validation-messages',
    'Vertex_AddressValidation/js/action/convert-quote-address',
    'Vertex_AddressValidation/js/action/build-quote-address',
    'Vertex_AddressValidation/js/model/difference-determiner'
], function (
    $,
    registry,
    Component,
    addressValidationRequest,
    addressResolver,
    fullScreenLoader,
    checkoutData,
    errorProcessor,
    messageContainer,
    validationMessages,
    convertQuoteAddress,
    buildQuoteAddress,
    differenceDeterminer
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
            const formData = checkoutData.getShippingAddressFromData(),
                checkoutProvider = registry.get('checkoutProvider');

            if (checkoutProvider && checkoutProvider.dictionaries && checkoutProvider.dictionaries.region_id) {
                const region = registry.get('checkoutProvider').dictionaries.region_id.find(function (obj) {
                    return obj.value === formData.region_id;
                });
                if (region && region.label) {
                    formData.region = region.label;
                }
            }

            return formData;
        },

        /**
         * Triggers a request to the address validation builder and adds the response
         */
        addressValidation: function () {
            var deferred = $.Deferred();
            this.isAddressValid = false;
            fullScreenLoader.startLoader();

            addressValidationRequest(convertQuoteAddress(this.getFormData()))
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
         * @param {?CleanAddress} response
         */
        handleAddressDifferenceResponse: function (response) {
            if (response === null || !Object.keys(response).length) {
                this.message.setWarningMessage(validationMessages.noAddressFound);
                return;
            }

            this.resolver.responseAddressData = buildQuoteAddress(response);

            const differences = differenceDeterminer(convertQuoteAddress(this.getFormData()), response),
                showSuccessMessage = this.validationConfig.showValidationSuccessMessage || false;

            if (differences.length === 0 && showSuccessMessage) {
                this.message.setSuccessMessage(validationMessages.noChangesNecessary);
            } else if (differences.length > 0) {
                this.message.setWarningMessage(validationMessages.changesFound, differences, response);
            } else {
                return true;
            }
        },

        /**
         * Get the update message
         */
        updateVertexAddress: function () {
            this.resolver.resolveAddressUpdate();

            this.message.setSuccessMessage(validationMessages.addressUpdated);
            this.isAddressValid = true;
        }
    });
});
