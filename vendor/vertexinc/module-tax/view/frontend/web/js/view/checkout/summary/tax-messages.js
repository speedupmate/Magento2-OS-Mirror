/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/totals'
], function (_, ko, Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            messages: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.subscribeTotals();
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe('messages');
            this.getMessages();

            return this;
        },

        /**
         * Retrieve messages
         */
        getMessages: function () {
            var taxSegment = totals.getSegment('tax');

            this.messages([]);

            if (taxSegment && taxSegment['extension_attributes']) {
                this.messages(taxSegment['extension_attributes']['vertex_tax_calculation_messages']);
            }
        },

        /**
         * Subscribe totals observer
         */
        subscribeTotals: function () {
            var self = this;

            totals.totals.subscribe(
                function () {
                    self.getMessages();
                },
                null,
                'change'
            );
        }
    });
});
