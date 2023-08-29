<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class ScheduleProvider
{
    public const STATUS_HANDLED = 'worldline_handled';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function getFailedSchedules(string $subscriptionId): array
    {
        /** @var ScheduleCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ScheduleInterface::SUBSCRIPTION_ID, $subscriptionId)
            ->addFieldToFilter(ScheduleInterface::STATUS, ScheduleInterface::STATUS_ERROR);

        return $collection->getItems();
    }

    public function updateFailedSchedules(string $subscriptionId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $scheduleTable = $this->resourceConnection->getTableName(Schedule::TABLE_NAME);
        $insertData = [ScheduleInterface::STATUS => self::STATUS_HANDLED];
        $where = [
            ScheduleInterface::SUBSCRIPTION_ID . ' = ?' => $subscriptionId,
            ScheduleInterface::STATUS . ' = ?' => ScheduleInterface::STATUS_ERROR
        ];

        $connection->update($scheduleTable, $insertData, $where);
    }
}
