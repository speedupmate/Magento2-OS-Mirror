define([
    'jquery',
    'ko',
    'intlTelInput'
], function ($, ko, intlTelInput) {
    'use strict';

    return function (validator) {

        var errorMap = [
            'Invalid telephone number',
            'Invalid country code',
            'Telephone number is too short',
            'Telephone number is too long',
            'Invalid telephone number'
        ];

        var validatorObj = {
            message: '',

            /**
             * @param {String} value
             * @param {*} params
             * @param {Object} additionalParams
             */
            validate: function (value, params, additionalParams) {
                var target = $('#' + additionalParams.uid),
                    countryCodeClass = target.parent().find('.iti__selected-flag .iti__flag').attr('class'),
                    countryCode,
                    isValid = false;

                if (countryCodeClass === undefined || countryCodeClass.indexOf(' ') === -1) {
                    this.message = errorMap[1];

                    return false;
                }

                countryCodeClass = countryCodeClass.split(' ')[1];
                countryCode = countryCodeClass.split('__')[1];
                isValid = window.intlTelInputUtils.isValidNumber(value, countryCode);

                if (!isValid) {
                    this.message = errorMap[
                        window.intlTelInputUtils.getValidationError(value, countryCode)
                        ];
                }

                // Ensure that changing the flag always updates the model
                ko.utils.triggerEvent(target[0], 'change');

                return isValid;
            }
        };

        validator.addRule(
            'validate-phone-number',
            validatorObj.validate,
            $.mage.__(validatorObj.message)
        );

        return validator;
    };
});
