/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['jquery'], function ($) {
    'use strict';

    /**
     * @typedef CleanseAddressActionConfig
     * @api
     * @property {?string} apiUrl - The URL to use for cleansing an address
     */

    /**
     * @type {CleanseAddressActionConfig}
     */
    const config = {apiUrl: null};

    /**
     * @api
     */
    return {
        /**
         * Globally configure the URL to query for address cleansing results
         *
         * @param {string} apiUrl
         */
        setApiUrl: function (apiUrl) {
            if (typeof apiUrl !== 'string') {
                throw new TypeError('apiUrl must be a string');
            }
            config.apiUrl = apiUrl;
        },

        /**
         * Call the Cleanse Address API with an unclean address
         *
         * @param {UncleanAddress} address
         * @returns {jQuery.Deferred}
         * @exception Throws an error if the api url has not been configured
         */
        cleanseAddress: function (address) {
            if (config.apiUrl === null) {
                throw 'API URL Not Defined';
            }
            const result = $.Deferred();
            $.post(config.apiUrl, address, 'json')
                .fail(function (jqXhr, textStatus) {
                    result.reject(textStatus);
                })
                .done(function (cleanAddress) {
                    result.resolve(cleanAddress);
                });
            return result;
        }
    }
});
