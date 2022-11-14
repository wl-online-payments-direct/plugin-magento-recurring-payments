<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\RedirectPayment\Service\Creator\Request\RedirectPaymentMethodSIDBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificInput;
use Worldline\RedirectPayment\Service\Creator\Request\RedirectPaymentMethodSpecificInputDataBuilder;

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
     * @see \Worldline\RedirectPayment\Service\Creator\Request\RedirectPaymentMethodSpecificInputDataBuilder::build()
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

        /** @var RedirectPaymentMethodSpecificInput $redirectPaymentMethodSpecificInput */
        $redirectPaymentMethodSpecificInput = $observer->getData(
            RedirectPaymentMethodSpecificInputDataBuilder::RP_METHOD_SPECIFIC_INPUT
        );

        if (!$redirectPaymentMethodSpecificInput) {
            return;
        }

        $redirectPaymentMethodSpecificInput->setTokenize(true);
    }
}
