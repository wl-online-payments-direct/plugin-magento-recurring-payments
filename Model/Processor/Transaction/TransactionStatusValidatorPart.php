<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\CreditCard\Service\Getter\Request as GetterRequest;
use Worldline\PaymentCore\Api\TransactionWLResponseManagerInterface;
use Worldline\PaymentCore\Model\Transaction\TransactionStatusInterface;
use Worldline\PaymentCore\Api\PaymentManagerInterface;

class TransactionStatusValidatorPart implements HandlerPartInterface
{
    /**
     * @var GetterRequest
     */
    private $getterRequest;

    /**
     * @var TransactionWLResponseManagerInterface
     */
    private $transactionWLResponseManager;

    /**
     * @var PaymentManagerInterface
     */
    private $paymentManager;

    public function __construct(
        GetterRequest $getterRequest,
        TransactionWLResponseManagerInterface $transactionWLResponseManager,
        PaymentManagerInterface $paymentManager
    ) {
        $this->getterRequest = $getterRequest;
        $this->transactionWLResponseManager = $transactionWLResponseManager;
        $this->paymentManager = $paymentManager;
    }

    public function handlePartial(HandleOrderContext $context): bool
    {
        $quote = $context->getQuote();
        $payment = $quote->getPayment();
        $paymentId = (string)$payment->getAdditionalInformation(PaymentDataBuilder::PAYMENT_ID);
        if (!$paymentId) {
            return false;
        }

        $paymentResponse = $this->getterRequest->create($paymentId, (int)$quote->getStoreId());
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
            throw new \InvalidArgumentException('No subscription in context');
        }

        if (!$context->getQuote()) {
            throw new \InvalidArgumentException('No quote in context');
        }
    }
}
