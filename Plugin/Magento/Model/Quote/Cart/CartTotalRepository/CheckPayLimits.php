<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Plugin\Magento\Model\Quote\Cart\CartTotalRepository;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsItemExtensionInterfaceFactory;
use Worldline\RecurringPayments\Model\ConfigProvider;
use Worldline\RecurringPayments\Model\Limit\PayLimitValidator;

class CheckPayLimits
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TotalsItemExtensionInterfaceFactory
     */
    private $extensionFactory;

    /**
     * @var PayLimitValidator
     */
    private $payLimitValidator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteValidate $quoteValidate,
        TotalsItemExtensionInterfaceFactory $extensionFactory,
        PayLimitValidator $payLimitValidator,
        ConfigProvider $configProvider
    ) {
        $this->quoteValidate = $quoteValidate;
        $this->quoteRepository = $quoteRepository;
        $this->extensionFactory = $extensionFactory;
        $this->payLimitValidator = $payLimitValidator;
        $this->configProvider = $configProvider;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CartTotalRepositoryInterface $subject, TotalsInterface $result, $cartId): TotalsInterface
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$this->configProvider->isLimitsNotificationEnabled((int)$quote->getStoreId())) {
            return $result;
        }

        foreach ($result->getItems() as $item) {
            $quoteItem = $quote->getItemById($item->getItemId());
            if ($this->quoteValidate->validateQuoteItem($quoteItem)) {
                $extensionAttributes = $item->getExtensionAttributes() ?: $this->extensionFactory->create();
                $extensionAttributes->setWorldlineRecurringLimitExceed(
                    !$this->payLimitValidator->validate($quoteItem)
                );
                $item->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }
}
