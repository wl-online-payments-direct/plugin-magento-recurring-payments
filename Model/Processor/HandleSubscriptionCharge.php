<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor;

use Amasty\RecurringPayments\Api\Processors\HandleSubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandler;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandlerFactory;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContextFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Worldline\RecurringPayments\Model\Processor\Transaction\TransactionGeneratorPart;
use Worldline\RecurringPayments\Model\Processor\Transaction\TransactionStatusValidatorPart;

class HandleSubscriptionCharge implements HandleSubscriptionInterface
{
    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CompositeHandlerFactory
     */
    private $compositeHandlerFactory;

    /**
     * @var TransactionGeneratorPart
     */
    private $transactionGeneratorPart;

    /**
     * @var HandleOrderContextFactory
     */
    private $handleOrderContextFactory;

    /**
     * @var TransactionStatusValidatorPart
     */
    private $transactionStatusValidatorPart;

    public function __construct(
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        CompositeHandlerFactory $compositeHandlerFactory,
        TransactionGeneratorPart $transactionGeneratorPart,
        HandleOrderContextFactory $handleOrderContextFactory,
        TransactionStatusValidatorPart $transactionStatusValidatorPart
    ) {
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->compositeHandlerFactory = $compositeHandlerFactory;
        $this->transactionGeneratorPart = $transactionGeneratorPart;
        $this->handleOrderContextFactory = $handleOrderContextFactory;
        $this->transactionStatusValidatorPart = $transactionStatusValidatorPart;
    }

    public function process(SubscriptionInterface $subscription): void
    {
        $order = $this->orderRepository->get((int)$subscription->getOrderId());

        $this->emulation->startEnvironmentEmulation((int)$order->getStoreId());
        $this->storeManager->getStore()->setCurrentCurrencyCode($order->getOrderCurrencyCode());

        /** @var HandleOrderContext $handleOrderContext */
        $handleOrderContext = $this->handleOrderContextFactory->create();
        $handleOrderContext->setSubscription($subscription);
        /** @var CompositeHandler $compositeHandler */
        $compositeHandler = $this->compositeHandlerFactory->create();
        $compositeHandler->addPart($this->transactionGeneratorPart, 'worldline_core_transaction', 'quote');
        $compositeHandler->addPart(
            $this->transactionStatusValidatorPart,
            'worldline_core_transaction_validate',
            'worldline_core_transaction'
        );
        $compositeHandler->handle($handleOrderContext);

        $this->emulation->stopEnvironmentEmulation();
    }
}
