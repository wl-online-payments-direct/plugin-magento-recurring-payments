<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\SubscriptionEntity;

use Worldline\RecurringPayments\Api\Data\SubscriptionInterfaceFactory;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;

class SubscriptionManager
{
    /**
     * @var SubscriptionInterfaceFactory
     */
    private $wlSubscriptionFactory;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $wlSubscriptionRepository;

    public function __construct(
        SubscriptionInterfaceFactory $wlSubscriptionFactory,
        SubscriptionRepositoryInterface $wlSubscriptionRepository
    ) {
        $this->wlSubscriptionFactory = $wlSubscriptionFactory;
        $this->wlSubscriptionRepository = $wlSubscriptionRepository;
    }

    public function createWLSubscription(
        string $orderIncrementId,
        string $token,
        int $payProductId,
        ?string $subscriptionId = null
    ): void {
        $wlSubscription = $this->wlSubscriptionFactory->create();
        $wlSubscription->setToken($token);
        $wlSubscription->setPaymentProductId($payProductId);
        $wlSubscription->setIncrementId($orderIncrementId);
        if ($subscriptionId) {
            $wlSubscription->setSubscriptionId($subscriptionId);
        }

        $this->wlSubscriptionRepository->save($wlSubscription);
    }
}
