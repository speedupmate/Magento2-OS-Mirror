/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

/**
 * @typedef UncleanAddress
 * @api
 * @property {String[]} streetAddress
 * @property {String} city
 * @property {String} mainDivision - Region, State, or Province
 * @property {String} postalCode - ZIP or postal code
 * @property {String} country - 2 or 3 letter country code
 */

/**
 * @typedef CleanAddress
 * @api
 * @property {String[]} streetAddress - street address lines
 * @property {?String} city - name of the city
 * @property {?String} subDivision - name of the sub-division (county, parish)
 * @property {?String} regionName - name of the region (state/province)
 * @property {?int} regionId - numeric (state/province) region identifier in the Magento database
 * @property {?String} postalCode - ZIP+4 or postal code
 * @property {?String} countryCode - 2 letter country code
 * @property {?String} countryName - name of the country
 */

/**
 * @typedef UpdateFieldElement
 * @property {String} name - Element name
 * @property {int} key - Element key
 */
