<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FrequencyOfAttempts implements OptionSourceInterface
{
    public const THIRTY_MINUTES = 30;
    public const TWO_HOURS_IN_MINUTES = 120;
    public const EIGHT_HOURS_IN_MINUTES = 480;
    public const DAY_IN_MINUTES = 1440;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::THIRTY_MINUTES, 'label' => __('Once every 30 minutes')],
            ['value' => self::TWO_HOURS_IN_MINUTES, 'label' => __('Once every 2 hours')],
            ['value' => self::EIGHT_HOURS_IN_MINUTES, 'label' => __('Once every 8 hours')],
            ['value' => self::DAY_IN_MINUTES, 'label' => __('Once every day')]
        ];
    }
}
