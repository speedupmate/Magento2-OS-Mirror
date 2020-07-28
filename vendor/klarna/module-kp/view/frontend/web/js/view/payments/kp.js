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
    'ko',
    'jquery',
    'mage/translate',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/view/billing-address',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information',
    'Klarna_Kp/js/model/config',
    'Klarna_Kp/js/model/klarna',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Klarna_Kp/js/view/payments',
    'Klarna_Kp/js/model/debug',
    'Klarna_Kp/js/action/set-payment-method-action'
  ],
  function (ko,
            $,
            $t,
            Component,
            billingAddress,
            fullScreenLoader,
            setPaymentInformationAction,
            config,
            klarna,
            quote,
            additionalValidators,
            kp,
            debug,
            setPaymentMethodAction) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Klarna_Kp/payments/kp',
        timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.',
        redirectAfterPlaceOrder: false
      },
      placeOrderHandler: null,
      validateHandler: null,

      isVisible: ko.observable(true),
      isLoading: false,
      isBillingSameAsShipping: true,
      showButton: ko.observable(false),

      checkPreSelect: function() {
        if (this.getCode() === this.isChecked()) {
          this.isLoading = false;
          this.debounceKlarnaLoad();
        }
      },

      getLogoUrl: function() {
        return config.getLogo(this.getCategoryId());
      },

      /**
       * @param {Object} handler
       */
      setPlaceOrderHandler: function (handler) {
        this.placeOrderHandler = handler;
      },

      /**
       * @param {Object} handler
       */
      setValidateHandler: function (handler) {
        this.validateHandler = handler;
      },

      /**
       * @returns {Object}
       */
      context: function () {
        return this;
      },

      /**
       * @returns {Boolean}
       */
      isShowLegend: function () {
        return true;
      },

      getTitle: function () {
        return config.getTitle(this.getCategoryId());
      },

      /**
       * Get data
       * @returns {Object}
       */
      getData: function () {
        return {
          'method': this.item.method,
          'additional_data': {
            'method_title': this.getTitle(),
            'logo': this.getLogoUrl(),
            'authorization_token': config.authorization_token()
          }
        };
      },

      getCategoryId: function () {
        // Strip off "klarna_"
        return this.getCode().substr(7);
      },

      hasMessage: function () {
        return config.message !== null || config.client_token === null || config.client_token === '';
      },

      getMessage: function () {
        if (config.message !== null) {
          return config.message;
        }
        return $t('An unknown error occurred. Please try another payment method');
      },

      getClientToken: function () {
        return config.client_token;
      },

      getAuthorizationToken: function () {
        return config.authorization_token();
      },
      initialize: function () {
        var self = this;

        this._super();

        this.showButton(false);
        if (this.hasMessage()) {
          // Don't try to initialize Klarna
          return;
        }
        klarna.init();
        quote.paymentMethod.subscribe(function (value) {
          self.isLoading = false;
          if (value && value.method === self.getCode()) {
            self.debounceKlarnaLoad();
          }
        });
        config.hasErrors.subscribe(function (value) {
          self.showButton(value);
        });

        billingAddress().isAddressSameAsShipping.subscribe(function (isSame) {
          self.isBillingSameAsShipping = isSame;
        });
        quote.shippingAddress.subscribe(function () {
          // MAGE-803: When billing and shipping are the same, both the shipping and billing listeners will be
          // called with the shipping one called first. If we allow this to update KP in that case then the
          // billing address will not match between Magento and Klarna as by the time it reaches here the address
          // change will not have propagated to the billing address in the Magento quote and the billing listener
          // will be blocked from updating KP as an update will already be in progress.
          if (self.getCode() === self.isChecked() && !self.isBillingSameAsShipping) {
            self.debounceKlarnaLoad();
          }
        });
        quote.billingAddress.subscribe(function () {
          if (self.getCode() === self.isChecked()) {
            self.debounceKlarnaLoad();
          }
        });
        quote.totals.subscribe(function (newTotals) {
            if (self.getCode() === self.isChecked()) {
                self.debounceKlarnaLoad();
            }
        });
      },
      getContainerId: function () {
        return this.getCode().replace(new RegExp('_', 'g'), '-') + '-container';
      },
      selectPaymentMethod: function () {
        this.isLoading = false;
        this.debounceKlarnaLoad();
        return this._super();
      },
      loadTimeout: null,
      debounceKlarnaLoad: function () {
        var self = this;
        if (self.loadTimeout) {
          clearTimeout(self.loadTimeout);
        }
        self.loadTimeout = setTimeout(function(){
          self.loadKlarna();
        }, 200);
      },

      loadKlarna: function () {
        var self = this;

        if (self.isLoading) {
          return false;
        }
        self.isLoading = true;
        try {
          klarna.load(self.getCategoryId(), self.getContainerId(), function (res) {
            debug.log(res);
            self.showButton(res.show_form);
            self.isLoading = false;
          });
          return true;
        } catch (e) {
          debug.log(e);
          self.isLoading = false;
          return false;
        }
      },
      authorize: function () {
        var self = this;

        if (additionalValidators.validate()) {
          self.showButton(false);
          if (this.hasMessage()) {
            return;
          }
          klarna.authorize(self.getCategoryId(), klarna.getUpdateData(), function (res) {
            debug.log(res);
            if (res.approved) {
              if (res.finalize_required) {
                self.finalize();
                return;
              }
              self.placeOrder();
            }

            if (res.show_form === false) {
              self.showButton(false);
            } else {
              self.showButton(true);
            }

          });
        }
      },
      finalize: function () {
        var self = this;

        if (this.hasMessage()) {
          self.showButton(false);
          return;
        }
        klarna.finalize(self.getCategoryId(), klarna.getUpdateData(), function (res) {
          debug.log(res);
          if (res.approved) {
            self.placeOrder();
          }
          self.showButton(true);
        });
      },
      afterPlaceOrder: function () {
        setPaymentMethodAction(this.messageContainer);
        return;
      }
    });
  }
);
