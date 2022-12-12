<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Vault\Model\PaymentMethodList;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Vault\Model\PaymentMethodList;
use Worldline\CreditCard\Vault\Vault as CCVault;
use Worldline\HostedCheckout\Vault\Vault as HCVault;
use Worldline\RedirectPayment\Vault\Vault as RPVault;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class HideVault
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(Session $checkoutSession, QuoteValidate $quoteValidate)
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * Hide vault for recurring quote
     *
     * @param PaymentMethodList $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetActiveList(PaymentMethodList $subject, array $result): array
    {
        if (!$this->quoteValidate->validateQuote($this->checkoutSession->getQuote())) {
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
