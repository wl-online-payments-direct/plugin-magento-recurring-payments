<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Limit;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class PaymentsCounter
{
    /**
     * @var QuoteGenerator
     */
    private $quoteGenerator;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var StartEndDatesResolver
     */
    private $startEndDatesResolver;

    public function __construct(
        QuoteGenerator $quoteGenerator,
        ItemDataRetriever $itemDataRetriever,
        DateTimeInterval $dateTimeInterval,
        StartEndDatesResolver $startEndDatesResolver
    ) {
        $this->quoteGenerator = $quoteGenerator;
        $this->itemDataRetriever = $itemDataRetriever;
        $this->dateTimeInterval = $dateTimeInterval;
        $this->startEndDatesResolver = $startEndDatesResolver;
    }

    public function getBaseAmountPerMonth(AbstractItem $item): float
    {
        $plan = $this->itemDataRetriever->getPlan($item);
        if (!$plan) {
            return 0.0;
        }

        [$startDate, $endDate] = $this->startEndDatesResolver->getStartEndDatesForCount($item);
        $disableDiscount = (bool)$plan->getEnableDiscountLimit();

        try {
            $quote = $this->quoteGenerator->generateFromItem($item, $disableDiscount);
        } catch (LocalizedException $e) {
            return 0.0;
        }

        $countPayments = $this->dateTimeInterval->getCountIntervalsBetweenDates(
            $startDate,
            $endDate,
            $plan->getFrequency(),
            $plan->getFrequencyUnit()
        );

        return $countPayments * $quote->getBaseGrandTotal();
    }
}
