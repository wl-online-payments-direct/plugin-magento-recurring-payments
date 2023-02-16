var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Worldline_RecurringPayments/js/view/payment/list-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Worldline_RecurringPayments/js/view/payment/default-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Worldline_RecurringPayments/js/view/shipping-address/address-renderer/default-mixin': true
            },
            'Amasty_CheckoutCore/js/view/payment/list': {
                'Worldline_RecurringPayments/js/view/payment/list-mixin': true
            },
            'Amasty_CheckoutCore/js/view/shipping-address/address-renderer/default-mixin': {
                'Worldline_RecurringPayments/js/view/shipping-address/address-renderer/default-mixin': true
            }
        }
    }
};
