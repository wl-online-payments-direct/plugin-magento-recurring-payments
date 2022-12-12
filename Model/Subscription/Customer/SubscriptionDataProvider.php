<?php

declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\TransactionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\CreditCard\Ui\ConfigProvider as CCConfigProvider;
use Worldline\HostedCheckout\Ui\ConfigProvider as HCConfigProvider;
use Worldline\RedirectPayment\Ui\ConfigProvider as RPConfigProvider;

class SubscriptionDataProvider
{
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        RepositoryInterface $subscriptionRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getSubscriptions(int $customerId): array
    {
        $customerIdCondition = $this->filterBuilder->setField(SubscriptionInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->setConditionType('eq')
            ->create();

        $ccCondition = $this->filterBuilder->setField(SubscriptionInterface::PAYMENT_METHOD)
            ->setValue(CCConfigProvider::CODE)
            ->setConditionType('eq')
            ->create();

        $hcCondition = $this->filterBuilder->setField(SubscriptionInterface::PAYMENT_METHOD)
            ->setValue(HCConfigProvider::HC_CODE)
            ->setConditionType('eq')
            ->create();

        $rpCondition = $this->filterBuilder->setField(SubscriptionInterface::PAYMENT_METHOD)
            ->setValue(RPConfigProvider::CODE)
            ->setConditionType('eq')
            ->create();

        $ccFilter = $this->filterGroupBuilder->addFilter($customerIdCondition)->addFilter($ccCondition)->create();
        $hcFilter = $this->filterGroupBuilder->addFilter($customerIdCondition)->addFilter($hcCondition)->create();
        $rpFilter = $this->filterGroupBuilder->addFilter($customerIdCondition)->addFilter($rpCondition)->create();

        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$ccFilter, $hcFilter, $rpFilter])->create();

        return $this->subscriptionRepository->getList($searchCriteria)->getItems();
    }

    public function getRelatedOrders(array $subscriptions): array
    {
        $orderIds = array_map(
            function (SubscriptionInterface $subscription) {
                return $subscription->getOrderId();
            },
            $subscriptions
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds, 'in')
            ->create();

        return $this->orderRepository->getList($searchCriteria)->getItems();
    }

    public function getRelatedProducts(array $subscriptions): array
    {
        $productIds = array_map(
            function (SubscriptionInterface $subscription) {
                return $subscription->getProductId();
            },
            $subscriptions
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }

    public function getRelatedLastTransactions(array $subscriptions): array
    {
        $subscriptionIds = array_map(
            function (SubscriptionInterface $subscription) {
                return $subscription->getSubscriptionId();
            },
            $subscriptions
        );

        return $this->transactionRepository->getLastRelatedTransactions($subscriptionIds);
    }
}
