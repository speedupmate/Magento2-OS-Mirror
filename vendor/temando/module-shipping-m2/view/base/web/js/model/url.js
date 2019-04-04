/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
define([], function () {
    'use strict';

    var pickupForwardUrlTemplate = '--id--';

    return {
        /**
         * @param {String} urlTemplate
         */
        setPickupForwardUrlTemplate: function (urlTemplate) {
            pickupForwardUrlTemplate = urlTemplate;
        },

        /**
         * @param {String} pickupId
         */
        buildPickupForwardUrl: function (pickupId) {
            return pickupForwardUrlTemplate.replace('--id--', pickupId);
        }
    };
});
