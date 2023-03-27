<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\CreatePaymentRequest;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\PaymentCore\Api\Config\GeneralSettingsConfigInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\AmountDataBuilderInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\ReferenceDataBuilderInterface;
use Worldline\PaymentCore\Api\Service\CreateRequest\Order\SurchargeDataBuilderInterface;

class OrderDataBuilder
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var AmountDataBuilderInterface
     */
    private $amountDataBuilder;

    /**
     * @var ReferenceDataBuilderInterface
     */
    private $referenceDataBuilder;

    /**
     * @var SurchargeDataBuilderInterface
     */
    private $surchargeDataBuilder;

    /**
     * @var GeneralSettingsConfigInterface
     */
    private $generalSettings;

    public function __construct(
        OrderFactory $orderFactory,
        AmountDataBuilderInterface $amountDataBuilder,
        ReferenceDataBuilderInterface $referenceDataBuilder,
        SurchargeDataBuilderInterface $surchargeDataBuilder,
        GeneralSettingsConfigInterface $generalSettings
    ) {
        $this->orderFactory = $orderFactory;
        $this->amountDataBuilder = $amountDataBuilder;
        $this->referenceDataBuilder = $referenceDataBuilder;
        $this->surchargeDataBuilder = $surchargeDataBuilder;
        $this->generalSettings = $generalSettings;
    }

    public function build(CartInterface $quote): Order
    {
        $order = $this->orderFactory->create();

        $order->setAmountOfMoney($this->amountDataBuilder->build($quote));
        $order->setReferences($this->referenceDataBuilder->build($quote));

        if ($this->generalSettings->isApplySurcharge((int)$quote->getStoreId())
            && (float)$quote->getGrandTotal() > 0.00001
        ) {
            $order->setSurchargeSpecificInput($this->surchargeDataBuilder->build());
        }

        return $order;
    }
}
