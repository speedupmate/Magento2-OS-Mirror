/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Has service
         *
         * @returns {Boolean} false.
         */
        hasService: function () {
            return false;
        },

        /**
         * Has addons
         *
         * @returns {Boolean} false.
         */
        hasAddons: function () {
            return false;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('disabled visible value');

            return this;
        }
    });
});
