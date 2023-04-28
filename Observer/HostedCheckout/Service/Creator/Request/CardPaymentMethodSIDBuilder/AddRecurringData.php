<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\HostedCheckout\Service\Creator\Request\CardPaymentMethodSIDBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use Worldline\HostedCheckout\Gateway\Config\Config;

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
     * @see \Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\CardPaymentMethodSIDBuilder::build()
     * @see \Worldline\RedirectPayment\Service\CreateHostedCheckoutRequest\CardPaymentMethodSIDBuilder::build()
     *
     */
    public function execute(Observer $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');
        if (!$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        /** @var CardPaymentMethodSpecificInput $cardPaymentMethodSpecificInput */
        $cardPaymentMethodSpecificInput = $observer->getData('card_payment_method_specific_input');
        if (!$cardPaymentMethodSpecificInput instanceof CardPaymentMethodSpecificInput) {
            return;
        }

        $cardPaymentMethodSpecificInput->setToken(null);
        $cardPaymentMethodSpecificInput->setTokenize(true);
        $cardPaymentMethodSpecificInput->setSkipAuthentication(false);
        $cardPaymentMethodSpecificInput->setAuthorizationMode(Config::AUTHORIZATION_MODE_SALE);
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator('first');
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor('cardholderInitiated');
        if ($cardPaymentMethodSpecificInput->getThreeDSecure()) {
            $cardPaymentMethodSpecificInput->getThreeDSecure()->setChallengeIndicator('challenge-required');
        }
    }
}
