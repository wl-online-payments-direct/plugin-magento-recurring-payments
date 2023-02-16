<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Limit;

use Worldline\RecurringPayments\Model\ConfigProvider;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class PayLimitValidator
{
    /**
     * @var PaymentsCounter
     */
    private $paymentsCounter;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(PaymentsCounter $paymentsCounter, ConfigProvider $configProvider)
    {
        $this->paymentsCounter = $paymentsCounter;
        $this->configProvider = $configProvider;
    }

    public function validate(AbstractItem $item): bool
    {
        $amount = $this->paymentsCounter->getBaseAmountPerMonth($item);
        $storeId = $item->getQuote()->getStoreId();

        return $amount <= $this->configProvider->getLimitBaseAmount($storeId);
    }
}
