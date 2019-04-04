/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

define(
    [
        'jquery',
        'ko',
        'amazonCore',
        'amazonPaymentConfig'
    ],
    function (
        $,
        ko,
        amazonCore,
        amazonPaymentConfig
    ) {
        'use strict';

        var isAmazonAccountLoggedIn = ko.observable(false),
            isAmazonEnabled = ko.observable(amazonPaymentConfig.getValue('isPwaEnabled')),
            orderReference,
            addressConsentToken = amazonCore.accessToken,
            //eslint-disable-next-line no-use-before-define
            isAmazonDefined = amazonCore.amazonDefined.subscribe(checkAmazonDefined),
            //eslint-disable-next-line no-use-before-define
            amazonLoginError = amazonCore.amazonLoginError.subscribe(setAmazonLoggedOutIfLoginError),
            amazonDeclineCode = ko.observable(false),
            sandboxSimulationReference = ko.observable('default'),
            isPlaceOrderDisabled = ko.observable(false),
            isShippingMethodsLoading = ko.observable(false),
            isAmazonShippingAddressSelected = ko.observable(false),
            isQuoteDirty = ko.observable(amazonPaymentConfig.getValue('isQuoteDirty')),
            isPwaVisible = ko.computed(function () {
                return isAmazonEnabled() && !isQuoteDirty();
            }),
            isAmazonCartInValid = ko.computed(function () {
                return isAmazonAccountLoggedIn() && isQuoteDirty();
            }),
            isLoginRedirectPage = $('body').hasClass('amazon-login-login-processauthhash');

        /**
         * Subscribes to amazonDefined observable which runs when amazon object becomes available
         * @param {String} amazonDefined
         */
        function checkAmazonDefined(amazonDefined) {
            if (amazonDefined && !isLoginRedirectPage) {
                verifyAmazonLoggedIn(); //eslint-disable-line no-use-before-define
                //remove subscription to amazonDefined once loaded
                isAmazonDefined.dispose();
            }
        }

        /**
         * Log out amazon user
         */
        function amazonLogOut() {
            if (amazonCore.amazonDefined()) {
                amazon.Login.logout(); // eslint-disable-line no-undef
            }
            this.isAmazonAccountLoggedIn(false);
        }

        /**
         * Set login error if logged out
         */
        function setAmazonLoggedOutIfLoginError(isLoggedOut) {
            if (isLoggedOut === true) {
                isAmazonAccountLoggedIn(false);
                amazonLoginError.dispose();
            }
        }

        /** if Amazon cart contents are invalid log user out **/
        isAmazonCartInValid.subscribe(function (isCartInValid) {
            if (isCartInValid) {
                amazonLogOut();
            }
        });

        //run this on loading storage model. If not defined subscribe will trigger when true
        checkAmazonDefined(amazonCore.amazonDefined());
        setAmazonLoggedOutIfLoginError(amazonCore.amazonLoginError());

        /**
         * Verifies amazon user is logged in
         */
        function verifyAmazonLoggedIn() {
            amazonCore.verifyAmazonLoggedIn().then(function (response) {
                if (!amazonCore.amazonLoginError()) {
                    isAmazonAccountLoggedIn(response);
                }
            });
        }

        return {
            isAmazonAccountLoggedIn: isAmazonAccountLoggedIn,
            isAmazonEnabled: isAmazonEnabled,
            amazonDeclineCode: amazonDeclineCode,
            sandboxSimulationReference: sandboxSimulationReference,
            isPlaceOrderDisabled: isPlaceOrderDisabled,
            isShippingMethodsLoading: isShippingMethodsLoading,
            isAmazonShippingAddressSelected: isAmazonShippingAddressSelected,
            isQuoteDirty: isQuoteDirty,
            isPwaVisible: isPwaVisible,
            amazonlogOut: amazonLogOut,

            /**
             * Set order reference
             */
            setOrderReference: function (or) {
                orderReference = or;
            },

            /**
             * Get order reference
             */
            getOrderReference: function () {
                return orderReference;
            },

            /**
             * Get address consent token
             */
            getAddressConsentToken: function () {
                return addressConsentToken();
            }
        };
    }
);
