define([
    'jquery',
    'intlTelInput'
], function ($) {
    'use strict';

    return function (config, node) {
        var telephoneInput = $(node)[0];
        var iti = window.intlTelInput($(node)[0], config);

        telephoneInput.addEventListener('blur', function() {
            telephoneInput.value = iti.getNumber();
        });
    };
});
