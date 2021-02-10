/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiElement',
    'ko',
    'mage/translate',
    'Vertex_AddressValidation/js/model/address-difference-template-renderer',
    'Vertex_AddressValidation/js/model/difference-determiner',
    'Vertex_AddressValidation/js/action/cleanse-address',
    'Vertex_AddressValidation/js/validation-messages'
], function ($, Component, ko, $t, differenceRenderer, differenceDeterminer, addressCleaner, validationMessages) {
    'use strict';

    return Component.extend({
        defaults: {
            /** @var {string} */
            prefix: '',

            /** @var {string[]} */
            validCountryList: ['US'],

            /**
             * @var {?int} - Milliseconds for how long an animation should run. Null is default, 0 is no animations
             */
            animationDuration: null,

            /**
             * @var {string} - Template to use for rendering differences between clean and unclean addresses
             */
            cleanseAddressTemplate: 'Vertex_AddressValidation/template/validation-result.html',

            /**
             * @var {string} - Selector for the element that will have its HTML replaced by our messages
             */
            messageContainerSelector: '[data-role="vertex-message_container"]',

            /**
             * @var {string} - Selector for the element that, when clicked, will trigger address cleansing
             */
            cleanseAddressButtonSelector: '[data-role="vertex-cleanse_address"]',

            /**
             * @var {string} - Selector for the element that, when clicked, will update address form fields
             */
            updateAddressButtonSelector: '[data-role="vertex-update_address"]',
        },

        /**
         * @function
         * @param {boolean} enabled - Whether or not the button should be enabled
         * @returns {boolean} Whether or not the button is enabled
         */
        cleanseAddressButtonEnabled: null,

        /**
         * @function
         * @param {boolean} inProgress - Whether or not we're currently cleansing an address
         * @returns {boolean} Whether or not we're currently cleansing an address
         */
        inProgress: null,

        /**
         * @function
         * @param {boolean} validCountry - Whether or not the currently selected country is valid
         * @returns {boolean} Whether or not the currently selected country is valid
         */
        validatableAddress: null,

        /**
         * @var {jQuery}
         */
        node: null,

        /**
         * @var {jQuery}
         */
        cleanseAddressButton: null,

        /**
         * @var {jQuery}
         */
        updateAddressButton: null,

        /**
         * @var {jQuery}
         */
        countryInput: null,

        /**
         * @var {jQuery}
         */
        streetInputs: null,

        /**
         * @var {jQuery}
         */
        regionInput: null,

        /**
         * @var {jQuery}
         */
        postalCodeInput: null,

        /**
         * @var {jQuery}
         */
        cityInput: null,

        /**
         * @var {jQuery}
         */
        form: null,

        /**
         * @var {jQuery}
         */
        messageContainer: null,

        /**
         * @var {function}
         */
        templateRenderer: null,

        /**
         * @var {?CleanAddress}
         */
        cleanAddress: null,

        /**
         * @param {Object} config
         * @param {Element} node
         * @returns {*}
         */
        initialize: function (config, node) {
            this.node = $(node);
            this._super();

            addressCleaner.setApiUrl(config.apiUrl);
            this.updateValidatableAddress();
            this.updateCleanseAddressButtonEnabled();

            this.templateRenderer = new differenceRenderer(this.cleanseAddressTemplate);

            return this;
        },

        initConfig: function () {
            this._super();

            this.cleanseAddressButton = this.node.find(this.cleanseAddressButtonSelector);
            this.updateAddressButton = this.node.find(this.updateAddressButtonSelector);
            this.messageContainer = this.node.find(this.messageContainerSelector);

            this.form = $('#' + window.order[this.prefix + 'Container']);

            this.streetInputs = this.form.find('input[name*="[street]"]');
            this.cityInput = this.form.find('input[name*="[city]"]');
            this.countryInput = this.form.find('select[name*="[country_id]"]');
            this.regionInput = this.form.find('select[name*="[region_id]"]');
            this.postalCodeInput = this.form.find('input[name*="[postcode]"]');

            this.animationDuration = this.animationDuration !== null
                ? parseInt(this.animationDuration, 10)
                : null;

            return this;
        },

        initObservable: function () {
            this._super();

            this.cleanseAddressButtonEnabled = ko.observable(null);
            this.inProgress = ko.observable(false);
            this.validatableAddress = ko.observable(false);

            this.countryInput.on('change', this.updateValidatableAddress.bind(this));
            this.regionInput.on('change', this.updateValidatableAddress.bind(this));
            this.streetInputs.on('keyup', this.updateValidatableAddress.bind(this));
            this.postalCodeInput.on('keyup', this.updateValidatableAddress.bind(this));

            this.inProgress.subscribe(this.updateCleanseAddressButtonEnabled.bind(this));
            this.cleanseAddressButtonEnabled.subscribe(this.triggerButtonUpdate.bind(this));
            this.cleanseAddressButton.on('click', this.cleanseAddress.bind(this));
            this.updateAddressButton.on('click', this.updateAddress.bind(this));

            return this;
        },

        getStreetLines: function () {
            return this.streetInputs
                .map(function (index, element) {
                    return $(element).val();
                })
                .toArray()
                .filter(function (v) {
                    return v.length > 0
                });
        },

        /**
         * Check if the currently selected country for the address is a country we support cleansing in
         *
         * This updates the value of {@link this.validatableAddress} and then calls {@link this.cleanseAddressButtonEnabled}
         */
        updateValidatableAddress: function () {
            /*
             * In order for our address to be worth cleansing, it must:
             * - be in a country we support cleansing for
             * - have a street address
             * - have either a postcode or a region
             */
            const validAddress = this.validCountryList.indexOf(this.countryInput.val()) >= 0
                && this.getStreetLines().length > 0
                && (this.postalCodeInput.val() !== '' || this.regionInput.val() !== '');

            this.validatableAddress(validAddress);
            this.updateCleanseAddressButtonEnabled();
        },

        /**
         * Update the address input fields with the differences found by the API
         */
        updateAddress: function () {
            const differences = differenceDeterminer(this.retrieveAddress(), this.cleanAddress);
            for (let index = 0, length = differences.length; index < length; ++index) {
                let difference = differences[index];
                switch (difference.type) {
                    case 'street':
                        this.streetInputs.val('');
                        for (
                            let streetIndex = 0, streetLength = difference.rawValue.length;
                            streetIndex < streetLength;
                            ++streetIndex
                        ) {
                            $(this.streetInputs[streetIndex]).val(difference.rawValue[streetIndex])
                                .trigger('change')
                                .trigger('blur');
                        }
                        break;
                    case 'city':
                        this.cityInput.val(difference.rawValue).trigger('change').trigger('blur');
                        break;
                    case 'region':
                        this.regionInput.val(difference.rawValue).trigger('change').trigger('blur');
                        break;
                    case 'postcode':
                        this.postalCodeInput.val(difference.rawValue).trigger('change').trigger('blur');
                        break;
                    case 'country':
                        this.countryInput.val(difference.rawValue).trigger('change').trigger('blur');
                        break;
                }
            }
            this.hideMessage();
        },

        /**
         * Determine whether or not the button should be enabled
         *
         * This updates the value of {@link this.buttonEnabled}
         *
         * @param {boolean} [inProgressValue]
         */
        updateCleanseAddressButtonEnabled: function (inProgressValue) {
            if (typeof inProgressValue === 'undefined') {
                inProgressValue = this.inProgress();
            }
            this.cleanseAddressButtonEnabled(this.validatableAddress() && !inProgressValue);
        },

        /**
         * Update the button to be enabled or disabled
         *
         * @param {boolean} enabled
         */
        triggerButtonUpdate: function (enabled) {
            $(this.cleanseAddressButton).attr('disabled', !enabled);
        },

        /**
         * Trigger Address Cleansing
         */
        cleanseAddress: function () {
            this.cleanAddress = null;
            this.inProgress(true);
            this.hideMessage();

            addressCleaner
                .cleanseAddress(this.retrieveAddress())
                .fail(this.showErrorMessage.bind(this))
                .done(this.suggestCleansedAddress.bind(this))
                .always(function () {
                    this.inProgress(false);
                }.bind(this));
        },

        /**
         * @param {?CleanAddress} address
         */
        suggestCleansedAddress: function (address) {
            if (address !== null && typeof address.ajaxRedirect !== 'undefined') {
                // We're about to get redirected.  So let's just.. stop
                return;
            }

            if (address !== null && (typeof address !== 'object' || typeof address.postal_code === 'undefined')) {
                // When things go weird wrong but we get a 200 (it happens!)
                this.showErrorMessage();
                return;
            }

            const differences = address === null ? [] : differenceDeterminer(this.retrieveAddress(), address);
            let messageText, containerClass = 'message-info';

            switch (true) {
                case address === null:
                    messageText = validationMessages.noAddressFound;
                    break;
                case differences.length === 0:
                    containerClass = 'message-success';
                    messageText = validationMessages.noChangesNecessary;
                    break;
                default:
                    messageText = validationMessages.adminChangesFound;
            }

            /** @var {vertexDifferenceRendererObject} */
            const templateVariables = {
                message: {
                    text: messageText,
                    differences: differences
                },
                address: address
            };

            for (let index = 0, length = differences.length; index < length; ++index) {
                if (differences[index].type === 'street') {
                    templateVariables.warning = validationMessages.streetAddressUpdateWarning;
                    break;
                }
            }

            this.messageContainer
                .stop(true, true)
                .addClass(containerClass)
                .html(this.templateRenderer.render(templateVariables));

            this.cleanAddress = address;

            if (differences.length > 0) {
                this.updateAddressButton.show();
            }

            if (this.animationDuration !== 0) {
                const options = this.animationDuration !== null ? {duration: this.animationDuration} : {};
                this.messageContainer.slideDown(options);
            } else {
                this.messageContainer.show();
            }
        },

        /**
         * @returns {UncleanAddress}
         */
        retrieveAddress: function () {
            /** @var {UncleanAddress} uncleanAddress */
            const uncleanAddress = {},
                city = this.form.find('input[name*="[city]"]').val(),
                mainDivisionValue = this.regionInput.val(),
                postalCodeValue = this.postalCodeInput.val();

            uncleanAddress.street_address = this.getStreetLines();
            if (city.length > 0) {
                uncleanAddress.city = city;
            }
            if (mainDivisionValue.length > 0) {
                uncleanAddress.main_division = this.regionInput.find('option[value="' + mainDivisionValue + '"]').text();
            }
            if (postalCodeValue.length > 0) {
                uncleanAddress.postal_code = postalCodeValue;
            }
            uncleanAddress.country = this.countryInput.val();
            return uncleanAddress;
        },

        /**
         * Show an error message (for various failures)
         *
         * @param {int=500} errorCode
         */
        showErrorMessage: function (errorCode) {
            if (typeof errorCode === 'undefined') {
                errorCode = 500;
            }
            if (this.animationDuration !== 0) {
                this.messageContainer.stop(true, true);
            }

            let message = '';
            switch (errorCode) {
                case '403':
                    message = $t('Your session has expired. Please reload the page and try again.');
                    break;
                default:
                    message = $t('There was an error cleansing the address. Please try again.');
                    break;
            }

            this.messageContainer
                .addClass('message-error')
                .text(message);

            if (this.animationDuration !== 0) {
                const options = this.animationDuration !== null ? {duration: this.animationDuration} : {};
                this.messageContainer.slideDown(options);
            } else {
                this.messageContainer.show();
            }
        },

        /**
         * Hide the message container and remove its classes
         */
        hideMessage: function () {
            const updateContainer = function () {
                this.messageContainer.text('')
                    .removeClass('message-error')
                    .removeClass('message-notice')
                    .removeClass('message-success');
            }.bind(this);

            this.updateAddressButton.hide();
            if (this.animationDuration === 0) {
                updateContainer();
                this.messageContainer.hide();
            } else {
                const options = {
                    done: updateContainer
                };
                if (this.animationDuration !== null) {
                    options.duration = this.animationDuration;
                }
                this.messageContainer.slideUp(options);
            }
        }
    });
})
