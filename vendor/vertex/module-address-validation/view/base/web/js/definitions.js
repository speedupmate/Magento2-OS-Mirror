/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

/**
 * @typedef UncleanAddress
 * @api
 * @property {string[]} streetAddress
 * @property {string} city
 * @property {string} mainDivision - Region, State, or Province
 * @property {string} postalCode - ZIP or postal code
 * @property {string} country - 2 or 3 letter country code
 */

/**
 * @typedef CleanAddress
 * @api
 * @property {string[]} streetAddress - street address lines
 * @property {?string} city - name of the city
 * @property {?string} subDivision - name of the sub-division (county, parish)
 * @property {?string} regionName - name of the region (state/province)
 * @property {?int} regionId - numeric (state/province) region identifier in the Magento database
 * @property {?string} postalCode - ZIP+4 or postal code
 * @property {?string} countryCode - 2 letter country code
 * @property {?string} countryName - name of the country
 */
