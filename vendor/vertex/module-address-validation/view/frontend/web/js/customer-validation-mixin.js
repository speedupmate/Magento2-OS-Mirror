/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiRegistry',
    'Vertex_AddressValidation/js/view/customer/address-form',
    'Vertex_AddressValidation/js/view/customer/address-validation'
], function ($, registry, addressForm, addressValidator) {
    'use strict';

    var config = window.vertexAddressValidationConfig || {};

    return function (addressValidation) {
        if (!config.enabled) {
            return addressValidation;
        }

        $.widget('mage.addressValidation', addressValidation, {
            /**
             * Initialize widget
             *
             * @returns {*}
             * @private
             */
            _create: function () {
                var result = this._super(),
                    button = $(this.options.selectors.button, this.element),
                    validator = addressValidator();

                addressForm.initialize(this.element, button);
                addressForm.renameSubmitButton(config.validateButtonText);

                this.element.data('validator').settings.submitHandler = function (form) {
                    if (addressForm.isSaveAsIs) {
                        addressForm.isSaveAsIs = false;
                        return this.submitForm(form);
                    }

                    validator.addressValidation(addressForm.getAddress()).done(this.submitForm.bind(this, form));
                }.bind(this);

                return result;
            },

            /**
             * Submit form
             *
             * @param {Object} form
             */
            submitForm: function (form) {
                addressForm.disableSubmitButtons();
                form.submit();
            }
        });
        return $.mage.addressValidation;
    }
});
