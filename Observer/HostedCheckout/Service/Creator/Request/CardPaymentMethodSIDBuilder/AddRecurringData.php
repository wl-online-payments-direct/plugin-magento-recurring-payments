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
     * @see \Worldline\HostedCheckout\Service\Creator\Request\CardPaymentMethodSpecificInputDataBuilder::build()
     * @see \Worldline\RedirectPayment\Service\Creator\Request\CardPaymentMethodSpecificInputDataBuilder::build()
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
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
        $cardPaymentMethodSpecificInput->setAuthorizationMode(Config::AUTHORIZATION_MODE_SALE);
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileSequenceIndicator('first');
        $cardPaymentMethodSpecificInput->setUnscheduledCardOnFileRequestor('cardholderInitiated');
        if ($cardPaymentMethodSpecificInput->getThreeDSecure()) {
            $cardPaymentMethodSpecificInput->getThreeDSecure()->setChallengeIndicator('challenge-required');
        }
    }
}
