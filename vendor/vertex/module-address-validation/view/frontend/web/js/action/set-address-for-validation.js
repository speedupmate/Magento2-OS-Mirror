/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    $,
    urlBuilder,
    storage,
    errorProcessor,
    messageContainer,
    fullScreenLoader
) {
    'use strict';

    return function (address) {
        var serviceUrl,
            payload;

        serviceUrl = urlBuilder.createUrl('/vertex-address-validation/vertex-address', {});
        payload = {
            address: address
        };

        fullScreenLoader.startLoader();

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).fail(
            function (response) {
                errorProcessor.process(response, messageContainer);
                fullScreenLoader.stopLoader();
            }
        );
    };
});
