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

            if (element.key !== undefined) {
                addressData[element.name][element.key] = value;
                // Checkout Provider is not working here for some reason
                $('.form-shipping-address input[name="' + element.name + '[' + element.key + ']"]').val(value);
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
