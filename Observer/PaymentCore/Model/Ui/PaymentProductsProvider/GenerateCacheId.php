<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\PaymentCore\Model\Ui\PaymentProductsProvider;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GenerateCacheId implements ObserverInterface
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
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @see \Worldline\PaymentCore\Ui\PaymentProductsProvider::generateCacheIdentifier()
     */
    public function execute(Observer $observer): void
    {
        $cacheIdEntity = $observer->getData('cache_identifier');
        if (!$cacheIdEntity instanceof CacheIdentifierInterface) {
            return;
        }

        if (!$this->quoteValidate->validateQuote($this->checkoutSession->getQuote())) {
            return;
        }

        $cacheIdEntity->setCacheIdentifier($cacheIdEntity->getCacheIdentifier() . '_' . 'worldline_recurring_payments');
    }
}
