<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Amasty\RecurringPayments\Model\Repository\ScheduleRepository;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Repository\ScheduleRepository;
use Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\RecurringPayments\Model\Subscription\Email\EmailNotifier;
use Magento\Framework\UrlInterface;
use Worldline\RecurringPayments\Model\ConfigProvider;
use Worldline\RecurringPayments\Model\ResourceModel\ScheduleProvider;
use Worldline\RecurringPayments\Model\ScheduleManager;

class CreateNewSchedule
{
    public const RENEW_URL_KEY = 'renew_url';
    public const RENEW_TOKEN_URL = 'wl_recurring/payment/renewToken';

    /**
     * @var EmailNotifier
     */
    private $emailNotifier;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ScheduleManager
     */
    private $scheduleManager;

    /**
     * @var ScheduleProvider
     */
    private $scheduleProvider;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionCancelOperation
     */
    private $subscriptionCancelOperation;

    /**
     * @var array
     */
    private $paymentMethods;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        EmailNotifier $emailNotifier,
        ConfigProvider $configProvider,
        ScheduleManager $scheduleManager,
        ScheduleProvider $scheduleProvider,
        RepositoryInterface $subscriptionRepository,
        SubscriptionCancelOperation $subscriptionCancelOperation,
        UrlInterface $urlBuilder,
        array $paymentMethods = []
    ) {
        $this->emailNotifier = $emailNotifier;
        $this->configProvider = $configProvider;
        $this->scheduleManager = $scheduleManager;
        $this->scheduleProvider = $scheduleProvider;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
        $this->paymentMethods = $paymentMethods;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Create new schedule for failed payments
     *
     * @param ScheduleRepository $subject
     * @param null $result
     * @param ScheduleInterface $schedule
     * @param string $errorMessage
     * @param string $finishedDate
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function afterHandleError(
        ScheduleRepository $subject,
        $result,
        ScheduleInterface $schedule,
        string $errorMessage,
        string $finishedDate
    ): void {
        $subscriptionId = $schedule->getSubscriptionId();
        $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);

        if (!in_array($subscription->getPaymentMethod(), $this->paymentMethods, true)) {
            return;
        }

        $storeId = (int)$subscription->getStoreId();

        $this->emailNotifier->sendEmail(
            $subscription,
            $this->configProvider->getEmailTemplate($storeId),
            [
                ScheduleInterface::SUBSCRIPTION_ID => $subscriptionId,
                self::RENEW_URL_KEY => $this->urlBuilder->getUrl(
                    self::RENEW_TOKEN_URL,
                    [
                        '_scope' => $storeId,
                        ScheduleInterface::SUBSCRIPTION_ID => $subscriptionId
                    ]
                )
            ]
        );

        $failedSchedules = $this->scheduleProvider->getFailedSchedules($subscriptionId);
        $countOfFailedSchedules = count($failedSchedules);
        if ($countOfFailedSchedules <= $this->configProvider->getAttemptsToWithdraw($storeId)) {
            $this->scheduleManager->createNewSchedule($subscriptionId, $storeId);
            return;
        }

        $this->subscriptionCancelOperation->execute($subscription);
    }
}
