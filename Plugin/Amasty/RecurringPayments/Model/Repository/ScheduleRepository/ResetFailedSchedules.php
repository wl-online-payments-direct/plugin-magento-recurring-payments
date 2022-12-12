<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Amasty\RecurringPayments\Model\Repository\ScheduleRepository;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Repository\ScheduleRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Worldline\RecurringPayments\Model\ResourceModel\ScheduleProvider;

class ResetFailedSchedules
{
    /**
     * @var ScheduleProvider
     */
    private $scheduleProvider;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var array
     */
    private $paymentMethods;

    public function __construct(
        ScheduleProvider $scheduleProvider,
        RepositoryInterface $subscriptionRepository,
        array $paymentMethods = []
    ) {
        $this->scheduleProvider = $scheduleProvider;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * Reset the retry attempt amount for failed payments if success happens during the payment process
     *
     * @param ScheduleRepository $subject
     * @param null $result
     * @param ScheduleInterface $schedule
     * @param string $finishedDate
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterHandleSuccess(
        ScheduleRepository $subject,
        $result,
        ScheduleInterface $schedule,
        string $finishedDate
    ): void {
        $subscriptionId = $schedule->getSubscriptionId();
        $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);

        if (!in_array($subscription->getPaymentMethod(), $this->paymentMethods, true)) {
            return;
        }

        $this->scheduleProvider->updateFailedSchedules($subscriptionId);
    }
}
