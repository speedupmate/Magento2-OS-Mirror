/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'underscore',
    'uiComponent',
    'ko',
    'Temando_Shipping/js/model/collection-points',
    'Temando_Shipping/js/action/save-search-request',
    'Temando_Shipping/js/action/select-search-result'
], function (_, Component, ko, collectionPoints, searchAction, selectCollectionPointAction) {
    'use strict';

    var selectedCollectionPoint = ko.observable(false),
        initializeZipCode =  collectionPoints.getSearchRequestPostCode(),
        initializeCountryCode = collectionPoints.getSearchRequestCountryCode(),
        readSelected = function () {
            if (selectedCollectionPoint()) {
                return selectedCollectionPoint();
            } else {
                var selected = collectionPoints.getCollectionPoints().find(function (element) {
                    return element.selected;
                });

                return selected ? selected.collection_point_id : false;
            }
        },
        writeSelected = function (value) {
            selectCollectionPointAction(value);
            collectionPoints.selectCollectionPoint(value);
        };

    return Component.extend({
        defaults: {
            template: 'Temando_Shipping/checkout/shipping/delivery-options'
        },
        selectedCollectionPoint: selectedCollectionPoint,
        selected: ko.pureComputed({
            read: readSelected,
            write: writeSelected,
            owner: this
        }),
        zipCodeError: ko.observable(''),
        zipValue: ko.observable(initializeZipCode),
        countryValue: ko.observable(initializeCountryCode),

        getCountryData: function () {
            var result = [];
            var countryData = window.checkoutConfig['ts-cp-countries'];
            _.each(countryData, function (country) {
                result.push({
                    'countryCode': country.value,
                    'countryName': country.label
                });
            });

            return result;
        },

        getCollectionPoints: function () {
            return collectionPoints.getCollectionPoints();
        },

        getMessage: function () {
            return collectionPoints.getMessage();
        },

        hasNoResult: function () {
            var result = false;
            if (collectionPoints.getSearchRequestPostCode() && this.getCollectionPoints().length < 1) {
                result = true;
            }
            return result;
        },

        /**
         * @return {null}
         */
        submitForm: function () {
            if (this.zipValue().trim().length) {
                // Call request for saving the fields into a table
                searchAction(this.zipValue(), this.countryValue());
                this.zipCodeError('');
            } else {
                this.zipCodeError('This is a required field.');
            }
        }
    });
});
