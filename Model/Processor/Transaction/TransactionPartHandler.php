<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Processor\Transaction;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\PaymentCore\Api\Payment\PaymentIdFormatterInterface;
use Worldline\PaymentCore\Api\PaymentDataManagerInterface;
use Worldline\PaymentCore\Api\Service\Payment\GetPaymentServiceInterface;
use Worldline\PaymentCore\Api\SurchargingQuoteManagerInterface;
use Worldline\PaymentCore\Model\Order\CanPlaceOrderContextManager;

class TransactionPartHandler implements HandlerPartInterface
{
    /**
     * @var GetPaymentServiceInterface
     */
    private $getPaymentService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PaymentIdFormatterInterface
     */
    private $paymentIdFormatter;

    /**
     * @var PaymentDataManagerInterface
     */
    private $paymentDataManager;

    /**
     * @var CanPlaceOrderContextManager
     */
    private $canPlaceOrderContextManager;

    /**
     * @var SurchargingQuoteManagerInterface
     */
    private $surchargingQuoteManager;

    public function __construct(
        GetPaymentServiceInterface $getPaymentService,
        CartRepositoryInterface $quoteRepository,
        PaymentIdFormatterInterface $paymentIdFormatter,
        PaymentDataManagerInterface $paymentDataManager,
        CanPlaceOrderContextManager $canPlaceOrderContextManager,
        SurchargingQuoteManagerInterface $surchargingQuoteManager
    ) {
        $this->getPaymentService = $getPaymentService;
        $this->quoteRepository = $quoteRepository;
        $this->paymentIdFormatter = $paymentIdFormatter;
        $this->paymentDataManager = $paymentDataManager;
        $this->canPlaceOrderContextManager = $canPlaceOrderContextManager;
        $this->surchargingQuoteManager = $surchargingQuoteManager;
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
        $payment->setMethod($context->getSubscription()->getPaymentMethod());
        $paymentId = (string)$payment->getAdditionalInformation(PaymentDataBuilder::PAYMENT_ID);
        if (!$paymentId) {
            return false;
        }

        $paymentId = $this->paymentIdFormatter->validateAndFormat($paymentId, true);
        $paymentResponse = $this->getPaymentService->execute($paymentId, (int)$quote->getStoreId());

        if ($surchargeOutput = $paymentResponse->getPaymentOutput()->getSurchargeSpecificOutput()) {
            $this->surchargingQuoteManager->formatAndSaveSurchargingQuote($quote, $surchargeOutput);
        }

        $statusCode = (int)$paymentResponse->getStatusOutput()->getStatusCode();
        $context = $this->canPlaceOrderContextManager->createContext($quote, $statusCode);
        if ($this->canPlaceOrderContextManager->canPlaceOrder($context)) {
            $this->quoteRepository->save($quote);
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
}
