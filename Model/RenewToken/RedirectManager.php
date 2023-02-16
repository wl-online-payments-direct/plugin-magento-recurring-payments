<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\RenewToken;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Api\Generators\QuoteGeneratorInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\HostedCheckout\Service\HostedCheckout\CreateHostedCheckoutRequestBuilder;
use Worldline\HostedCheckout\Service\HostedCheckout\CreateHostedCheckoutService;
use Worldline\RecurringPayments\Model\QuoteContext;

class RedirectManager
{
    public const SUBSCRIBE_OPTION = 'subscribe';

    /**
     * @var QuoteGeneratorInterface
     */
    private $quoteGenerator;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var CreateHostedCheckoutService
     */
    private $createRequest;

    /**
     * @var CreateHostedCheckoutRequestBuilder
     */
    private $createRequestBuilder;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var QuoteContext
     */
    private $quoteContext;

    /**
     * CollectionFactory
     */
    private $subscriptionPlanCollectionFactory;

    public function __construct(
        QuoteGeneratorInterface $quoteGenerator,
        RepositoryInterface $subscriptionRepository,
        CreateHostedCheckoutService $createRequest,
        CreateHostedCheckoutRequestBuilder $createRequestBuilder,
        Json $serializer,
        QuoteContext $quoteContext,
        CollectionFactory $subscriptionPlanCollectionFactory
    ) {
        $this->quoteGenerator = $quoteGenerator;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->createRequest = $createRequest;
        $this->createRequestBuilder = $createRequestBuilder;
        $this->serializer = $serializer;
        $this->quoteContext = $quoteContext;
        $this->subscriptionPlanCollectionFactory = $subscriptionPlanCollectionFactory;
    }

    public function getRenewTokenUrl(string $subscriptionId): string
    {
        $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);

        $newQuote = $this->quoteGenerator->generate($subscription);

        $payment = $newQuote->getPayment();
        $payment->setMethod($subscription->getPaymentMethod());
        $newQuote->setRenewTokenProcessFlag(true);
        $newQuote->setSubscriptionId($subscriptionId);

        $this->addRecurringOptionsToItem($newQuote);

        $this->quoteContext->setQuote($newQuote);

        $newQuote->reserveOrderId();

        $request = $this->createRequestBuilder->build($newQuote);
        $response = $this->createRequest->execute($request, (int)$newQuote->getStoreId());

        return $response->getRedirectUrl();
    }

    private function addRecurringOptionsToItem(CartInterface $newQuote): void
    {
        $item = $newQuote->getAllItems()[0];
        /** @var Collection $subscriptionPlanCollection */
        $subscriptionPlanCollection = $this->subscriptionPlanCollectionFactory->create();
        $subscriptionPlanCollection->getSelect()->limit(1);
        $subscriptionPlan = $subscriptionPlanCollection->getFirstItem();

        $buyRequestData = $item->getBuyRequest()->getData();
        $buyRequestData[self::SUBSCRIBE_OPTION] = self::SUBSCRIBE_OPTION;
        $buyRequestData[ProductRecurringAttributesInterface::SUBSCRIPTION_PLAN_ID] = $subscriptionPlan->getId();

        $buyRequest = $this->serializer->serialize($buyRequestData);
        $item->addOption(['code' => 'info_buyRequest', 'value' => $buyRequest]);
        $item->saveItemOptions();
    }
}
