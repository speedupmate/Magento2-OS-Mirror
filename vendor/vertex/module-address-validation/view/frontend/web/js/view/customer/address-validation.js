/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'ko',
    'mage/translate',
    'uiRegistry',
    'uiComponent',
    'Vertex_AddressValidation/js/action/address-validation-request',
    'Vertex_AddressValidation/js/model/customer/address-resolver',
    'Vertex_AddressValidation/js/view/validation-message',
    'Vertex_AddressValidation/js/view/customer/address-form'
], function (
    $,
    ko,
    $t,
    registry,
    Component,
    addressValidationRequest,
    addressResolver,
    message,
    addressForm
) {
    'use strict';

    var config = window.vertexAddressValidationConfig || {};

    return Component.extend({
        message: null,
        formAddressData: null,
        isAddressValid: false,
        updateAddress: false,
        addressResolver: addressResolver,

        initialize: function () {
            this._super();

            this.message = registry.get('addressValidationMessage');
            addressForm.formUpdated.extend({notify:'always'}).subscribe(this.addressUpdated.bind(this));

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
                    if (this.handleAddressDifferenceResponse(response) === true) {
                        deferred.resolve();
                    }
                }.bind(this)).fail(function () {
                    deferred.reject();
                }).always(function () {
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
         * @param {Object} response
         */
        handleAddressDifferenceResponse: function (response) {
            var difference = this.addressResolver.resolveAddressDifference(response, this.formAddressData);

            if (difference === true && config.showSuccessMessage) {
                this.message.showSuccessMessage = true;
            } else if (difference.length === 0) {
                addressForm.renameSubmitButton(config.saveAsIsButtonText);
                this.message.setWarningMessage($t('We did not find a valid address'));
            } else if (difference !== true) {
                this.updateAddress = true;
                addressForm.renameSubmitButton(config.updateButtonText);
                addressForm.showSaveAsIsButton();
                this.message.setWarningMessage($t('The address is not valid'), difference);
            }
            return difference;
        },

        /**
         * Get the update message
         */
        updateVertexAddress: function () {
            this.addressResolver.resolveAddressUpdate();
            this.message.setSuccessMessage($t('The address was updated'));

            if (config.showSuccessMessage) {
                this.message.showSuccessMessage = true;
            }
        }
    });
});
