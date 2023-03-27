<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Model\Config\Source\Status;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use OnlinePayments\Sdk\Domain\PaymentOutput;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\PaymentCore\Api\AmountFormatterInterface;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;

class TransactionGeneratorManager
{
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';

    /**
     * @var AmountFormatterInterface
     */
    private $amountFormatter;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $wlSubscriptionRepository;

    /**
     * @var RecurringTransactionGeneratorInterface
     */
    private $recurringTransactionGenerator;

    public function __construct(
        AmountFormatterInterface $amountFormatter,
        SubscriptionRepositoryInterface $wlSubscriptionRepository,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator
    ) {
        $this->amountFormatter = $amountFormatter;
        $this->wlSubscriptionRepository = $wlSubscriptionRepository;
        $this->recurringTransactionGenerator = $recurringTransactionGenerator;
    }

    public function setToken(CartInterface $quote, string $orderIncrementId): void
    {
        $payment = $quote->getPayment();
        $wlSubscription = $this->wlSubscriptionRepository->getByIncrementId($orderIncrementId);
        $publicToken = $wlSubscription->getToken();
        if ($publicToken) {
            $payment->setAdditionalInformation(PaymentDataBuilder::TOKEN_ID, $publicToken);
        }
    }

    public function setPaymentProductId(CartInterface $quote, string $orderIncrementId): void
    {
        $payment = $quote->getPayment();
        $wlSubscription = $this->wlSubscriptionRepository->getByIncrementId($orderIncrementId);
        $payProductId = $wlSubscription->getPaymentProductId();
        if ($payProductId) {
            $payment->setAdditionalInformation(self::PAYMENT_PRODUCT_ID, $payProductId);
        }
    }

    public function generateRecurringTransaction(
        HandleOrderContext $context,
        OrderInterface $order,
        PaymentOutput $paymentOutput,
        string $transactionId
    ): TransactionInterface {
        $transactionAmount = (int)$paymentOutput->getAcquiredAmount()->getAmount();
        $transactionCurrency = $paymentOutput->getAcquiredAmount()->getCurrencyCode();

        $amount = $this->amountFormatter->formatToFloat($transactionAmount, $transactionCurrency);

        return $this->recurringTransactionGenerator->generate(
            $amount,
            $order->getIncrementId(),
            $order->getOrderCurrencyCode(),
            $transactionId,
            Status::SUCCESS,
            $context->getSubscription()->getSubscriptionId()
        );
    }
}
