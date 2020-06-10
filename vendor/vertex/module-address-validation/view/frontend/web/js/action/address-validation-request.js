/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'mage/storage',
    'Vertex_AddressValidation/js/model/url-builder'
], function (
    storage,
    urlBuilder
) {
    'use strict';

    return function (address) {
        return storage.post(
            urlBuilder.createUrl('/vertex-address-validation/vertex-address', {}),
            JSON.stringify({
                address: address
            })
        );
    };
});
