<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer\SubscriptionGrid;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Model\Subscription\GridSource;

class SubscriptionInfoFormatter extends GridSource
{
    public function formatSubscriptionDate(int $timestamp): string
    {
        return $this->formatDate($timestamp);
    }

    public function formatSubscriptionPrice(float $price, string $currency): string
    {
        return $this->formatPrice($price, $currency);
    }

    public function addCountryToAddress(AddressInterface $address): void
    {
        $this->setCountry($address);
    }

    public function addStreetToAddress(AddressInterface $address): void
    {
        $this->setStreet($address);
    }
}
