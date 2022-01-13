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

// jscs:disable requireCamelCaseOrUpperCaseIdentifiers
return {
    b2b_enabled: config.b2b_enabled,

    /**
     */
    buildAddress: function (address, email, isShipping) {
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

        isShipping = typeof isShipping !== 'undefined' ?  isShipping : false;

        if (!address) { // Somehow we got a null passed in
            return addr;
        }

        if (address.prefix) {
            addr.title = address.prefix;
        }

        if (address.firstname) {
            addr.given_name = address.firstname;
        }

        if (address.lastname) {
            addr.family_name = address.lastname;
        }

        if (address.street) {
            if (address.street.length > 0) {
                addr.street_address = address.street[0];
            }

            if (address.street.length > 1) {
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
        if (address.company && (this.b2b_enabled || isShipping)) {
            addr.organization_name = address.company;
        }
        debug.log(addr);

        return addr;
    },

    /**
     */
    buildCustomer: function (billingAddress) {
        var type = 'person';

        if (this.b2b_enabled && billingAddress.company) {
            type = 'organization';
        }

        return {
            'type': type
        };
    },

    /**
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
        data.billing_address = this.buildAddress(quote.billingAddress(), email);
        data.shipping_address = this.buildAddress(shippingAddress, email, true);
        data.customer = this.buildCustomer(quote.billingAddress());
        debug.log(data);

        return data;
    },

    /**
     */
    load: function (payment_method, container_id, callback) {
        var data = null;

        debug.log('Loading container ' + container_id);

        if ($('#' + container_id).length) {
            debug.log('Loading method ' + payment_method);

            if (config.data_sharing_onload) {
                data = this.getUpdateData();
            }
            Klarna.Payments.load(
            {
                payment_method_category: payment_method,
                container: '#' + container_id
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
     */
    init: function () {
        Klarna.Payments.init({
            client_token: config.client_token
        });
    },

    /**
     */
    authorize: function (payment_method, data, callback) {
        Klarna.Payments.authorize(
        {
            payment_method_category: payment_method
        },
          data,
          function (res) {
            var errors = false;

            debug.log(res);

            if (res.approved === true) {
                config.authorization_token(res.authorization_token);
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
     */
    finalize: function (payment_method, data, callback) {
        Klarna.Payments.finalize(
        {
            payment_method_category: payment_method
        },
          data,
          function (res) {
            var errors = false;

            debug.log(res);

            if (res.approved === true) {
                config.authorization_token(res.authorization_token);
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
