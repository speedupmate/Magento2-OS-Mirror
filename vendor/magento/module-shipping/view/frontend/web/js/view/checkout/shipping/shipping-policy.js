/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Shipping/js/model/config'

], function (Component, config) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Shipping/checkout/shipping/shipping-policy'
        },
        config: config()
    });
});
