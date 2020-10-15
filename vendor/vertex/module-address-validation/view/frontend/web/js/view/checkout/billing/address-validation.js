/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'Vertex_AddressValidation/js/view/checkout/shipping/address-validation',
    'Vertex_AddressValidation/js/model/checkout/billing/address-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function (
    Component,
    addressResolver,
    checkoutData,
    registry
) {
    'use strict';

    return Component.extend({
        resolver: addressResolver,

        /**
         * @returns {Object}
         */
        getFormData: function () {
            const formData = checkoutData.getBillingAddressFromData(),
                checkoutProvider = registry.get('checkoutProvider');

            if (checkoutProvider && checkoutProvider.dictionaries && checkoutProvider.dictionaries.region_id) {
                const region = registry.get('checkoutProvider').dictionaries.region_id.find(function (obj) {
                    return obj.value === formData.region_id;
                });
                if (region && region.label) {
                    formData.region = region.label;
                }
            }

            return formData;
        }
    });
});
