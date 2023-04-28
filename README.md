# Worldline Online Payments

## Recurring Payments

This powerful module will allow you to:
- Set up subscriptions on any product you propose through your catalog.
- Offer free trials and free shipping.
- Define custom subscription plans or use a variety of default ones proposed.
- Automatically retry the payments when it fails based on your own preferences.
- Configure your products to define the subscriptions details available.

[![M2 Coding Standard](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/coding-standard.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/mess-detector.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments/actions/workflows/mess-detector.yml)

This is optional module base on [amasty recurring payment](https://amasty.com/subscriptions-recurring-payments-for-magento-2.html) solution.

This addon includes the following worldline solutions:
- [credit card](https://github.com/wl-online-payments-direct/plugin-magento-creditcard)
- [hosted checkout](https://github.com/wl-online-payments-direct/plugin-magento-hostedcheckout)
- [redirect payments (single payment buttons)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments)

### Change log:

#### 1.4.4
- Add support for the 5.1.0 version of PHP SDK.
- General code improvements.

#### 1.4.3
- Add support for Magento 2.4.6.
- Add support for the 5.0.0 version of PHP SDK.
- General code improvements.

#### 1.4.2
- Add fix for Adobe Commerce cloud instances.

#### 1.4.1
- General code improvements and bug fixes.

#### 1.4.0
- Add Sepa Direct Debit payment method.
- General code improvements and bug fixes.

#### 1.3.1
- Rise core modules versions.

#### 1.3.0
- Add a functionality to limit the amounts purchased for the Subscriptions & Recurring payments.
- Add a link in the subscription emails to renew the token when it is expired or payment failed.
- Add marketing content to the readme file.
- Add integration tests.
- General code improvements and bug fixes.

#### 1.2.4
- Rise core version.

#### 1.2.3
- Add uninstall script.
- Update release notes.

#### 1.2.2
- General code improvements and bug fixes.

#### 1.2.1
- Support the 1.3.1 version of Redirect Payments (single payment button).

#### 1.2.0
- Add retry mechanism configuration to withdraw the money in case a payment has failed.
- Token deletion when a subscription is canceled.
- Emails are now sent to the customer in case of a failed payment.
- Improved cancelation process of recurring payments.
- General code improvements and bug fixes.

#### 1.1.1
- Improve work for multi website instances.

#### 1.1.0
- Improve the "waiting" page.
- Add the "pending" page.

#### 1.0.0
- Support recurring payments based on Amasty recurring payment extension.
