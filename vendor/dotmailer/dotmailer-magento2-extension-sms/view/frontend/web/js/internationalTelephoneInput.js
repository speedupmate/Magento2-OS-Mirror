define([
    'jquery',
    'intlTelInput'
], function ($) {
    'use strict';

    return function (config, node) {
        // initialise plugin
        window.intlTelInput($(node)[0], config);
    };
});
