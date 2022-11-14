<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\RecurringPayments\Api\Data\SubscriptionInterface;

class Subscription extends AbstractDb
{
    public const TABLE_NAME = 'worldline_recurring_payments_subscription';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, SubscriptionInterface::ID);
    }
}
