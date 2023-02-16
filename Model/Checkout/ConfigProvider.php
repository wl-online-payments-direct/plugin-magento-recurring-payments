<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\RecurringPayments\Model\ConfigProvider as ConfigSettings;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigSettings
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigSettings $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
    }

    public function getConfig(): array
    {
        $cartPageMessage = '';
        $storeId = (int)$this->storeManager->getStore()->getId();
        $isLimitsEnabled = $this->configProvider->isLimitsNotificationEnabled($storeId);
        $notificationMessage = $this->configProvider->getLimitsNotificationMessage($storeId);
        if ($isLimitsEnabled && strpos($notificationMessage, '{{amount-currency}}') !== false) {
            $cartPageUrl = $this->storeManager->getStore()->getUrl('checkout/cart');
            $limitAmount = (string)$this->configProvider->getLimitBaseAmount($storeId);
            $currencyCode = (string)$this->storeManager->getStore()->getBaseCurrencyCode();
            $notificationMessage = str_replace(
                '{{amount-currency}}',
                $limitAmount . ' ' . $currencyCode,
                $notificationMessage
            );
            $cartPageMessage = __('<a href="%1">Return to the cart page.</a>', $cartPageUrl);
        }

        return [
            'worldlineRecurringCheckoutConfig' => [
                'limitNotificationMessageEnabled' => $isLimitsEnabled,
                'limitNotificationMessage' => $notificationMessage,
                'returnToCartPageMessage' => $cartPageMessage
            ]
        ];
    }
}
