<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Gateway\HostedCheckout\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificOutput;
use Worldline\PaymentCore\Gateway\SubjectReader;
use Worldline\RecurringPayments\Model\SubscriptionEntity\SubscriptionManager;

class SubscriptionDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    public function __construct(
        SubjectReader $subjectReader,
        SubscriptionManager $subscriptionManager
    ) {
        $this->subjectReader = $subjectReader;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $orderIncrementId = $paymentDO->getOrder()->getOrderIncrementId();
        $transaction = $this->subjectReader->readTransaction($response);
        $paymentOutput = $this->getOutput($transaction);

        if (!$paymentOutput) {
            return;
        }

        $token = $paymentOutput->getToken();
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

        return $output;
    }
}
