<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\SubscriptionEntity;

use Magento\Framework\Model\AbstractModel;
use Worldline\RecurringPayments\Api\Data\SubscriptionInterface;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription as SubscriptionResource;

class Subscription extends AbstractModel implements SubscriptionInterface
{
    protected function _construct(): void
    {
        $this->_init(SubscriptionResource::class);
    }

    public function getToken(): ?string
    {
        return $this->getData(SubscriptionInterface::TOKEN);
    }

    public function setToken(string $token): void
    {
        $this->setData(SubscriptionInterface::TOKEN, $token);
    }

    public function getIncrementId(): string
    {
        return $this->getData(SubscriptionInterface::INCREMENT_ID);
    }

    public function setIncrementId(string $incrementId): void
    {
        $this->setData(SubscriptionInterface::INCREMENT_ID, $incrementId);
    }

    public function getSubscriptionId(): ?string
    {
        return $this->getData(SubscriptionInterface::SUBSCRIPTION_ID);
    }

    public function setSubscriptionId(string $subscriptionId): void
    {
        $this->setData(SubscriptionInterface::SUBSCRIPTION_ID, $subscriptionId);
    }

    public function getPaymentProductId(): int
    {
        return (int)$this->getData(SubscriptionInterface::PAYMENT_PRODUCT_ID);
    }

    public function setPaymentProductId(int $paymentProductId): void
    {
        $this->setData(SubscriptionInterface::PAYMENT_PRODUCT_ID, $paymentProductId);
    }
}
