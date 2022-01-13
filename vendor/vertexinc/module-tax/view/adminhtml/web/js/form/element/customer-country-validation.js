/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/lib/validation/validator',
    'Vertex_Tax/js/form/depend-field-checker',
    'mage/translate'
], function ($, ko, Select, validator, dependFieldChecker) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                'taxvat': '${ $.provider }:data.customer.taxvat'
            },
            listens: {
                '${ $.provider }:data.customer.taxvat': '_taxVatUpdated'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.required(!!this.taxvat().length);

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.taxvat = ko.observable('');

            return this;
        },

        /**
         * Initialize Config
         *
         * @param {Object} config
         */
        initConfig: function (config) {
            /**
             * Validates if a customer VAT number is set, then selecting a Country is required.
             */
            validator.addRule(
                'vertex-customer-country',
                function (value) {
                    const dependField = 'input[name="customer[' + config.dependField + ']"]';

                    return dependFieldChecker.validateValues(dependField, value);
                },
                $.mage.__('Please select a Country.')
            );

            this._super();

            return this;
        },

        /**
         * Handles case when the Tax VAT ID field is updated
         *
         * @param {String} newValue
         * @private
         */
        _taxVatUpdated: function (newValue) {
            this.required(!!newValue.length);
        }
    });
});
