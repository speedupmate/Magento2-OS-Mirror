/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Customer/js/model/address-list',
    'Vertex_AddressValidation/js/model/customer/address-resolver',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/checkout-data'
], function ($, registry, addressList, addressResolver, createShippingAddress, checkoutData) {
    'use strict';

    addressResolver = $.extend({}, addressResolver, {
        checkoutProvider: registry.get('checkoutProvider'),

        updateFields: function (element, value) {
            var addressData = $.extend({}, this.checkoutProvider.get('shippingAddress'));

            if (element.name === 'street') {
                // Just updating the addressData element doesn't seem to work on street inputs
                const streetInputs = $('.form-shipping-address input[name^="street["]');
                value = typeof value === "string" ? [value] : Object.values(value);
                streetInputs.val('');
                for(let index = 0, length = value.length;index < length;++index) {
                    addressData[element.name][index] = typeof value[index] !== 'undefined' ? value[index] : '';
                    $(streetInputs[index])
                        .val(addressData[element.name][index]);
                }
            } else {
                addressData[element.name] = value;
            }

            this.checkoutProvider.set('shippingAddress', addressData);
            this.checkoutProvider.trigger('shippingAddress', addressData);

            // Update address list containers
            createShippingAddress(addressData);
            checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
        }
    });
    return addressResolver;
});
