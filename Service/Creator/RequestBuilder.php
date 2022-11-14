<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\Creator;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequestFactory;
use Worldline\PaymentCore\Api\Data\PaymentProductsInterface;
use Worldline\RecurringPayments\Model\Processor\Transaction\TransactionGeneratorPart;
use Worldline\RecurringPayments\Service\Creator\Request\CardPaymentMethodSpecificInputDataBuilder;
use Worldline\RecurringPayments\Service\Creator\Request\OrderDataBuilder;
use Worldline\RecurringPayments\Service\Creator\Request\RedirectPaymentMethodSpecificInputDataBuilder;

class RequestBuilder
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

    public function __construct(
        OrderDataBuilder $orderDataBuilder,
        CreatePaymentRequestFactory $createPaymentRequestFactory,
        CardPaymentMethodSpecificInputDataBuilder $cardPaymentMethodSpecificInputDataBuilder,
        RedirectPaymentMethodSpecificInputDataBuilder $redirectPaymentMethodSpecificInputDataBuilder
    ) {
        $this->orderDataBuilder = $orderDataBuilder;
        $this->createPaymentRequestFactory = $createPaymentRequestFactory;
        $this->cardPaymentMethodSpecificInputDataBuilder = $cardPaymentMethodSpecificInputDataBuilder;
        $this->redirectPaymentMethodSpecificInputDataBuilder = $redirectPaymentMethodSpecificInputDataBuilder;
    }

    public function build(CartInterface $quote): CreatePaymentRequest
    {
        $createPaymentRequest = $this->createPaymentRequestFactory->create();
        $createPaymentRequest->setOrder($this->orderDataBuilder->build($quote));
        $createPaymentRequest->setCardPaymentMethodSpecificInput(
            $this->cardPaymentMethodSpecificInputDataBuilder->build($quote)
        );

        $payProductId = $quote->getPayment()->getAdditionalInformation(TransactionGeneratorPart::PAYMENT_PRODUCT_ID);

        if ($payProductId === PaymentProductsInterface::APPLE_PAY_KEY) {
            $createPaymentRequest->setRedirectPaymentMethodSpecificInput(
                $this->redirectPaymentMethodSpecificInputDataBuilder->build($quote)
            );
        }

        return $createPaymentRequest;
    }
}
