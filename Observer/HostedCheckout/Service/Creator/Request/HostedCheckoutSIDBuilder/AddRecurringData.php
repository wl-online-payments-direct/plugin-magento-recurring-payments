<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\HostedCheckout\Service\Creator\Request\HostedCheckoutSIDBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckout;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckoutFactory;
use OnlinePayments\Sdk\Domain\PaymentProductFilter;
use OnlinePayments\Sdk\Domain\PaymentProductFilterFactory;
use Worldline\HostedCheckout\Service\Creator\Request\SpecificInputDataBuilder;
use Worldline\PaymentCore\Model\Ui\PaymentProductsProvider;

class AddRecurringData implements ObserverInterface
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var PaymentProductsProvider
     */
    private $payProductsProvider;

    /**
     * @var PaymentProductFilterFactory
     */
    private $paymentProductFilterFactory;

    /**
     * @var PaymentProductFiltersHostedCheckoutFactory
     */
    private $paymentProductFiltersHCFactory;

    public function __construct(
        QuoteValidate $quoteValidate,
        PaymentProductsProvider $payProductsProvider,
        PaymentProductFilterFactory $paymentProductFilterFactory,
        PaymentProductFiltersHostedCheckoutFactory $paymentProductFiltersHCFactory
    ) {
        $this->quoteValidate = $quoteValidate;
        $this->payProductsProvider = $payProductsProvider;
        $this->paymentProductFilterFactory = $paymentProductFilterFactory;
        $this->paymentProductFiltersHCFactory = $paymentProductFiltersHCFactory;
    }

    /**
     * @see \Worldline\HostedCheckout\Service\Creator\Request\SpecificInputDataBuilder::build()
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

        /** @var HostedCheckoutSpecificInput $hostedCheckoutSpecificInput */
        $hostedCheckoutSpecificInput = $observer->getData(SpecificInputDataBuilder::HOSTED_CHECKOUT_SPECIFIC_INPUT);
        if (!$hostedCheckoutSpecificInput) {
            return;
        }

        $payProducts = $this->payProductsProvider->getPaymentProducts((int)$quote->getStoreId());

        /** @var PaymentProductFilter $paymentProductFilter */
        $paymentProductFilter = $this->paymentProductFilterFactory->create();
        $paymentProductFilter->setProducts(array_keys($payProducts));

        /** @var PaymentProductFiltersHostedCheckout $paymentProductFiltersHC */
        $paymentProductFiltersHC = $this->paymentProductFiltersHCFactory->create();
        $paymentProductFiltersHC->setRestrictTo($paymentProductFilter);

        $hostedCheckoutSpecificInput->setPaymentProductFilters($paymentProductFiltersHC);
    }
}
