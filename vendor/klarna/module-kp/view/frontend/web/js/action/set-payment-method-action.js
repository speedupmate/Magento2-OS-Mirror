define(
    [
        'jquery',
        'Klarna_Kp/js/model/config'
    ],
    function ($, config) {
        'use strict';
        return function (messageContainer) {
          $.mage.redirect(config.redirect_url);
        };
    }
);
