define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Worldline_RecurringPayments/js/model/pay-limit-storage'
], function ($, quote, payLimitStorage) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();

                quote.billingAddress.subscribe(function () {
                    if (payLimitStorage.isLimitNotificationEnabled && payLimitStorage.isLimitExceeded) {
                        this.isPlaceOrderActionAllowed(false);
                    }
                }, this);

                return this;
            }
        });
    };
});
