<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\CreditCard\Service\Creator\Request\Order\AmountDataBuilder;
use Worldline\CreditCard\Service\Creator\Request\Order\ReferenceDataBuilder;

class OrderDataBuilder
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var AmountDataBuilder
     */
    private $amountDataBuilder;

    /**
     * @var ReferenceDataBuilder
     */
    private $referenceDataBuilder;

    public function __construct(
        OrderFactory $orderFactory,
        AmountDataBuilder $amountDataBuilder,
        ReferenceDataBuilder $referenceDataBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->amountDataBuilder = $amountDataBuilder;
        $this->referenceDataBuilder = $referenceDataBuilder;
    }

    public function build(CartInterface $quote): Order
    {
        $order = $this->orderFactory->create();

        $order->setAmountOfMoney($this->amountDataBuilder->build($quote));
        $order->setReferences($this->referenceDataBuilder->build($quote));

        return $order;
    }
}
