<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Vault\Model\PaymentMethodList;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Vault\Model\PaymentMethodList;
use Magento\Vault\Model\VaultPaymentInterface;
use Worldline\CreditCard\Vault\Vault as CCVault;
use Worldline\HostedCheckout\Vault\Vault as HCVault;
use Worldline\RedirectPayment\Vault\Vault as RPVault;

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
     * @param PaymentMethodList $subject
     * @param array $result
     * @param $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetActiveList(PaymentMethodList $subject, array $result, $storeId): array
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
