/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'ko',
    'underscore',
    'Vertex_AddressValidation/js/model/address-converter',
    'Vertex_AddressValidation/js/model/customer/address-resolver'
], function ($, ko, _, addressConverter, addressResolver) {
    'use strict';

    var config = window.vertexAddressValidationConfig || {};

    return {
        form: {},
        button: {},
        saveAsIsButton: {},
        formUpdated: ko.observable(false),
        isSaveAsIs: false,

        /**
         * Initialize address form object
         *
         * @param {Object} form
         * @param {Object} button
         */
        initialize: function (form, button) {
            var self = this,
                fieldsToValidate = _.clone(addressResolver.addressFieldsForValidation);

            this.form = form || {};
            this.button = button || {};

            fieldsToValidate.push('country_id');
            fieldsToValidate.forEach(function (name) {
                self.getFieldByName(name).on('input', function () {
                    self.formUpdated(true);
                });
            });
        },

        /**
         * Return jQuery object by name
         *
         * @param {String} name
         */
        getFieldByName: function (name) {
            return this.form.find('[name=%s]'.replace('%s', name));
        },

        /**
         * Rename form button value
         *
         * @param {String} value
         */
        renameSubmitButton: function (value, button) {
            var button = button || this.button;
            var buttonValue = $(button.html()).html(value);
            button.html(buttonValue).attr('title', value);
        },

        /**
         * Show 'Save As Is' button
         */
        showSaveAsIsButton: function () {
            if (!_.isEmpty(this.saveAsIsButton)) {
                this.saveAsIsButton.show();
                return;
            }

            this.saveAsIsButton = $('<button/>', {
                text: config.saveAsIsButtonText || '',
                class: 'action save vertex-secondary',
                'data-action': 'save-as-is-address',
                click: function () {
                    this.isSaveAsIs = true;
                }.bind(this)
            });

            this.saveAsIsButton.insertAfter(this.button);
        },

        /**
         * Hide 'Save As Is' button
         */
        hideSaveAsIsButton: function () {
            if (!_.isEmpty(this.saveAsIsButton)) {
                this.saveAsIsButton.hide();
            }
        },

        /**
         * Disable form submit buttons
         */
        disableSubmitButtons: function () {
            this.button.attr('disabled', true);

            if (!_.isEmpty(this.saveAsIsButton)) {
                this.saveAsIsButton.attr('disabled', true);
            }
        },

        /**
         * Retrieves form address and converts it to customer address
         *
         * @returns {Object}
         */
        getAddress: function () {
            return addressConverter(this.form);
        },

        /**
         * Start loader
         */
        startLoader: function () {
            $('body').trigger('processStart');
        },

        /**
         * Stop loader
         */
        stopLoader: function () {
            $('body').trigger('processStop');
        }
    };
});
