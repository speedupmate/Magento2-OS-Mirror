/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([], function () {
    'use strict';

    /**
     * Convert a Cleansed Address to a Magento Quote Address object
     *
     * @param {CleanAddress} address
     * @returns {Object} quoteAddress
     */
    return function (address) {
        return {
            street: address.street_address,
            city: address.city,
            region: address.region_name,
            region_id: address.region_id,
            postcode: address.postal_code,
            country_id: address.country_code
        }
    }
})
