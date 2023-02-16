<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer\SubscriptionGrid;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\DateTime\DateTimeComparer;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Magento\Framework\Intl\DateTimeFactory;

class SubscriptionInfoStatusDataAssigner
{
    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var DateTimeComparer
     */
    private $dateTimeComparer;

    /**
     * @var SubscriptionInfoFormatter
     */
    private $subscriptionInfoFormatter;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    public function __construct(
        DateTimeInterval $dateTimeInterval,
        DateTimeComparer $dateTimeComparer,
        SubscriptionInfoFormatter $subscriptionInfoFormatter,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->dateTimeInterval = $dateTimeInterval;
        $this->dateTimeComparer = $dateTimeComparer;
        $this->subscriptionInfoFormatter = $subscriptionInfoFormatter;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    public function addData(
        SubscriptionInterface $subscription,
        SubscriptionInfoInterface $subscriptionInfo,
        ?string $currency = null
    ): void {
        if ($subscription->getStatus() !== SubscriptionInterface::STATUS_ACTIVE) {
            $this->setCanceledStatus($subscriptionInfo);

            return;
        }

        $nextBillingDate = $this->getNextBillingDate($subscription);

        if ($this->isNextDateExists($subscription, $nextBillingDate)) {
            $subscriptionInfo->setNextBilling(
                $this->subscriptionInfoFormatter->formatSubscriptionDate(strtotime($nextBillingDate))
            );

            $baseNextBillingAmount = $this->getNextBillingAmount($subscription);
            $subscriptionInfo->setNextBillingAmount(
                $this->subscriptionInfoFormatter->formatSubscriptionPrice($baseNextBillingAmount, $currency)
            );
            $this->setActiveStatus($subscriptionInfo);
        } else {
            $this->setCanceledStatus($subscriptionInfo);
        }

        $this->setTrial($subscriptionInfo, $subscription);
    }

    private function isNextDateExists(SubscriptionInterface $subscription, string $nextBillingDate): bool
    {
        if (!$subscriptionEndDate = $subscription->getEndDate()) {
            return true;
        }

        $subscriptionEndDateObject = $this->dateTimeFactory->create($subscriptionEndDate);
        $nextBillingDateObject = $this->dateTimeFactory->create($nextBillingDate);
        return $nextBillingDateObject <= $subscriptionEndDateObject;
    }

    private function setCanceledStatus(SubscriptionInfoInterface $subscriptionInfo)
    {
        $subscriptionInfo->setIsActive(false);
        $subscriptionInfo->setStatus(__('Canceled')->__toString());
    }

    private function setActiveStatus(SubscriptionInfoInterface $subscriptionInfo)
    {
        $subscriptionInfo->setIsActive(true);
        $subscriptionInfo->setStatus(__('Active')->__toString());
    }

    private function setTrial(SubscriptionInfoInterface $subscriptionInfo, SubscriptionInterface $subscription)
    {
        if (!$this->dateTimeInterval->isTrialPeriodActive(
            $subscription->getStartDate(),
            $subscription->getTrialDays()
        )) {
            return;
        }

        $subscriptionInfo->setTrialStartDate($subscriptionInfo->getStartDate());
        $trialEndDate = $this->dateTimeInterval->getStartDateAfterTrial(
            $subscription->getStartDate(),
            $subscription->getTrialDays()
        );

        $endDate = $this->subscriptionInfoFormatter->formatSubscriptionDate(strtotime($trialEndDate));
        $subscriptionInfo->setTrialEndDate($endDate);
    }

    private function getNextBillingDate($subscription): string
    {
        if ($lastPaymentDate = $subscription->getLastPaymentDate()) {
            return $this->dateTimeInterval->getNextBillingDate(
                $lastPaymentDate,
                $subscription->getFrequency(),
                $subscription->getFrequencyUnit()
            );
        }

        if ($subscription->getTrialDays()) {
            return $this->dateTimeInterval->getStartDateAfterTrial(
                $subscription->getStartDate(),
                $subscription->getTrialDays()
            );
        }

        if (!$this->dateTimeComparer->compareDates(
            $subscription->getCreatedAt(),
            $subscription->getStartDate()
        )) {
            return $subscription->getStartDate();
        }

        return $this->dateTimeInterval->getNextBillingDate(
            $subscription->getStartDate(),
            $subscription->getFrequency(),
            $subscription->getFrequencyUnit()
        );
    }

    private function getNextBillingAmount($subscription): float
    {
        if ($subscription->getRemainingDiscountCycles() !== null
            && $subscription->getRemainingDiscountCycles() < 1
        ) {
            return (float)$subscription->getBaseGrandTotal();
        }

        return (float)$subscription->getBaseGrandTotalWithDiscount();
    }
}
