<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Magento\Vault\Model\PaymentMethodList;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Vault\Model\PaymentMethodList;
use Worldline\CreditCard\Vault\Vault as CCVault;
use Worldline\HostedCheckout\Vault\Vault as HCVault;
use Worldline\RecurringPayments\Model\QuoteContext;
use Worldline\RedirectPayment\Vault\Vault as RPVault;

class HideVault
{
    /**
     * @var QuoteContext
     */
    private $quoteContext;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(QuoteContext $quoteContext, QuoteValidate $quoteValidate)
    {
        $this->quoteContext = $quoteContext;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * Hide vault for recurring quote
     *
     * @param PaymentMethodList $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetActiveList(PaymentMethodList $subject, array $result): array
    {
        if (!$this->quoteValidate->validateQuote($this->quoteContext->getQuote())) {
            return $result;
        }

        foreach ($result as $key => $vaultPayment) {
            if ($vaultPayment instanceof CCVault
                || $vaultPayment instanceof HCVault
                || $vaultPayment instanceof RPVault
            ) {
                unset($result[$key]);
            }
        }

        return $result;
    }
}
