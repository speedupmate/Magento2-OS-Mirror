/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'underscore',
    'uiComponent',
    'ko',
    'Temando_Shipping/js/model/pickup-locations',
    'Temando_Shipping/js/action/select-pickup-location'
], function (_, Component, ko, pickupLocations, selectPickupLocationAction) {
    'use strict';

    var selectedPickupLocation = ko.observable(false),
        readSelected = function () {
            if (selectedPickupLocation()) {
                return selectedPickupLocation();
            } else {
                var selected = pickupLocations.getPickupLocations().find(function (element) {
                    return element.selected;
                });

                return selected ? selected.pickup_location_id : false;
            }
        },
        writeSelected = function (value) {
            selectPickupLocationAction(value);
            pickupLocations.selectPickupLocation(value);
        };

    return Component.extend({
        defaults: {
            template: 'Temando_Shipping/checkout/shipping/delivery-options'
        },
        selectedPickupLocation: selectedPickupLocation,
        selected: ko.pureComputed({
            read: readSelected,
            write: writeSelected,
            owner: this
        }),

        getPickupLocations: function () {
            return pickupLocations.getPickupLocations();
        },

        getMessage: function () {
            return pickupLocations.getMessage();
        },

        hasNoResult: function () {
            var result = false;
            if (this.getPickupLocations().length < 1) {
                result = true;
            }
            return result;
        }
    });
});
