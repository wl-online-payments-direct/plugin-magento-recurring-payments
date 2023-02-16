<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\RedirectPayment\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;
use Worldline\RedirectPayment\Gateway\Config\Config;

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
            yield __('Wordline Hosted Checkout payment method is not enabled');
        }
    }
}
