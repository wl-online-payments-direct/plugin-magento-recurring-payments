define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote',
    'Worldline_RecurringPayments/js/model/pay-limit-storage'
], function ($, _, $t, alert, messageList, quote, payLimitStorage) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();

                if (payLimitStorage.isLimitNotificationEnabled && payLimitStorage.isLimitExceeded) {
                    messageList.addErrorMessage({'message': $.mage.__(payLimitStorage.limitNotificationMessage)});
                }

                quote.paymentMethod.subscribe(function () {
                    if (payLimitStorage.isLimitNotificationEnabled && payLimitStorage.isLimitExceeded) {
                        alert({
                            content: $.mage.__(payLimitStorage.limitNotificationMessage + ' ' + payLimitStorage.returnToCartPageMessage),
                        });
                    }

                }, this);

                return this;
            }
        });
    };
});
