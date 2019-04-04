/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [],
    function () {
        "use strict";
        return {
            getRules: function() {
                return {
                    'country_id': {
                        'required': true
                    }
                };
            }
        };
    }
);
