<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Setup;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Worldline\RecurringPayments\Model\SubscriptionEntity\ResourceModel\Subscription;

class Uninstall implements UninstallInterface
{
    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var CollectionFactory
     */
    private $configCollectionFactory;

    public function __construct(ConfigResource $configResource, CollectionFactory $configCollectionFactory)
    {
        $this->configResource = $configResource;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $setup->getConnection()->dropTable($setup->getTable(Subscription::TABLE_NAME));
        $this->clearConfigurations();

        $setup->endSetup();
    }

    private function clearConfigurations(): void
    {
        $collection = $this->configCollectionFactory->create()
            ->addPathFilter('amasty_recurring_payments/worldline');

        foreach ($collection->getItems() as $config) {
            $this->configResource->delete($config);
        }
    }
}
