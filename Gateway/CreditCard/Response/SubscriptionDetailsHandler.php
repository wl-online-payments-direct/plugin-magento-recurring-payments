<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Gateway\CreditCard\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
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
