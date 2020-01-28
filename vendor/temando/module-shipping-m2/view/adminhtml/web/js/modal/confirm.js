define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/loader'
], function ($, confirm) {
    'use strict';

    return function (config, elem) {
        elem.onclick = function () {
            confirm({
                title: config.title,
                content: config.message,
                actions: {
                    confirm: function () {
                        if (config.loader) {
                            $('body').loader('show');
                        }

                        window.setLocation(config.url);
                    }
                }
            });
        };
    }
});
