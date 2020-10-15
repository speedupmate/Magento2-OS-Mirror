/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'ko',
    'uiRegistry',
    'uiComponent',
    'Vertex_AddressValidation/js/validation-messages',
    'Vertex_AddressValidation/js/action/address-validation-request',
    'Vertex_AddressValidation/js/model/customer/address-resolver',
    'Vertex_AddressValidation/js/view/validation-message',
    'Vertex_AddressValidation/js/view/customer/address-form',
    'Vertex_AddressValidation/js/model/difference-determiner'
], function (
    $,
    ko,
    registry,
    Component,
    validationMessages,
    addressValidationRequest,
    addressResolver,
    message,
    addressForm,
    differenceDeterminer
) {
    'use strict';

    var config = window.vertexAddressValidationConfig || {};

    return Component.extend({
        message: null,
        formAddressData: null,
        isAddressValid: false,
        updateAddress: false,
        addressResolver: addressResolver,
        correctAddress: null,

        initialize: function () {
            this._super();

            this.message = registry.get('addressValidationMessage');
            addressForm.formUpdated.extend({notify: 'always'}).subscribe(this.addressUpdated.bind(this));

            return this;
        },

        /**
         * Reset validation after address update
         */
        addressUpdated: function () {
            addressForm.renameSubmitButton(config.validateButtonText);
            addressForm.hideSaveAsIsButton();
            this.isAddressValid = false;
            this.updateAddress = false;
            this.message.clear();
            this.message.showSuccessMessage = false;
        },

        /**
         * Triggers a request to the address validation builder and adds the response
         *
         * @param {Object} formAddressData
         * @returns {Object}
         */
        addressValidation: function (formAddressData) {
            var deferred = $.Deferred();

            if (this.isAddressValid || !this.validateCountry()) {
                if (this.updateAddress) {
                    this.updateVertexAddress();
                }
                return deferred.resolve();
            }

            this.formAddressData = formAddressData;
            addressForm.startLoader();

            addressValidationRequest(formAddressData)
                .done(function (response) {
                    this.isAddressValid = true;
                    this.correctAddress = response;
                    if (this.handleAddressDifferenceResponse(response) === true) {
                        deferred.resolve();
                    } else {
                        addressForm.stopLoader();
                    }
                }.bind(this)).fail(function () {
                deferred.reject();
            }).fail(function () {
                addressForm.stopLoader();
            });

            return deferred;
        },

        /**
         * Check if country is used in validation
         *
         * @returns {boolean}
         */
        validateCountry: function () {
            var countryCode = addressForm.getFieldByName('country_id').val();

            return countryCode !== undefined
                ? config.countryValidation.includes(countryCode)
                : true;
        },

        /**
         * Get the message with the differences
         *
         * @param {?CleanAddress} response
         */
        handleAddressDifferenceResponse: function (response) {
            if (response === null || !Object.keys(response).length) {
                addressForm.renameSubmitButton(config.saveAsIsButtonText);
                this.message.setWarningMessage(validationMessages.noAddressFound);
                return;
            }

            const differences = differenceDeterminer(this.formAddressData, response);

            if (differences.length === 0 && config.showSuccessMessage) {
                this.message.showSuccessMessage = true;
                return true;
            } else if (differences.length > 0) {
                this.updateAddress = true;
                addressForm.renameSubmitButton(config.updateButtonText);
                addressForm.showSaveAsIsButton();
                this.message.setWarningMessage(validationMessages.changesFound, differences, response);
            } else {
                return true;
            }
        },

        /**
         * Get the update message
         */
        updateVertexAddress: function () {
            addressForm.updateAddress(differenceDeterminer(this.formAddressData, this.correctAddress));
            this.message.setSuccessMessage(validationMessages.addressUpdated);

            if (config.showSuccessMessage) {
                this.message.showSuccessMessage = true;
            }
        }
    });
});
