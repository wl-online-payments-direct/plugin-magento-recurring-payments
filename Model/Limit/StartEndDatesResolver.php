<?php

declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Limit;

use Amasty\RecurringPayments\Model\Subscription\Mapper\StartEndDateMapper;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class StartEndDatesResolver
{
    /**
     * @var StartEndDateMapper
     */
    private $startEndDateMapper;

    public function __construct(StartEndDateMapper $startEndDateMapper)
    {
        $this->startEndDateMapper = $startEndDateMapper;
    }

    public function getStartEndDatesForCount(AbstractItem $item): array
    {
        [$startDate, $endDate] = $this->startEndDateMapper->getSpecifiedStartEndDates($item);
        // biggest month
        $startDateString = date('Y-m') . '-01';
        $endDateString = date('Y-m') . '-31';

        if ($endDate) {
            if (!$startDate) {
                $startDate = new \DateTime('now', $endDate->getTimezone());
            }
            $startDate->setTime(0, 0);
            $endDate->setTime(0, 0);
            $diff = $endDate->diff($startDate);
            if ($diff->days <= 31) {
                $startDateString = $startDate->format('Y-m-d');
                $endDateString = $endDate->format('Y-m-d');
            }
        }

        return [$startDateString, $endDateString];
    }
}
