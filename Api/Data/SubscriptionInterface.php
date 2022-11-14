<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Api\Data;

interface SubscriptionInterface
{
    public const ID = 'id';
    public const TOKEN = 'token';
    public const INCREMENT_ID = 'increment_id';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';

    public function getToken(): ?string;

    public function setToken(string $token): void;

    public function getIncrementId(): string;

    public function setIncrementId(string $incrementId): void;

    public function getSubscriptionId(): ?string;

    public function setSubscriptionId(string $subscriptionId): void;

    public function getPaymentProductId(): int;

    public function setPaymentProductId(int $paymentProductId): void;
}
