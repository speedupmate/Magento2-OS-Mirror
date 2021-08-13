define(
    [
        'jquery',
        'Klarna_Kp/js/model/config'
    ],
    function ($, config) {
        'use strict';

        return function () {
            $.mage.redirect(config.redirectUrl);
        };
    }
);
