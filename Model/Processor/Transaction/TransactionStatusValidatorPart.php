<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\PaymentCore\Api\PaymentManagerInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;

class TransactionStatusValidatorPart implements HandlerPartInterface
{
    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var PaymentManagerInterface
     */
    private $paymentManager;

    /**
     * @var GetPaymentServiceInterface
     */
    private $getPaymentService;

    public function __construct(
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        PaymentManagerInterface $paymentManager,
        GetPaymentServiceInterface $getPaymentService
    ) {
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->paymentManager = $paymentManager;
        $this->getPaymentService = $getPaymentService;
    }

    /**
     * Handler for subscriptions
     *
     * @param HandleOrderContext $context
     * @return bool
     * @throws LocalizedException
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        $quote = $context->getQuote();
        $payment = $quote->getPayment();
        $paymentId = (string)$payment->getAdditionalInformation(PaymentDataBuilder::PAYMENT_ID);
        if (!$paymentId) {
            return false;
        }

        $paymentResponse = $this->getPaymentService->execute($paymentId, (int)$quote->getStoreId());
        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
        if (in_array(
            $statusCode,
            [TransactionStatusInterface::PENDING_CAPTURE_CODE, TransactionStatusInterface::CAPTURED_CODE]
        )) {
            $this->paymentManager->savePayment($paymentResponse);
            $this->transactionWLResponseManager->saveTransaction($paymentResponse);
            return true;
        }

        return false;
    }

    public function validate(HandleOrderContext $context): void
    {
        if (!$context->getSubscription()) {
            throw new InvalidArgumentException('No subscription in context');
        }

        if (!$context->getQuote()) {
            throw new InvalidArgumentException('No quote in context');
        }
    }
}
