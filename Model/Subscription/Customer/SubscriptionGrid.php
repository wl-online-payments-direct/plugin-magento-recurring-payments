<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer;

use Amasty\RecurringPayments\Api\Subscription\GridInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Worldline\RecurringPayments\Model\Subscription\Customer\SubscriptionGrid\SubscriptionInfoBuilder;

class SubscriptionGrid implements GridInterface
{
    /**
     * @var SubscriptionDataProvider
     */
    private $subscriptionDataProvider;

    /**
     * @var SubscriptionInfoBuilder
     */
    private $subscriptionInfoBuilder;

    /**
     * @var SubscriptionDataContainerFactory
     */
    private $subscriptionDataContainerFactory;

    public function __construct(
        SubscriptionDataProvider $subscriptionDataProvider,
        SubscriptionInfoBuilder $subscriptionInfoBuilder,
        SubscriptionDataContainerFactory $subscriptionDataContainerFactory
    ) {
        $this->subscriptionDataProvider = $subscriptionDataProvider;
        $this->subscriptionInfoBuilder = $subscriptionInfoBuilder;
        $this->subscriptionDataContainerFactory = $subscriptionDataContainerFactory;
    }

    public function process(int $customerId): array
    {
        $result = [];

        $subscriptions = $this->subscriptionDataProvider->getSubscriptions($customerId);
        $orders = $this->subscriptionDataProvider->getRelatedOrders($subscriptions);
        $products = $this->subscriptionDataProvider->getRelatedProducts($subscriptions);
        $lastTransactions = $this->subscriptionDataProvider->getRelatedLastTransactions($subscriptions);

        /** @var SubscriptionInterface $subscription */
        foreach ($subscriptions as $subscription) {
            $dataContainer = $this->subscriptionDataContainerFactory->create([
                'subscription' => $subscription,
                'order' => $orders[$subscription->getOrderId()] ?? null,
                'product' => $products[$subscription->getProductId()] ?? null,
                'lastTransaction' => $lastTransactions[$subscription->getSubscriptionId()] ?? null,
            ]);

            $result[] = $this->subscriptionInfoBuilder->build($dataContainer);
        }

        return $result;
    }
}
