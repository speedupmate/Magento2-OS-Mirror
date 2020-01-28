define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Customer/js/model/customer',
    'mage/translate'
], function (
    $,
    _,
    checkoutData,
    createShippingAddress,
    selectShippingAddress,
    customer,
    $t
) {
    'use strict';

    return {
        message: {
            messageText: $t('The address is valid'),
            differences: [],
            type: 0,
            class: 'message success'
        },
        addressFieldsForValidation: ['city', 'postcode', 'street'],

        /**
         * Will check if the response is different, same or invalid
         *
         * @param {Object} apiResponse
         * @param {Boolean} isBilling
         * @returns {*|exports.message|{messageText, differences, type, class}}
         */
        resolveAddressDifference: function (apiResponse, isBilling) {
            var differences = [],
                valid = [],
                addressFromData;

            if (isBilling) {
                addressFromData = checkoutData.getBillingAddressFromData();
            } else {
                addressFromData = checkoutData.getShippingAddressFromData();
            }

            if (this.invalidErrorResponse(apiResponse)) {
                return this.warningOrNoResponseMessage([]);
            }

            _.each(this.addressFieldsForValidation, function (v, i) {
                var responseValue, value, name, isComplex,
                    complexValues = [],
                    isDifferent = false;

                isComplex = _.isObject(addressFromData[v]) || _.isArray(addressFromData[v]);

                if (apiResponse[v] !== addressFromData[v]) {

                    if (isComplex) {
                        _.each(addressFromData[v], function (val, index) {
                            if (val && apiResponse[v][index] && val !== apiResponse[v][index]) {
                                complexValues[index] = apiResponse[v][index];
                                isDifferent = true;
                            }
                        });
                    }

                    if (!isDifferent && isComplex) {
                        valid[i] = v;
                    }

                    responseValue = apiResponse[v];

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
                return this.successMessage(false);
            }

            return this.warningOrNoResponseMessage(differences);
        },

        /**
         * Will updated the local storage based on user type
         *
         * @param {String} validAddressStorage
         * @returns {*|exports.message|{messageText, differences, type, class}}
         */
        resolveShippingAddressInvalid: function (validAddressStorage) {
            var addressFromData, newShippingAddress;

            addressFromData = this.updateAddressFormData(validAddressStorage);

            if (customer.isLoggedIn) {
                newShippingAddress = createShippingAddress(addressFromData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressFromData));
            } else {
                checkoutData.setShippingAddressFromData(addressFromData);
            }

            return this.successMessage(true);
        },

        /**
         * Will updated the local storage based on user type
         *
         * @param {String} validAddressStorage
         * @returns {*|exports.message|{messageText, differences, type, class}}
         */
        resolveBillingAddressInvalid: function (validAddressStorage) {
            var addressFromData = this.updateAddressFormData(validAddressStorage, true);

            checkoutData.setBillingAddressFromData(addressFromData);

            return this.successMessage(true);
        },

        /**
         * Will update the data storage with the values from the api response
         *
         * @param {String} validAddressStorage
         * @param {Boolean} isBilling
         * @returns {*}
         */
        updateAddressFormData: function (validAddressStorage, isBilling) {
            var addressFromData,
                validResponse = JSON.parse(validAddressStorage),
                self = this;

            if (isBilling) {
                addressFromData = checkoutData.getBillingAddressFromData();
            } else {
                addressFromData = checkoutData.getShippingAddressFromData();
            }

            _.each(this.addressFieldsForValidation, function (v) {
                var fieldValue = validResponse[v],
                    linesObj = {};

                if (_.isObject(addressFromData[v])) {
                    _.each(addressFromData[v], function (val, i) {
                        if (fieldValue[i]) {
                            linesObj[i] = fieldValue[i];
                            self.updateFields(v + '[' + i + ']', fieldValue, false);

                            return;
                        }
                        linesObj[i] = val;
                    });
                    addressFromData[v] = linesObj;

                    return;
                }

                addressFromData[v] = fieldValue;
                self.updateFields(v, fieldValue, false);
            });

            return addressFromData;
        },

        /**
         * Update validated fields
         *
         * @param {String} name
         * @param {String} value
         * @param {Boolean} isVisible
         */
        updateFields: function (name, value, isVisible) {
            if (isVisible) {
                $('input[name="' + name + '"]:visible').val(value);
            } else {
                $('input[name="' + name + '"]').val(value);
            }
        },

        /**
         * Will check if the api response found a address
         *
         * @param {Object} apiResponse
         * @returns {Boolean}
         */
        invalidErrorResponse: function (apiResponse) {
            var isInvalid = false;

            _.each(this.addressFieldsForValidation, function (v) {
                if (_.isArray(apiResponse[v]) && apiResponse[v][0] === '') {
                    isInvalid = true;

                    return;
                }

                if (apiResponse[v] === null) {
                    isInvalid = true;
                }
            });

            return isInvalid;
        },

        /**
         * Will return the invalid and error message
         *
         * @param {Array} differences
         * @returns {exports.message|{messageText, differences, type, class}}
         */
        warningOrNoResponseMessage: function (differences) {
            this.message.messageText = $t('We did not find a valid address');

            if (differences.length) {
                this.message.messageText = $t('The address is not valid');
            }
            this.message.differences = differences;
            this.message.type = 1;
            this.message.class = 'message warning';

            return this.message;
        },

        /**
         * Will return the update and success message
         *
         * @param {Boolean} update
         * @returns {exports.message|{messageText, differences, type, class}}
         */
        successMessage: function (update) {
            this.message.messageText = $t('The address is valid');

            if (update) {
                this.message.messageText = $t('The address was updated');
            }
            this.message.type = 0;
            this.message.class = 'message success';
            this.message.differences = [];

            return this.message;
        }
    };
});
