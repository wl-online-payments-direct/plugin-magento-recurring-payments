<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const FREQUENCY_XPATH = 'amasty_recurring_payments/worldline/frequency';
    public const EMAIL_TEMPLATE_XPATH = 'amasty_recurring_payments/worldline/email_template';
    public const ATTEMPTS_TO_WITHDRAW_XPATH = 'amasty_recurring_payments/worldline/attempts_to_withdraw';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getAttemptsToWithdraw(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(
            self::ATTEMPTS_TO_WITHDRAW_XPATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFrequency(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(self::FREQUENCY_XPATH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getEmailTemplate(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE_XPATH,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }
}
