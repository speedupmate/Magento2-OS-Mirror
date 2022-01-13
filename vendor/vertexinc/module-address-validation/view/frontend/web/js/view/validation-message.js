define([
    'ko',
    'uiComponent',
    'Vertex_AddressValidation/js/model/address-difference-template-renderer',
    'Vertex_AddressValidation/js/validation-messages'
], function (ko, Component, differenceRenderer, validationMessages) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vertex_AddressValidation/validation-message',
            cleanseAddressTemplate: 'Vertex_AddressValidation/template/validation-result.html',
            showSuccessMessage: false,
            message: {},
            address: null,
            hasMessage: false,
            tracks: {
                showSuccessMessage: true,
                message: true
            }
        },

        templateRenderer: null,

        initialize: function () {
            this._super();
            this.templateRenderer = new differenceRenderer(this.cleanseAddressTemplate);
            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Model} Chainable.
         */
        initObservable: function () {
            this.address = ko.observable();

            this.hasMessage = ko.pureComputed(function() {
                return this._objectHasEntries(this.message);
            }.bind(this));

            this.renderedTemplate = ko.pureComputed(function () {
                const templateVariables = {
                    message: {
                        text: this.message.text,
                        differences: this.message.differences
                    },
                    address: this.address()
                };

                for (let index = 0,length = this.message.differences.length;index < length;++index) {
                    if (this.message.differences[index].type === 'street') {
                        templateVariables.warning = validationMessages.streetAddressUpdateWarning;
                        break;
                    }
                }

                return this.templateRenderer.render(templateVariables);
            }.bind(this));

            return this._super();
        },


        /**
         * Sets a success message
         *
         * @param {String} text
         * @param {Object} differences
         */
        setSuccessMessage: function (text, differences) {
            this.setMessage(0, 'message success', text, differences || []);
        },

        /**
         * Sets a warning message
         *
         * @param {String} text
         * @param {Object} differences
         * @param {CleanAddress} address
         */
        setWarningMessage: function (text, differences, address) {
            this.address(address || {});
            this.setMessage(1, 'message warning', text, differences || []);
        },

        /**
         * Sets a message
         *
         * @param {Integer} type
         * @param {String} cssClass
         * @param {String} text
         * @param {Object} differences
         */
        setMessage: function (type, cssClass, text, differences) {
            this.message = {
                type: type,
                text: text,
                class: cssClass || '',
                differences: differences || []
            };
        },

        /**
         * Returns if message exists
         *
         * @returns {Boolean}
         */
        hasMessage: function () {
            return ko.computed(function () {
                return this._objectHasEntries(this.message);
            }.bind(this));
        },

        /**
         * Returns message
         *
         * {Object}
         */
        clear: function () {
            this.message = {};
        },

        /**
         * Return whether or not the object has any entries
         *
         * Object.entries is not supported by IE11 or Opera Mini.
         * Writing a quick method to serve the same purpose was easier than
         * importing a shim.
         *
         * @param {Object} object
         * @returns {boolean}
         * @private
         */
        _objectHasEntries: function(object) {
            if (typeof Object.entries !== 'undefined') {
                return Object.entries(object).length !== 0;
            }
            for (let key in object) {
                if (object.hasOwnProperty(key)) {
                    return true;
                }
            }
        },
    });
});
