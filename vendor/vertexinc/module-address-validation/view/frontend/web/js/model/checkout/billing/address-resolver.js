/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Customer/js/model/address-list',
    'Vertex_AddressValidation/js/model/customer/address-resolver'
], function ($, registry, addressList, addressResolver) {
    'use strict';

    addressResolver = $.extend({}, addressResolver, {
        updateFields: function (element, value) {
            if (element.name === 'street') {
                const streetInputs = $('.payment-method input[name^="street["]');
                streetInputs.val('');
                value = typeof value === "string" ? [value] : Object.values(value);
                for (let index = 0, length = value.length;index < length;++index) {
                    $(streetInputs[index])
                        .val(value[index]);
                }
                streetInputs.trigger('change').trigger('blur');
            } else {
                $('.payment-method input[name="' + element.name + '"]')
                    .val(value)
                    .trigger('change')
                    .trigger('blur');
            }
        }
    });
    return addressResolver;
});
