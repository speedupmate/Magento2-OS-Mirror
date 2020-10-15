/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'mage/storage',
    'Vertex_AddressValidation/js/model/url-builder'
], function (
    storage,
    urlBuilder
) {
    'use strict';

    /**
     * Cleanse an Address
     *
     * @param {UncleanAddress} address
     */
    return function (address) {
        let url = '/address/cleanse';
        const params = {address: address};

        if (window.checkoutConfig && window.checkoutConfig.isCustomerLoggedIn === false) {
            url = '/guest-address/cleanse';
            params.cartId = window.checkoutConfig.quoteId;
        }

        return storage.post(
            urlBuilder.createUrl(url, {}),
            JSON.stringify(params)
        );
    };
});
