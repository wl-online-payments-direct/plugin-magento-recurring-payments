<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Gateway\CreditCard\Response;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Payment\Gateway\Response\HandlerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use Worldline\PaymentCore\Api\QuoteResourceInterface;
use Worldline\PaymentCore\Api\SubjectReaderInterface;
use Worldline\RecurringPayments\Model\SubscriptionEntity\SubscriptionManager;

class SubscriptionDetailsHandler implements HandlerInterface
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var SubjectReaderInterface
     */
    private $subjectReader;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var QuoteResourceInterface
     */
    private $quoteResource;

    public function __construct(
        QuoteValidate $quoteValidate,
        SubjectReaderInterface $subjectReader,
        SubscriptionManager $subscriptionManager,
        QuoteResourceInterface $quoteResource
    ) {
        $this->quoteValidate = $quoteValidate;
        $this->subjectReader = $subjectReader;
        $this->subscriptionManager = $subscriptionManager;
        $this->quoteResource = $quoteResource;
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $orderIncrementId = $paymentDO->getOrder()->getOrderIncrementId();
        $quote = $this->quoteResource->getQuoteByReservedOrderId($orderIncrementId);
        if (!$quote || !$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        $transaction = $this->subjectReader->readTransaction($response);
        /** @var CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput */
        $cardPaymentMethodSpecificOutput = $transaction->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        $token = $cardPaymentMethodSpecificOutput->getToken();
        if (!$token) {
            return;
        }

        $payProductId = $cardPaymentMethodSpecificOutput->getPaymentProductId();

        $this->subscriptionManager->createWLSubscription($orderIncrementId, $token, $payProductId);
    }
}
