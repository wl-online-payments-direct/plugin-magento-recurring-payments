<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\HostedCheckout\Service\CreateHostedCheckoutRequest\SepaDDSIBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBase;
use Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\SepaDirectDebitSIBuilder;

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
     * @see \Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\SepaDirectDebitSIBuilder
     * @see \Worldline\RedirectPayment\Service\CreateHostedCheckoutRequest\SepaDirectDebitSIBuilder
     */
    public function execute(Observer $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');
        if (!$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        $sepaDebitSI = $observer->getData(SepaDirectDebitSIBuilder::HC_SEPA_SPECIFIC_INPUT);
        if (!$sepaDebitSI instanceof SepaDirectDebitPaymentMethodSpecificInputBase) {
            return;
        }

        $sepaDebitSI->getPaymentProduct771SpecificInput()->getMandate()->setSignatureType('SMS');
        $sepaDebitSI->getPaymentProduct771SpecificInput()->getMandate()->setRecurrenceType('RECURRING');
    }
}
