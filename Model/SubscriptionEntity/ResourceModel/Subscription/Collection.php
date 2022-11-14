<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription as SubscriptionResource;
use Worldline\RecurringPayments\Model\SubscriptionEntity\Subscription as SubscriptionModel;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(SubscriptionModel::class, SubscriptionResource::class);
    }
}
