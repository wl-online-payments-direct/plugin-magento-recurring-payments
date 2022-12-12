<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule as ScheduleResource;
use Amasty\RecurringPayments\Model\ScheduleFactory;
use DateTime;

class ScheduleManager
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    public function __construct(
        ConfigProvider $configProvider,
        ScheduleFactory $scheduleFactory,
        ScheduleResource $scheduleResource
    ) {
        $this->configProvider = $configProvider;
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleResource = $scheduleResource;
    }

    public function createNewSchedule(string $subscriptionId, int $storeId): void
    {
        /** @var ScheduleInterface $newSchedule */
        $newSchedule = $this->scheduleFactory->create();

        $now = new DateTime;
        $now->modify('+' . $this->configProvider->getFrequency($storeId) . ' minutes');

        $newSchedule->setScheduledAt($now);
        $newSchedule->setSubscriptionId($subscriptionId);
        $newSchedule->setJobCode(ScheduleInterface::JOB_CODE_SUBSCRIPTION_CHARGE);

        $this->scheduleResource->save($newSchedule);
    }
}
