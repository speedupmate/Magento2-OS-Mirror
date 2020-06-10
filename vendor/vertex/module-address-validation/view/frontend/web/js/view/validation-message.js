define([
    'ko',
    'uiComponent'
], function (ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Vertex_AddressValidation/validation-message',
            showSuccessMessage: false,
            message: {},
            hasMessage: false,
            tracks: {
                showSuccessMessage: true,
                message: true
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Model} Chainable.
         */
        initObservable: function () {
            this.hasMessage = ko.pureComputed(function() {
                return Object.entries(this.message).length !== 0;
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
            this.setMessage(0, 'message success', text, differences || {});
        },

        /**
         * Sets a warning message
         *
         * @param {String} text
         * @param {Object} differences
         */
        setWarningMessage: function (text, differences) {
            this.setMessage(1, 'message warning', text, differences || {});
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
                differences: differences || {}
            };
        },

        /**
         * Returns if message exists
         *
         * @returns {Boolean}
         */
        hasMessage: function () {
            var message = this.message;

            return ko.computed(function () {
                return Object.entries(message).length !== 0
            });
        },

        /**
         * Returns message
         *
         * {Object}
         */
        clear: function () {
            this.message = {};
        }
    });
});
