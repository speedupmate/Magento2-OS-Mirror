/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'Magento_Customer/js/model/customer/address',
    'Vertex_AddressValidation/js/lib/jquery.serialize-object'
], function (customerAddress) {
    'use strict';

    return function (form) {
        var addressData = form.serializeObject();

        addressData.region = {
            region_id: addressData.region_id,
            region: addressData.region
        };
        return customerAddress(addressData);
    };
});
