<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Model\Config as AmRecurringConfig;
use Amasty\RecurringPayments\Model\Config\Source\Status;
use Amasty\RecurringPayments\Model\Subscription\Email\EmailNotifier;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\CreditCard\Service\Creator\Request;
use Worldline\RecurringPayments\Service\Creator\RequestBuilder;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionGeneratorPart implements HandlerPartInterface
{
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';

    /**
     * @var Request
     */
    private $createRequest;

    /**
     * @var RequestBuilder
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
     * @var SubscriptionRepositoryInterface
     */
    private $wlSubscriptionRepository;

    /**
     * @var RecurringTransactionGeneratorInterface
     */
    private $recurringTransactionGenerator;

    public function __construct(
        Request $createRequest,
        RequestBuilder $createRequestBuilder,
        EmailNotifier $emailNotifier,
        AmRecurringConfig $amRecurringConfig,
        OrderRepositoryInterface $orderRepository,
        SubscriptionRepositoryInterface $wlSubscriptionRepository,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator
    ) {
        $this->createRequest = $createRequest;
        $this->createRequestBuilder = $createRequestBuilder;
        $this->emailNotifier = $emailNotifier;
        $this->amRecurringConfig = $amRecurringConfig;
        $this->orderRepository = $orderRepository;
        $this->wlSubscriptionRepository = $wlSubscriptionRepository;
        $this->recurringTransactionGenerator = $recurringTransactionGenerator;
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

        $this->setToken($quote, $orderIncrementId);
        $this->setPaymentProductId($quote, $orderIncrementId);

        $request = $this->createRequestBuilder->build($quote);
        $response = $this->createRequest->create($request, $storeId);

        $transactionId = $response->getPayment()->getId();
        $payment->setAdditionalInformation(PaymentDataBuilder::PAYMENT_ID, $transactionId);
        $context->setTransactionId($transactionId);

        $recurringTransaction = $this->generateRecurringTransaction($context, $order, $transactionId);
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

    private function setToken(CartInterface $quote, string $orderIncrementId): void
    {
        $payment = $quote->getPayment();
        $wlSubscription = $this->wlSubscriptionRepository->getByIncrementId($orderIncrementId);
        $publicToken = $wlSubscription->getToken();
        if ($publicToken) {
            $payment->setAdditionalInformation(PaymentDataBuilder::TOKEN_ID, $publicToken);
        }
    }

    private function setPaymentProductId(CartInterface $quote, string $orderIncrementId): void
    {
        $payment = $quote->getPayment();
        $wlSubscription = $this->wlSubscriptionRepository->getByIncrementId($orderIncrementId);
        $payProductId = $wlSubscription->getPaymentProductId();
        if ($payProductId) {
            $payment->setAdditionalInformation(self::PAYMENT_PRODUCT_ID, $payProductId);
        }
    }

    private function generateRecurringTransaction(
        HandleOrderContext $context,
        OrderInterface $order,
        string $transactionId
    ): TransactionInterface {
        return $this->recurringTransactionGenerator->generate(
            (float)$context->getQuote()->getBaseGrandTotal(),
            $order->getIncrementId(),
            $order->getOrderCurrencyCode(),
            $transactionId,
            Status::SUCCESS,
            $context->getSubscription()->getSubscriptionId()
        );
    }
}
