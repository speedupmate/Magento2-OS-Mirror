/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['mage/translate'], function ($t) {
    'use strict';

    /**
     * @typedef VertexAddressReadableDifference
     * @property {string} type - Type of difference (used for code)
     * @property {string} name - Human readable name of the item that has changed
     * @property {(string|string[])} value - Human readable value of the item that has changed
     * @property {(string|string[])} rawValue - Script usable value of the item that has changed
     */

    /**
     * @param {UncleanAddress} uncleanAddress
     * @param {CleanAddress} cleanAddress
     * @returns {boolean}
     */
    function streetAddressesAreDifferent(uncleanAddress, cleanAddress) {
        uncleanAddress.street_address.filter(function (val) {
            // Filter out empty strings
            return val.length > 0;
        });

        if (uncleanAddress.street_address.length !== cleanAddress.street_address.length) {
            return true;
        }
        for(let index = 0,length = uncleanAddress.street_address.length;index < length;++index) {
            if (uncleanAddress.street_address[index] !== cleanAddress.street_address[index]) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param {UncleanAddress} uncleanAddress
     * @param {CleanAddress} cleanAddress
     * @returns {VertexAddressReadableDifference[]}
     */
    return function (uncleanAddress, cleanAddress) {
        const listedDifferences = [];
        if (streetAddressesAreDifferent(uncleanAddress, cleanAddress)) {
            listedDifferences.push({type: 'street', name: $t('Street Address'), value: cleanAddress.street_address, rawValue: cleanAddress.street_address});
        }
        if (uncleanAddress.city !== cleanAddress.city) {
            listedDifferences.push({type: 'city', name: $t('City'), value: cleanAddress.city, rawValue: cleanAddress.city});
        }
        if (uncleanAddress.main_division !== cleanAddress.region_name) {
            listedDifferences.push({type: 'region', name: $t('State/Province'), value: cleanAddress.region_name, rawValue: cleanAddress.region_id});
        }
        if (uncleanAddress.postal_code !== cleanAddress.postal_code) {
            listedDifferences.push({type: 'postcode', name: $t('Zip/Postal Code'), value: cleanAddress.postal_code, rawValue: cleanAddress.postal_code});
        }
        if (uncleanAddress.country !== cleanAddress.country_code) {
            listedDifferences.push({type: 'country', name: $t('Country'), value: cleanAddress.country_name, rawValue: cleanAddress.country_code});
        }
        return listedDifferences;
    };
});
