<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\PaymentCore\Model\Ui\PaymentProductsProvider;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Worldline\PaymentCore\Api\Data\CacheIdentifierInterface;
use Worldline\RecurringPayments\Model\QuoteContext;

class GenerateCacheId implements ObserverInterface
{
    public const CACHE_KEY = 'worldline_recurring_payments';

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
     * @param Observer $observer
     * @return void
     * @see \Worldline\PaymentCore\Ui\PaymentProductsProvider::generateCacheIdentifier()
     */
    public function execute(Observer $observer): void
    {
        $cacheIdEntity = $observer->getData('cache_identifier');
        if (!$cacheIdEntity instanceof CacheIdentifierInterface) {
            return;
        }

        if (!$this->quoteValidate->validateQuote($this->quoteContext->getQuote())) {
            return;
        }

        $cacheIdEntity->setCacheIdentifier($cacheIdEntity->getCacheIdentifier() . '_' . self::CACHE_KEY);
    }
}
