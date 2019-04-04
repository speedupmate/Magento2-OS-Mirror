/**
* Copyright © 2013-2017 Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('click', function() {
            history.back();
            return false;
        });
    };
});
