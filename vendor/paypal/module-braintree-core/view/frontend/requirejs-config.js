var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'PayPal_Braintree/js/model/step-navigator-mixin': true
            }
        }
    },
    map: {
        '*': {
            braintreeCheckoutPayPalAdapter: 'PayPal_Braintree/js/view/payment/adapter'
        }
    },
};
