/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'ko',
    'underscore',
    'uiComponent',
    'Temando_Shipping/js/model/url',
    'jquery',
    'mage/translate'
], function (ko, _, Component, urlBuilder, $) {
    'use strict';
    return Component.extend({
        defaults: {
            pickupId: ko.observable(''),
            placeholder: 'PID123456',
            messages: {
                note: ko.observable(''),
                error: ko.observable('')
            }
        },

        /**
         * Submits form via regular submission action, e.g. press enter
         *
         * @param {Object} form
         */
        submit: function (form) {
            var forwardId = this.pickupId();

            this.messages.note('');
            this.messages.error('');

            if (!/^PID+[0-9]*$/.test(forwardId)) {
                this.messages.error($.mage.__('Invalid Pickup ID "%1".').replace('%1', forwardId));
                this.pickupId('');
            } else {
                this.messages.note($.mage.__('Please wait.'));

                var url = urlBuilder.buildPickupForwardUrl(forwardId);
                setLocation(url);
            }

            return false;
        }
    });
});
