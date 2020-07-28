/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'Vertex_AddressValidation/js/view/checkout/shipping/address-validation',
    'Vertex_AddressValidation/js/model/checkout/billing/address-resolver',
    'Magento_Checkout/js/checkout-data'
], function (
    Component,
    addressResolver,
    checkoutData
) {
    'use strict';

    return Component.extend({
        resolver: addressResolver,

        /**
         * @returns {Object}
         */
        getFormData: function () {
            return checkoutData.getBillingAddressFromData();
        }
    });
});
