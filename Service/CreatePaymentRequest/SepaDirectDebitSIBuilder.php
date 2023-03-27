<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\CreatePaymentRequest;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBase;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBaseFactory;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentProduct771SpecificInputBase;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentProduct771SpecificInputBaseFactory;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\RecurringPayments\Model\Processor\Transaction\TransactionGeneratorManager;

class SepaDirectDebitSIBuilder
{
    /**
     * @var SepaDirectDebitPaymentMethodSpecificInputBaseFactory
     */
    private $debitPaymentMethodSpecificInputBaseFactory;

    /**
     * @var SepaDirectDebitPaymentProduct771SpecificInputBaseFactory
     */
    private $debitPaymentProduct771SpecificInputBaseFactory;

    public function __construct(
        SepaDirectDebitPaymentMethodSpecificInputBaseFactory $debitPaymentMethodSpecificInputBaseFactory,
        SepaDirectDebitPaymentProduct771SpecificInputBaseFactory $debitPaymentProduct771SpecificInputBaseFactory
    ) {
        $this->debitPaymentMethodSpecificInputBaseFactory = $debitPaymentMethodSpecificInputBaseFactory;
        $this->debitPaymentProduct771SpecificInputBaseFactory = $debitPaymentProduct771SpecificInputBaseFactory;
    }

    public function build(CartInterface $quote): SepaDirectDebitPaymentMethodSpecificInputBase
    {
        $publicToken = $quote->getPayment()->getAdditionalInformation(PaymentDataBuilder::TOKEN_ID);
        $payProductId = $quote->getPayment()->getAdditionalInformation(TransactionGeneratorManager::PAYMENT_PRODUCT_ID);

        /** @var SepaDirectDebitPaymentProduct771SpecificInputBase $paymentProduct771 */
        $paymentProduct771 = $this->debitPaymentProduct771SpecificInputBaseFactory->create();
        $paymentProduct771->setExistingUniqueMandateReference($publicToken);

        /** @var SepaDirectDebitPaymentMethodSpecificInputBase $debitPaymentMethodSpecificInput */
        $debitPaymentMethodSpecificInput = $this->debitPaymentMethodSpecificInputBaseFactory->create();
        $debitPaymentMethodSpecificInput->setPaymentProductId($payProductId);
        $debitPaymentMethodSpecificInput->setPaymentProduct771SpecificInput($paymentProduct771);

        return $debitPaymentMethodSpecificInput;
    }
}
