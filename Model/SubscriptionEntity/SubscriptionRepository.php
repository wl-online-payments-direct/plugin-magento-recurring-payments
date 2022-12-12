<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\SubscriptionEntity;

use Worldline\RecurringPayments\Api\Data\SubscriptionInterface;
use Worldline\RecurringPayments\Api\Data\SubscriptionInterfaceFactory;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription as SubscriptionResource;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription\Collection as SubscriptCollection;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription\CollectionFactory;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionFactory;

    /**
     * @var array
     */
    private $wlSubscriptions = [];

    public function __construct(
        CollectionFactory $collectionFactory,
        SubscriptionResource $subscriptionResource,
        SubscriptionInterfaceFactory $subscriptionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    public function save(SubscriptionInterface $subscription): SubscriptionInterface
    {
        $this->subscriptionResource->save($subscription);
        return $subscription;
    }

    public function getByIncrementId(string $incrementId): SubscriptionInterface
    {
        if (empty($this->wlSubscriptions[$incrementId])) {
            /** @var SubscriptCollection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(SubscriptionInterface::INCREMENT_ID, ['eq' => $incrementId]);
            $collection->getSelect()->limit(1);

            $this->wlSubscriptions[$incrementId] = $collection->getFirstItem();
        }

        return $this->wlSubscriptions[$incrementId];
    }

    public function getBySubscriptionId(string $subscriptionId): SubscriptionInterface
    {
        $subscription = $this->subscriptionFactory->create();

        $this->subscriptionResource->load($subscription, $subscriptionId, SubscriptionInterface::SUBSCRIPTION_ID);

        return $subscription;
    }
}
