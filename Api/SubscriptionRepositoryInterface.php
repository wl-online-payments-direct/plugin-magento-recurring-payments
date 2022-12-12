<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Api;

use Worldline\RecurringPayments\Api\Data\SubscriptionInterface;

interface SubscriptionRepositoryInterface
{
    public function save(SubscriptionInterface $subscription): SubscriptionInterface;

    public function getByIncrementId(string $incrementId): SubscriptionInterface;

    public function getBySubscriptionId(string $subscriptionId): SubscriptionInterface;
}
