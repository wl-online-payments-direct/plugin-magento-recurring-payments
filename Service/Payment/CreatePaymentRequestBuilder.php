<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\Payment;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequestFactory;
use Worldline\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Worldline\RecurringPayments\Model\Processor\Transaction\TransactionGeneratorManager;
use Worldline\RecurringPayments\Service\CreatePaymentRequest\CardPaymentMethodSpecificInputDataBuilder;
use Worldline\RecurringPayments\Service\CreatePaymentRequest\OrderDataBuilder;
use Worldline\RecurringPayments\Service\CreatePaymentRequest\RedirectPaymentMethodSpecificInputDataBuilder;
use Worldline\RecurringPayments\Service\CreatePaymentRequest\SepaDirectDebitSIBuilder;

class CreatePaymentRequestBuilder
{
    /**
     * @var OrderDataBuilder
     */
    private $orderDataBuilder;

    /**
     * @var CreatePaymentRequestFactory
     */
    private $createPaymentRequestFactory;

    /**
     * @var CardPaymentMethodSpecificInputDataBuilder
     */
    private $cardPaymentMethodSpecificInputDataBuilder;

    /**
     * @var RedirectPaymentMethodSpecificInputDataBuilder
     */
    private $redirectPaymentMethodSpecificInputDataBuilder;

    /**
     * @var SepaDirectDebitSIBuilder
     */
    private $sepaDirectDebitSIBuilder;

    public function __construct(
        OrderDataBuilder $orderDataBuilder,
        CreatePaymentRequestFactory $createPaymentRequestFactory,
        CardPaymentMethodSpecificInputDataBuilder $cardPaymentMethodSpecificInputDataBuilder,
        RedirectPaymentMethodSpecificInputDataBuilder $redirectPaymentMethodSpecificInputDataBuilder,
        SepaDirectDebitSIBuilder $sepaDirectDebitSIBuilder
    ) {
        $this->orderDataBuilder = $orderDataBuilder;
        $this->createPaymentRequestFactory = $createPaymentRequestFactory;
        $this->cardPaymentMethodSpecificInputDataBuilder = $cardPaymentMethodSpecificInputDataBuilder;
        $this->redirectPaymentMethodSpecificInputDataBuilder = $redirectPaymentMethodSpecificInputDataBuilder;
        $this->sepaDirectDebitSIBuilder = $sepaDirectDebitSIBuilder;
    }

    public function build(CartInterface $quote): CreatePaymentRequest
    {
        $createPaymentRequest = $this->createPaymentRequestFactory->create();
        $createPaymentRequest->setOrder($this->orderDataBuilder->build($quote));
        $createPaymentRequest->setCardPaymentMethodSpecificInput(
            $this->cardPaymentMethodSpecificInputDataBuilder->build($quote)
        );

        $payProductId = $quote->getPayment()->getAdditionalInformation(TransactionGeneratorManager::PAYMENT_PRODUCT_ID);

        if ($payProductId === PaymentProductsDetailsInterface::APPLE_PAY_PRODUCT_ID) {
            $createPaymentRequest->setRedirectPaymentMethodSpecificInput(
                $this->redirectPaymentMethodSpecificInputDataBuilder->build($quote)
            );
        }

        if ($payProductId === PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID) {
            $createPaymentRequest->setSepaDirectDebitPaymentMethodSpecificInput(
                $this->sepaDirectDebitSIBuilder->build($quote)
            );
        }

        return $createPaymentRequest;
    }
}
