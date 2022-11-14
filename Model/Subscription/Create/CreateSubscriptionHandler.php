<?php

declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Create;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Subscription\Create\CreateSubscriptionHandlerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderInterface;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;
use Worldline\RecurringPayments\Model\SubscriptionEntity\SubscriptionManager;

class CreateSubscriptionHandler implements CreateSubscriptionHandlerInterface
{
    private const SUBSCRIPTION_PREFIX = 'worldline_';

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $wlSubscriptionRepository;

    public function __construct(
        SubscriptionManager $subscriptionManager,
        SubscriptionRepositoryInterface $wlSubscriptionRepository
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->wlSubscriptionRepository = $wlSubscriptionRepository;
    }

    /**
     * @param OrderInterface $order
     * @param AbstractItem $item
     * @param SubscriptionInterface $subscription
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(OrderInterface $order, AbstractItem $item, SubscriptionInterface $subscription): void
    {
        $orderIncrementId = $order->getIncrementId();
        $subscriptionId = uniqid(self::SUBSCRIPTION_PREFIX, true);
        $subscription->setSubscriptionId($subscriptionId);

        $this->saveWLSubscription($orderIncrementId, $subscriptionId);
    }

    private function saveWLSubscription(string $incrementId, string $subscriptionId): void
    {
        $wlSubscription = $this->wlSubscriptionRepository->getByIncrementId($incrementId);
        if (!$wlSubscription->getId()) {
            return;
        }

        if (!$wlSubscription->getSubscriptionId()) {
            $wlSubscription->setSubscriptionId($subscriptionId);
            $this->wlSubscriptionRepository->save($wlSubscription);
            return;
        }

        $this->subscriptionManager->createWLSubscription(
            $incrementId,
            $wlSubscription->getToken(),
            $wlSubscription->getPaymentProductId(),
            $subscriptionId
        );
    }
}
