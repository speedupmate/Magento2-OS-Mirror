/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['uiRegistry'], function (registry) {
    'use strict';

    /**
     * Convert a Magento Quote or Customer Address to an unclean address
     *
     * In most instances, the caller will need to set the region property on the address object.
     *
     * @param {Object} address
     * @returns {UncleanAddress}
     */
    return function (address) {
        const streetAddress = [];

        for (let i in address.street) {
            if (!address.street.hasOwnProperty(i)) {
                continue;
            }
            if (address.street[i].length > 0) {
                streetAddress.push(address.street[i]);
            }
        }

        let countryId;
        if (typeof address.countryId !== 'undefined') {
            countryId = address.countryId;
        } else if (typeof address.country_id !== 'undefined') {
            countryId = address.country_id;
        }

        return {
            street_address: streetAddress,
            city: address.city,
            main_division: address.region,
            postal_code: address.postcode,
            country: countryId
        };
    };
});
