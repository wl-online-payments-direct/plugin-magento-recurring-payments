<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const FREQUENCY = 'amasty_recurring_payments/worldline/frequency';
    public const EMAIL_TEMPLATE = 'amasty_recurring_payments/worldline/email_template';
    public const ATTEMPTS_TO_WITHDRAW = 'amasty_recurring_payments/worldline/attempts_to_withdraw';
    public const IS_LIMITS_NOTIFICATION_ENABLED = 'amasty_recurring_payments/worldline/limits_notification_enabled';
    public const LIMITS_NOTIFICATION_MESSAGE = 'amasty_recurring_payments/worldline/limits_notification_message';
    public const LIMIT_BASE_AMOUNT = 'amasty_recurring_payments/worldline/limit_base_amount';

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
            self::ATTEMPTS_TO_WITHDRAW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFrequency(?int $storeId = null): int
    {
        return (int)$this->scopeConfig->getValue(self::FREQUENCY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getEmailTemplate(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::EMAIL_TEMPLATE,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }

    public function isLimitsNotificationEnabled(?int $storeId = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::IS_LIMITS_NOTIFICATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLimitsNotificationMessage(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::LIMITS_NOTIFICATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getLimitBaseAmount(?int $storeId = null): float
    {
        return (float)$this->scopeConfig->getValue(self::LIMIT_BASE_AMOUNT, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
