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
                return this._objectHasEntries(this.message);
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
