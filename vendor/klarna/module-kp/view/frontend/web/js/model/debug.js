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
        'Klarna_Kp/js/model/config'
    ],
    function (config) {
        'use strict';

        return {
            /**
             * Logging the message
             * @param {String} message
             */
            log: function (message) {
                if (config.debug) {
                    console.trace();
                    console.log(message);
                }
            }
        };
    }
);
