<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Transaction;
use Magento\Catalog\Model\Product;
use Magento\Sales\Api\Data\OrderInterface;

class SubscriptionDataContainer
{
    /**
     * @var SubscriptionInterface
     */
    private $subscription;

    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @var Product|null
     */
    private $product;

    /**
     * @var Transaction|null
     */
    private $lastTransaction;

    public function __construct(
        SubscriptionInterface $subscription,
        ?OrderInterface $order = null,
        ?Product $product = null,
        ?Transaction $lastTransaction = null
    ) {
        $this->subscription = $subscription;
        $this->order = $order;
        $this->product = $product;
        $this->lastTransaction = $lastTransaction;
    }

    public function getSubscription(): SubscriptionInterface
    {
        return $this->subscription;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getLastTransaction(): ?Transaction
    {
        return $this->lastTransaction;
    }
}
