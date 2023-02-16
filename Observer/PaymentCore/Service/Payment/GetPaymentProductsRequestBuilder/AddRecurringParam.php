<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\PaymentCore\Service\Payment\GetPaymentProductsRequestBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use Worldline\RecurringPayments\Model\QuoteContext;
use Worldline\PaymentCore\Service\Payment\GetPaymentProductsRequestBuilder;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddRecurringParam implements ObserverInterface
{
    /**
     * @var QuoteContext
     */
    private $quoteContext;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(QuoteContext $quoteContext, QuoteValidate $quoteValidate)
    {
        $this->quoteContext = $quoteContext;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @see \Worldline\PaymentCore\Service\Payment\GetPaymentProductsRequestBuilder::build()
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var GetPaymentProductsParams $getPaymentProductsParams */
        $getPaymentProductsParams = $observer->getData(GetPaymentProductsRequestBuilder::GET_PAYMENT_PRODUCTS_PARAMS);
        if (!$getPaymentProductsParams) {
            return;
        }

        if (!$this->quoteValidate->validateQuote($this->quoteContext->getQuote())) {
            return;
        }

        $getPaymentProductsParams->setIsRecurring(true);
    }
}
