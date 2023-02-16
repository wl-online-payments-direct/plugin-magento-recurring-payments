define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Worldline_RecurringPayments/js/model/pay-limit-storage'
], function ($, ko, _, quote, payLimitStorage) {
    'use strict';

    return function (Component) {
        return Component.extend({
            defaults: {
                placeOrderButtonSelector: '#checkout-payment-method-load .action.checkout',
                paymentBlockSelector: '#checkout-payment-method-load',
                amastyCheckoutActionsBlock: '.checkout-column.opc'
            },

            initialize: function () {
                this._super();
                this.initPaymentSubscriber();

                return this;
            },

            initPaymentSubscriber: _.once(function () {
                quote.paymentMethod.subscribe(this.updateIsPlaceOrderActionAllowed, this);
            }),

            updateIsPlaceOrderActionAllowed: function () {
                if (payLimitStorage.isLimitNotificationEnabled && payLimitStorage.isLimitExceeded) {
                    this.blockPaymentBlock(true)
                }
            },

            blockPaymentBlock: function (state) {
                let visiblePlaceOrderButtons = $(this.placeOrderButtonSelector + ':visible');

                if (visiblePlaceOrderButtons.length) {
                    visiblePlaceOrderButtons.prop('disabled', state);
                }

                $(this.paymentBlockSelector).toggleClass('-wl-blocked', state);
                $(this.amastyCheckoutActionsBlock).toggleClass('-wl-blocked', state);
            }
        });
    };
});
