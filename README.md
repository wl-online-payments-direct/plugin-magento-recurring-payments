# Worldline Online Payments

## Recurring Payments

[![M2 Coding Standard](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/coding-standard.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/mess-detector.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/mess-detector.yml)

This is optional module base on [amasty recurring payment](https://amasty.com/subscriptions-recurring-payments-for-magento-2.html) solution.

This addon includes the following worldline solutions:
- [credit card](https://github.com/wl-online-payments-direct/plugin-magento-creditcard)
- [hosted checkout](https://github.com/wl-online-payments-direct/plugin-magento-hostedcheckout)
- [redirect payments (single payment buttons)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments)

Change log:

#### 1.2.3
- Add uninstall script.
- Update release notes.

#### 1.2.2
- General code improvements and bug fixes.

#### 1.2.1
- Support the 1.3.1 version of Redirect Payments (single payment button) 

#### 1.2.0
- Add retry mechanism configuration to withdraw the money in case a payment has failed
- Token deletion when a subscription is canceled
- Emails are now sent to the customer in case of a failed payment
- Improved cancelation process of recurring payments
- General code improvements and bug fixes

#### 1.1.1
- Improve work for multi website instances

#### 1.1.0
- Improve the "waiting" page
- Add the "pending" page

#### 1.0.0
- Support recurring payments based on Amasty recurring payment extension
