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
use Worldline\PaymentCore\Ui\PaymentProductsProvider;
use Worldline\RecurringPayments\Model\QuoteContext;

class AddRecurringData implements ObserverInterface
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
        UrlInterface $urlBuilder,
        QuoteContext$quoteContext,
        QuoteValidate $quoteValidate,
        PaymentProductsProvider $payProductsProvider,
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

        $this->replaceReturnUrl($quote, $hostedCheckoutSpecificInput);

        $payProducts = $this->payProductsProvider->getPaymentProducts((int)$quote->getStoreId());

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
        if ($quote->getId() == $quoteFromContext->getId() && $quoteFromContext->getRenewTokenProcessFlag()) {
            $returnUrl = $this->urlBuilder->getUrl(
                self::RETURN_URL,
                ['subscription_id' => $quoteFromContext->getSubscriptionId()]
            );
            $hostedCheckoutSpecificInput->setReturnUrl($returnUrl);
        }
    }
}
