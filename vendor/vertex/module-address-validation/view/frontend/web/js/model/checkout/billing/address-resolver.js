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
            var name = element.key !== undefined
                ? element.name + '[' + element.key + ']'
                : element.name;

            $('input[name="' + name + '"]').val(value).trigger('change');
        }
    });
    return addressResolver;
});
