/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        let config = window.checkoutConfig.payment,
            braintreeType = 'braintree',
            payPalType = 'braintree_paypal',
            payPalCreditType = 'braintree_paypal_credit',
            braintreeAchDirectDebit = 'braintree_ach_direct_debit';

        if (config[braintreeType].isActive) {
            rendererList.push(
                {
                    type: braintreeType,
                    component: 'PayPal_Braintree/js/view/payment/method-renderer/hosted-fields'
                }
            );
        }

        if (config[payPalType].isActive) {
            rendererList.push(
                {
                    type: payPalType,
                    component: 'PayPal_Braintree/js/view/payment/method-renderer/paypal'
                }
            );
        }

        if (config[payPalCreditType].isActive) {
            rendererList.push(
                {
                    type: payPalCreditType,
                    component: 'PayPal_Braintree/js/view/payment/method-renderer/paypal-credit'
                }
            );
        }

        rendererList.push(
            {
                type: 'braintree_venmo',
                component: 'PayPal_Braintree/js/view/payment/method-renderer/venmo'
            }
        );

        if (config[braintreeAchDirectDebit].isActive) {
            rendererList.push(
                {
                    type: braintreeAchDirectDebit,
                    component: 'PayPal_Braintree/js/view/payment/method-renderer/ach'
                }
            );
        }

        rendererList.push(
            {
                type: 'braintree_local_payment',
                component: 'PayPal_Braintree/js/view/payment/method-renderer/lpm'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
