<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\CreditCard\Service\Creator\Request\CardPaymentMethodSIDBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use Worldline\CreditCard\Gateway\Config\Config;
use Worldline\CreditCard\WebApi\CreatePaymentManagement\PaymentMethodDataAssigner;

class AddRecurringData implements ObserverInterface
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(QuoteValidate $quoteValidate)
    {
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param Observer $observer
     * @return void
     * @see \Worldline\CreditCard\Service\CreatePaymentRequest\CardPaymentMethodSIDBuilder::build()
     *
     */
    public function execute(Observer $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');
        if (!$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        $payProductId = $quote->getPayment()->getAdditionalInformation(PaymentMethodDataAssigner::PAYMENT_PRODUCT_ID);

        /** @var CardPaymentMethodSpecificInput $cardPaymentMethodSpecificInput */
        $cardPaymentMethodSpecificInput = $observer->getData('card_payment_method_specific_input');
        if (!$cardPaymentMethodSpecificInput instanceof CardPaymentMethodSpecificInput) {
            return;
        }

        $cardPaymentMethodSpecificInput->setSkipAuthentication(false);
        $cardPaymentMethodSpecificInput->setPaymentProductId($payProductId);
        $cardPaymentMethodSpecificInput->setAuthorizationMode(Config::AUTHORIZATION_MODE_SALE);
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator('first');
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor('cardholderInitiated');
        if ($cardPaymentMethodSpecificInput->getThreeDSecure()) {
            $cardPaymentMethodSpecificInput->getThreeDSecure()->setChallengeIndicator('challenge-required');
        }
    }
}
