define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (ko, $, quote) {
    'use strict';

    let isLimitNotificationEnabled = window.checkoutConfig.worldlineRecurringCheckoutConfig.limitNotificationMessageEnabled,
        limitNotificationMessage = window.checkoutConfig.worldlineRecurringCheckoutConfig.limitNotificationMessage,
        returnToCartPageMessage = window.checkoutConfig.worldlineRecurringCheckoutConfig.returnToCartPageMessage,
        totalItems = quote.totals().items,
        isLimitExceeded = !!totalItems.find(function (item) {
            return !!item.extension_attributes && !!item.extension_attributes.worldline_recurring_limit_exceed;
        });

    return {
        isLimitNotificationEnabled: isLimitNotificationEnabled,
        limitNotificationMessage: limitNotificationMessage,
        returnToCartPageMessage: returnToCartPageMessage,
        isLimitExceeded: isLimitExceeded
    };
});
