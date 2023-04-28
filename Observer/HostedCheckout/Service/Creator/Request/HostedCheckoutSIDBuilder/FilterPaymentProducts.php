<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\HostedCheckout\Service\Creator\Request\HostedCheckoutSIDBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\PaymentProductFilter;
use OnlinePayments\Sdk\Domain\PaymentProductFilterFactory;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckout;
use OnlinePayments\Sdk\Domain\PaymentProductFiltersHostedCheckoutFactory;
use Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\SpecificInputDataBuilder;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\PaymentCore\Api\Ui\PaymentProductsProviderInterface;
use Worldline\RecurringPayments\Model\QuoteContext;

class FilterPaymentProducts implements ObserverInterface
{
    public const RETURN_URL = 'wl_recurring/returns/returnUrl';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var QuoteContext
     */
    private $quoteContext;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var PaymentProductsProviderInterface
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
        UrlInterface $urlBuilder,
        QuoteContext$quoteContext,
        QuoteValidate $quoteValidate,
        PaymentProductsProviderInterface $payProductsProvider,
        PaymentProductFilterFactory $paymentProductFilterFactory,
        PaymentProductFiltersHostedCheckoutFactory $paymentProductFiltersHCFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->quoteContext = $quoteContext;
        $this->quoteValidate = $quoteValidate;
        $this->payProductsProvider = $payProductsProvider;
        $this->paymentProductFilterFactory = $paymentProductFilterFactory;
        $this->paymentProductFiltersHCFactory = $paymentProductFiltersHCFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     * @see \Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\SpecificInputDataBuilder::build()
     *
     */
    public function execute(Observer $observer): void
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

        $this->replaceReturnUrl($quote, $hostedCheckoutSpecificInput);

        $payProducts = $this->payProductsProvider->getPaymentProducts((int)$quote->getStoreId());
        if ((float)$quote->getGrandTotal() < 0.00001) {
            unset($payProducts[PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID]);
        }

        /** @var PaymentProductFilter $paymentProductFilter */
        $paymentProductFilter = $this->paymentProductFilterFactory->create();
        $paymentProductFilter->setProducts(array_keys($payProducts));

        /** @var PaymentProductFiltersHostedCheckout $paymentProductFiltersHC */
        $paymentProductFiltersHC = $this->paymentProductFiltersHCFactory->create();
        $paymentProductFiltersHC->setRestrictTo($paymentProductFilter);

        $hostedCheckoutSpecificInput->setPaymentProductFilters($paymentProductFiltersHC);
    }

    private function replaceReturnUrl(
        CartInterface $quote,
        HostedCheckoutSpecificInput $hostedCheckoutSpecificInput
    ): void {
        $quoteFromContext = $this->quoteContext->getQuote();
        if ($quoteFromContext->getRenewTokenProcessFlag() && $quote->getId() == $quoteFromContext->getId()) {
            $returnUrl = $this->urlBuilder->getUrl(
                self::RETURN_URL,
                ['subscription_id' => $quoteFromContext->getSubscriptionId()]
            );
            $hostedCheckoutSpecificInput->setReturnUrl($returnUrl);
        }
    }
}
