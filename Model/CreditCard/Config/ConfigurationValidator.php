<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\CreditCard\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;
use Worldline\CreditCard\Gateway\Config\Config;

class ConfigurationValidator implements ValidatorInterface
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function enumerateConfigurationIssues(): \Generator
    {
        if (!$this->config->isActive()) {
            yield __('Wordline Credit Card payment method is not enabled');
        }
    }
}
