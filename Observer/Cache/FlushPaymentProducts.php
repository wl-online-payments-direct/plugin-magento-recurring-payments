<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\PaymentCore\Ui\PaymentProductsProvider;
use Worldline\RecurringPayments\Observer\PaymentCore\Model\Ui\PaymentProductsProvider\GenerateCacheId;

class FlushPaymentProducts implements ObserverInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(CacheInterface $cache, StoreManagerInterface $storeManager)
    {
        $this->cache = $cache;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $tags = [];
        $storeIds = array_keys($this->storeManager->getStores(true));
        foreach ($storeIds as $storeId) {
            $tags[] = PaymentProductsProvider::CACHE_ID . '_' . $storeId . '_' . GenerateCacheId::CACHE_KEY;
        }
        $this->cache->clean($tags);
    }
}
