/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';

        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        var clientToken = window.checkoutConfig.payment.klarna_kp.client_token,
            message = window.checkoutConfig.payment.klarna_kp.message,
            authorizationToken = ko.observable(window.checkoutConfig.payment.klarna_kp.authorization_token),
            debug = window.checkoutConfig.payment.klarna_kp.debug,
            enabled = window.checkoutConfig.payment.klarna_kp.enabled,
            b2bEnabled = window.checkoutConfig.payment.klarna_kp.b2b_enabled,
            dataSharingOnload = window.checkoutConfig.payment.klarna_kp.data_sharing_onload,
            success = window.checkoutConfig.payment.klarna_kp.success,
            hasErrors = ko.observable(false),
            availableMethods = window.checkoutConfig.payment.klarna_kp.available_methods,
            redirectUrl = window.checkoutConfig.payment.klarna_kp.redirect_url;

        return {
            hasErrors: hasErrors,
            debug: debug,
            enabled: enabled,
            b2bEnabled: b2bEnabled,
            dataSharingOnload: dataSharingOnload,
            clientToken: clientToken,
            message: message,
            success: success,
            authorizationToken: authorizationToken,
            availableMethods: availableMethods,
            redirectUrl: redirectUrl,

            /**
             * Getting back the title
             * @param {String} code
             * @returns {String}
             */
            getTitle: function (code) {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                if (window.checkoutConfig.payment.klarna_kp[code]) {

                    return window.checkoutConfig.payment.klarna_kp[code].title;
                }

                return 'Klarna Payments';
            },

            /**
             * Getting back the logo
             * @param {String} code
             * @returns {String}
             */
            getLogo: function (code) {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                if (window.checkoutConfig.payment.klarna_kp[code]) {

                    return window.checkoutConfig.payment.klarna_kp[code].logo;
                }

                return '';
            }
        };
    }
);
