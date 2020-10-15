/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'ko',
    'underscore',
    'Vertex_AddressValidation/js/model/customer/address-resolver'
], function ($, ko, _, addressResolver) {
    'use strict';

    const config = window.vertexAddressValidationConfig || {};

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
         * @returns {UncleanAddress}
         */
        getAddress: function () {
            const address = {},
                city = this.form.find('input[name="city"]').val(),
                streetAddress = this.form.find('input[name="street[]"]')
                    .map(function (index, element) {
                        return $(element).val();
                    })
                    .toArray()
                    .filter(function (value) {
                        return value.length > 0;
                    }),
                mainDivisionValue = this.form.find('select[name="region_id"]').val(),
                mainDivision = this.form.find('select[name="region_id"] option[value="' + mainDivisionValue + '"]').text(),
                postalCode = this.form.find('input[name="postcode"]').val(),
                country = this.form.find('select[name="country_id"]').val();

            address.street_address = streetAddress;
            if (city.length > 0) {
                address.city = city;
            }
            if (mainDivisionValue.length > 0) {
                address.main_division = mainDivision;
            }
            if (postalCode.length > 0) {
                address.postal_code = postalCode;
            }
            address.country = country;

            return address;
        },

        updateAddress: function (differences) {
            for (let index = 0, length = differences.length;index < length;++index) {
                let difference = differences[index];
                switch (difference.type) {
                    case 'street':
                        this.form.find('input[name="street[]"]').val('');
                        for (
                            let streetIndex = 0, streetLength = difference.rawValue.length;
                            streetIndex < streetLength;
                            ++streetIndex
                        ) {
                            $(this.form.find('input[name="street[]"]')[streetIndex])
                                .val(difference.rawValue[streetIndex])
                                .trigger('change')
                                .trigger('blur');
                        }
                        break;
                    case 'city':
                        this.form.find('input[name="city"]')
                            .val(difference.rawValue)
                            .trigger('change')
                            .trigger('blur');
                        break;
                    case 'region':
                        this.form.find('select[name="region_id"]')
                            .val(difference.rawValue)
                            .trigger('change')
                            .trigger('blur');
                        break;
                    case 'postcode':
                        this.form.find('input[name="postcode"]')
                            .val(difference.rawValue)
                            .trigger('change')
                            .trigger('blur');
                        break;
                    case 'country':
                        this.form.find('select[name="country_id"]')
                            .val(difference.rawValue)
                            .trigger('change')
                            .trigger('blur');
                        break;
                }
            }
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
