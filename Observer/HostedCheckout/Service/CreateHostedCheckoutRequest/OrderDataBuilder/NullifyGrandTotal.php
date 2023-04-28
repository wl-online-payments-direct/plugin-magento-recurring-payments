<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\HostedCheckout\Service\CreateHostedCheckoutRequest\OrderDataBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Order;
use Worldline\HostedCheckout\Service\CreateHostedCheckoutRequest\OrderDataBuilder;
use Worldline\RecurringPayments\Model\QuoteContext;

/**
 * Nullify the grand total to execute new query for renew token
 */
class NullifyGrandTotal implements ObserverInterface
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

    public function execute(Observer $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getData('quote');
        if (!$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        /** @var Order $orderData */
        $orderData = $observer->getData(OrderDataBuilder::ORDER_DATA);
        if (!$orderData instanceof Order) {
            return;
        }

        $quoteFromContext = $this->quoteContext->getQuote();
        if ($quoteFromContext->getRenewTokenProcessFlag() && $quote->getId() == $quoteFromContext->getId()) {
            $quote->setGrandTotal(0);
            $orderData->getAmountOfMoney()->setAmount(0);
        }
    }
}
