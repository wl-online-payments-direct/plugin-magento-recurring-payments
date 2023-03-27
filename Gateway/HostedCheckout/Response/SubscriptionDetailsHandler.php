<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Gateway\HostedCheckout\Response;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Payment\Gateway\Response\HandlerInterface;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificOutput;
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
        if (!$this->quoteValidate->validateQuote($quote)) {
            return;
        }

        $transaction = $this->subjectReader->readTransaction($response);
        $paymentOutput = $this->getOutput($transaction);

        if (!$paymentOutput) {
            return;
        }

        if ($paymentOutput instanceof SepaDirectDebitPaymentMethodSpecificOutput) {
            $token = $paymentOutput->getPaymentProduct771SpecificOutput()->getMandateReference();
        } else {
            $token = $paymentOutput->getToken();
        }

        if (!$token) {
            return;
        }

        $payProductId = $paymentOutput->getPaymentProductId();

        $this->subscriptionManager->createWLSubscription($orderIncrementId, $token, $payProductId);
    }

    private function getOutput($transaction): ?DataObject
    {
        /** @var CardPaymentMethodSpecificOutput $output */
        $output = $transaction->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getCardPaymentMethodSpecificOutput();
        if ($output instanceof CardPaymentMethodSpecificOutput) {
            return $output;
        }

        /** @var RedirectPaymentMethodSpecificOutput $output */
        $output = $transaction->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getRedirectPaymentMethodSpecificOutput();
        if ($output instanceof RedirectPaymentMethodSpecificOutput) {
            return $output;
        }

        $output = $transaction->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getSepaDirectDebitPaymentMethodSpecificOutput();

        return $output;
    }
}
