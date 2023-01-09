<?php

declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\PaymentCore\Api\Data\CanPlaceOrderContextInterfaceFactory;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Model\Order\ValidatorPool\StatusCodeValidator;

class TransactionPartHandler implements HandlerPartInterface
{
    /**
     * @var GetPaymentServiceInterface
     */
    private $getPaymentService;

    /**
     * @var StatusCodeValidator
     */
    private $statusCodeValidator;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;
    /**
     * @var CanPlaceOrderContextInterfaceFactory
     */
    private $canPlaceOrderContextFactory;

    public function __construct(
        GetPaymentServiceInterface $getPaymentService,
        StatusCodeValidator $statusCodeValidator,
        PaymentDataManagerInterface $paymentDataManager,
        CanPlaceOrderContextInterfaceFactory $canPlaceOrderContextFactory
    ) {
        $this->getPaymentService = $getPaymentService;
        $this->statusCodeValidator = $statusCodeValidator;
        $this->paymentDataManager = $paymentDataManager;
        $this->canPlaceOrderContextFactory = $canPlaceOrderContextFactory;
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
        if ($this->canPlaceOrder($paymentResponse, $quote)) {
            $this->paymentDataManager->savePaymentData($paymentResponse);
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

    private function canPlaceOrder(PaymentResponse $paymentResponse, CartInterface $quote): bool
    {
        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
        $paymentId = (string)$quote->getPayment()->getAdditionalInformation(PaymentDataBuilder::PAYMENT_ID);

        $context = $this->canPlaceOrderContextFactory->create();
        $context->setStatusCode($statusCode);
        $context->setWorldlinePaymentId($paymentId);
        $context->setStoreId($quote->getStoreId());

        try {
            $this->statusCodeValidator->validate($context);
            return true;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
