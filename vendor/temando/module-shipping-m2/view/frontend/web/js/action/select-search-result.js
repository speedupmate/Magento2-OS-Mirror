/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Temando_Shipping/js/model/cache-service',
    'Magento_Checkout/js/model/shipping-service'
], function (urlBuilder, customer, storage, quote, cacheService, shippingService) {
    'use strict';

    return function (selectedValue) {

        var url, urlParams, serviceUrl;
        if (customer.isLoggedIn()) {
            url = '/carts/mine/checkout-collection-point/select';
            urlParams = {};
        } else {
            url = '/guest-carts/:cartId/checkout-collection-point/select';
            urlParams = {
                cartId: quote.getQuoteId()
            };
        }
        var payload = {collectionPointId: selectedValue};
        serviceUrl = urlBuilder.createUrl(url, urlParams);

        shippingService.isLoading(true);

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).success(
            function (response) {
                if (quote.shippingAddress()) {
                    // if a shipping address was selected, clear shipping rates cache
                    cacheService.invalidateCacheForAddress(quote.shippingAddress());
                    quote.shippingAddress.valueHasMutated();
                } else {
                    // otherwise stop spinner, no new rates to display
                    shippingService.isLoading(false);
                }
            }
        ).fail(
            function () {
                shippingService.isLoading(false);
            }
        );
    };
});
