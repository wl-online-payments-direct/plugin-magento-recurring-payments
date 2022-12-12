<?php

declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Model\Config as AmRecurringConfig;
use Amasty\RecurringPayments\Model\Subscription\Email\EmailNotifier;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\CreditCard\Api\Service\Payment\CreatePaymentServiceInterface;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\RecurringPayments\Service\Payment\CreatePaymentRequestBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionGeneratorPart implements HandlerPartInterface
{
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';

    /**
     * @var CreatePaymentRequestBuilder
     */
    private $createRequestBuilder;

    /**
     * @var EmailNotifier
     */
    private $emailNotifier;

    /**
     * @var AmRecurringConfig
     */
    private $amRecurringConfig;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreatePaymentServiceInterface
     */
    private $createPaymentService;

    /**
     * @var TransactionGeneratorManager
     */
    private $generatorManager;

    public function __construct(
        CreatePaymentRequestBuilder $createRequestBuilder,
        EmailNotifier $emailNotifier,
        AmRecurringConfig $amRecurringConfig,
        OrderRepositoryInterface $orderRepository,
        CreatePaymentServiceInterface $createPaymentService,
        TransactionGeneratorManager $generatorManager
    ) {
        $this->createRequestBuilder = $createRequestBuilder;
        $this->emailNotifier = $emailNotifier;
        $this->amRecurringConfig = $amRecurringConfig;
        $this->orderRepository = $orderRepository;
        $this->createPaymentService = $createPaymentService;
        $this->generatorManager = $generatorManager;
    }

    public function handlePartial(HandleOrderContext $context): bool
    {
        $quote = $context->getQuote();
        $payment = $quote->getPayment();
        $storeId = (int)$quote->getStoreId();
        $subscription = $context->getSubscription();
        $order = $this->orderRepository->get((int)$subscription->getOrderId());
        $orderIncrementId = (string)$order->getIncrementId();

        $quote->reserveOrderId();

        $this->generatorManager->setToken($quote, $orderIncrementId);
        $this->generatorManager->setPaymentProductId($quote, $orderIncrementId);

        $request = $this->createRequestBuilder->build($quote);
        $response = $this->createPaymentService->execute($request, $storeId);

        $transactionId = $response->getPayment()->getId();
        $payment->setAdditionalInformation(PaymentDataBuilder::PAYMENT_ID, $transactionId);
        $context->setTransactionId($transactionId);

        $recurringTransaction = $this->generatorManager->generateRecurringTransaction($context, $order, $transactionId);
        $context->setRecurringTransaction($recurringTransaction);

        if ($this->amRecurringConfig->isNotifySubscriptionPurchased($storeId)) {
            $template = $this->amRecurringConfig->getEmailTemplateSubscriptionPurchased($storeId);
            $this->emailNotifier->sendEmail($subscription, $template);
        }

        return true;
    }

    public function validate(HandleOrderContext $context): void
    {
        if (!$context->getSubscription()) {
            throw new \InvalidArgumentException('No subscription in context');
        }

        if (!$context->getQuote()) {
            throw new \InvalidArgumentException('No quote in context');
        }
    }
}
