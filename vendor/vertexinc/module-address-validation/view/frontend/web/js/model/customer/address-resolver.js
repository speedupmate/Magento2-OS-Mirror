/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return {
        addressFieldsForValidation: ['city', 'postcode', 'street'],
        responseAddressData: {},
        formAddressData: {},

        /**
         * Will check if the response is different, same or invalid
         *
         * @param {Object} responseAddressData
         * @param {Object} formAddressData
         * @returns {Boolean|Array}
         */
        resolveAddressDifference: function (responseAddressData, formAddressData) {
            var differences = [],
                valid = [];

            this.responseAddressData = responseAddressData;
            this.formAddressData = formAddressData;

            if (this.invalidErrorResponse(responseAddressData)) {
                return [];
            }

            _.each(this.addressFieldsForValidation, function (v, i) {
                var responseValue, value, name, isComplex,
                    complexValues = [],
                    isDifferent = false;

                isComplex = _.isObject(formAddressData[v]) || _.isArray(formAddressData[v]);

                if (responseAddressData[v] !== formAddressData[v]) {
                    if (isComplex) {
                        _.each(formAddressData[v], function (val, index) {
                            if (val && responseAddressData[v][index] && val !== responseAddressData[v][index]) {
                                complexValues[index] = responseAddressData[v][index];
                                isDifferent = true;
                            }
                        });
                    }

                    if (!isDifferent && isComplex) {
                        valid.push(v);
                    }

                    responseValue = responseAddressData[v];
                    if (complexValues.length) {
                        responseValue = complexValues.join(', ');
                    }

                    if (!complexValues.length && _.isArray(responseValue) || responseValue === null) {
                        return;
                    }

                    value = responseValue.substr(0, 1).toUpperCase() + responseValue.substr(1);
                    name = v.substr(0, 1).toUpperCase() + v.substr(1);
                    differences.push({
                        value: value,
                        name: name
                    });

                    return;
                }
                valid[i] = v;
            });

            if (valid.length === this.addressFieldsForValidation.length && _.isEmpty(differences)) {
                return true;
            }
            return differences;
        },

        /**
         * Updates form inputs with the values from the API response
         *
         * @returns {*}
         */
        resolveAddressUpdate: function () {
            var responseAddressData = this.responseAddressData,
                formAddressData = this.formAddressData,
                self = this;

            _.each(this.addressFieldsForValidation, function (v) {
                var fieldValue = responseAddressData[v],
                    linesObj = {};

                if (_.isObject(formAddressData[v])) {
                    _.each(formAddressData[v], function (val, i) {
                        if (fieldValue[i]) {
                            linesObj[i] = fieldValue[i];
                            self.updateFields({name: v, key: i}, fieldValue[i]);

                            return;
                        }
                        linesObj[i] = val;
                    });
                    formAddressData[v] = linesObj;
                    return;
                }

                formAddressData[v] = fieldValue;
                self.updateFields({name: v}, fieldValue);
            });

            return formAddressData;
        },

        /**
         * Update validated fields
         *
         * @param {Object} element
         * @param {String} value
         */
        updateFields: function (element, value) {
            if (element.key !== undefined) {
                $('input[name="' + element.name + '[]"]').eq(element.key).val(value).trigger('change');
            } else {
                $('input[name="' + element.name + '"]').val(value).trigger('change');
            }
        },

        /**
         * Will check if the api response found a address
         *
         * @param {Object} responseData
         * @returns {Boolean}
         */
        invalidErrorResponse: function (responseData) {
            _.each(this.addressFieldsForValidation, function (v) {
                if (_.isArray(responseData[v]) && responseData[v][0] === '') {
                    return true;
                }

                if (responseData[v] === null) {
                    return true;
                }
            });
            return false;
        }
    };
});
