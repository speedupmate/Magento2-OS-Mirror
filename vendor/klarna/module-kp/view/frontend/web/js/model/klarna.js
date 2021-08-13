/* global Klarna */
/**
 * This file is part of the Klarna KP module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Klarna_Kp/js/model/config',
        'Klarna_Kp/js/model/debug',
        'klarnapi'
    ],
    function ($, quote, customer, config, debug) {
        'use strict';

        return {
            b2bEnabled: config.b2bEnabled,

            /**
             * Getting back the address based on the input
             * @param {Array} address
             * @param {String} email
             * @returns {{
             *      street_address: String,
             *      country: String,
             *      city: String,
             *      phone: String,
             *      organization_name: String,
             *      given_name: String,
             *      postal_code: String,
             *      family_name: String,
             *      email: *
             * }}
             */
            buildAddress: function (address, email) {
                var addr = {
                    'organization_name': '',
                    'given_name': '',
                    'family_name': '',
                    'street_address': '',
                    'city': '',
                    'postal_code': '',
                    'country': '',
                    'phone': '',
                    'email': email
                };

                if (!address) { // Somehow we got a null passed in
                    return addr;
                }

                if (address.prefix) {
                    addr.title = address.prefix;
                }

                if (address.firstname) {
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    addr.given_name = address.firstname;
                }

                if (address.lastname) {
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    addr.family_name = address.lastname;
                }

                if (address.street) {
                    if (address.street.length > 0) {
                        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                        addr.street_address = address.street[0];
                    }

                    if (address.street.length > 1) {
                        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                        addr.street_address2 = address.street[1];
                    }
                }

                if (address.city) {
                    addr.city = address.city;
                }

                if (address.regionCode) {
                    addr.region = address.regionCode;
                }

                if (address.postcode) {
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    addr.postal_code = address.postcode;
                }

                if (address.countryId) {
                    addr.country = address.countryId;
                }

                if (address.telephone) {
                    addr.phone = address.telephone;
                }

                // Having organization_name in the billing address causes KP/PLSI to return B2B methods
                // no matter the customer type. So we only want to set this if the merchant has enabled B2B.
                if (address.company && this.b2bEnabled) {
                    addr['organization_name'] = address.company;
                }
                debug.log(addr);

                return addr;
            },

            /**
             * Getting back the customer
             * @param {Object} billingAddress
             * @returns {{type: String}}
             */
            buildCustomer: function (billingAddress) {
                var type = 'person';

                if (this.b2bEnabled && billingAddress.company) {
                    type = 'organization';
                }

                return {
                    'type': type
                };
            },

            /**
             * Getting back data for performing a Klarna update request
             * @returns {{billing_address: {}, shipping_address: {}, customer: {}}}
             */
            getUpdateData: function () {
                var email = '',
                    shippingAddress = quote.shippingAddress(),
                    data = {
                        'billing_address': {},
                        'shipping_address': {},
                        'customer': {}
                    };

                if (customer.isLoggedIn()) {
                    email = customer.customerData.email;
                } else {
                    email = quote.guestEmail;
                }

                if (quote.isVirtual()) {
                    shippingAddress = quote.billingAddress();
                }

                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                data.billing_address = this.buildAddress(quote.billingAddress(), email);
                data.shipping_address = this.buildAddress(shippingAddress, email);
                data.customer = this.buildCustomer(quote.billingAddress());
                debug.log(data);

                return data;
            },

            /**
             * Performing the Klarna load request to load the Klarna widget
             * @param {String} paymentMethod
             * @param {String} containerId
             * @param {Callback} callback
             */
            load: function (paymentMethod, containerId, callback) {
                var data = null;

                debug.log('Loading container ' + containerId);

                if ($('#' + containerId).length) {
                    debug.log('Loading method ' + paymentMethod);

                    if (config.dataSharingOnload) {
                        data = this.getUpdateData();
                    }
                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    Klarna.Payments.load(
                        {
                            payment_method_category: paymentMethod,
                            container: '#' + containerId
                        },
                        data,
                        function (res) {
                            var errors = false;

                            debug.log(res);

                            if (res.errors) {
                                errors = true;
                            }
                            config.hasErrors(errors);

                            if (callback) {
                                callback(res);
                            }
                        }
                    );
                }
            },

            /**
             * Initiating Klarna to add the javascript SDK to the page
             */
            init: function () {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                Klarna.Payments.init({
                    client_token: config.clientToken
                });
            },

            /**
             * Sending the Klarna authorize request
             * @param {String} paymentMethod
             * @param {Array} data
             * @param {Callback} callback
             */
            authorize: function (paymentMethod, data, callback) {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                Klarna.Payments.authorize(
                    {
                        payment_method_category: paymentMethod
                    },
                    data,
                    function (res) {
                        var errors = false;

                        debug.log(res);

                        if (res.approved === true) {
                            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                            config.authorizationToken(res.authorization_token);
                        }

                        if (res.errors) {
                            errors = true;
                        }
                        config.hasErrors(errors);
                        callback(res);
                    }
                );
            },

            /**
             * Finalizing the Klarna authorization
             * @param {String} paymentMethod
             * @param {Array} data
             * @param {Callback} callback
             */
            finalize: function (paymentMethod, data, callback) {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                Klarna.Payments.finalize(
                    {
                        payment_method_category: paymentMethod
                    },
                    data,
                    function (res) {
                        var errors = false;

                        debug.log(res);

                        if (res.approved === true) {
                            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                            config.authorizationToken(res.authorization_token);
                        }

                        if (res.errors) {
                            errors = true;
                        }
                        config.hasErrors(errors);
                        callback(res);
                    }
                );
            }
        };
    }
);
