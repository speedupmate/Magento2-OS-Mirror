define([
    'jquery',
    'intlTelInput',
    'jquery/validate'
], function ($, intlTelInput) {
    'use strict';

    var errorMap = [
            'Invalid telephone number',
            'Invalid country code',
            'Telephone number is too short',
            'Telephone number is too long',
            'Invalid telephone number'
        ],
        validatorObj = {
            validate: function (value) {
                var countryCodeClass = $('.iti__selected-flag .iti__flag').attr('class'),
                    countryCode,
                    isValid;

                countryCodeClass = countryCodeClass.split(' ')[1];

                if (countryCodeClass === undefined) {
                    $.validator.messages['validate-phone-number'] = errorMap[1];

                    return false;
                }

                countryCode = countryCodeClass.split('__')[1];
                isValid = intlTelInputUtils.isValidNumber(value, countryCode);

                if (!isValid) {
                    $.validator.messages['validate-phone-number'] = errorMap[
                        intlTelInputUtils.getValidationError(value, countryCode)
                        ];
                }

                return isValid;
            }
        };

    $.validator.addMethod(
        'validate-phone-number',
        validatorObj.validate,
        $.validator.messages['validate-phone-number']
    );

    return function (widget) {
        return widget;
    };
});
