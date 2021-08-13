/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['jquery'], function ($) {
    'use strict';

    const config = window.vertexAddressValidationConfig || {};

    return function (addressValidation) {
        if (!config.enabled) {
            return addressValidation;
        }

        $.widget('mage.addressValidation', addressValidation, {
            _vertexValidator: null,
            _vertexForm: null,

            /**
             * Initialize widget
             *
             * @returns {*}
             * @private
             */
            _create: function () {
                var result = this._super(),
                    button = $(this.options.selectors.button, this.element);

                require([
                    'Vertex_AddressValidation/js/view/customer/address-form',
                    'Vertex_AddressValidation/js/view/customer/address-validation'
                ], function (addressForm, addressValidator) {
                    this._vertexValidator = addressValidator();
                    this._vertexForm = addressForm;

                    addressForm.initialize(this.element, button);
                    addressForm.renameSubmitButton(config.validateButtonText);
                }.bind(this));

                this.element.data('validator').settings.submitHandler = function (form) {
                    if (this._vertexForm && this._vertexForm.isSaveAsIs) {
                        this._vertexForm.isSaveAsIs = false;
                        return this.submitForm(form);
                    }

                    if (this._vertexValidator && this._vertexForm) {
                        this._vertexValidator.addressValidation(this._vertexForm.getAddress()).done(this.submitForm.bind(this, form));
                    }
                }.bind(this);

                return result;
            },

            /**
             * Submit form
             *
             * @param {Object} form
             */
            submitForm: function (form) {
                if (this._vertexForm) {
                    this._vertexForm.disableSubmitButtons();
                }
                form.submit();
            }
        });
        return $.mage.addressValidation;
    };
});
