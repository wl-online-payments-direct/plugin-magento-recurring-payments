<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\CreatePaymentRequest;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificInputFactory;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;

class RedirectPaymentMethodSpecificInputDataBuilder
{
    /**
     * @var RedirectPaymentMethodSpecificInputFactory
     */
    private $redirectPaymentMethodSpecificInputFactory;

    public function __construct(
        RedirectPaymentMethodSpecificInputFactory $redirectPaymentMethodSpecificInputFactory
    ) {
        $this->redirectPaymentMethodSpecificInputFactory = $redirectPaymentMethodSpecificInputFactory;
    }

    public function build(CartInterface $quote): RedirectPaymentMethodSpecificInput
    {
        $publicToken = $quote->getPayment()->getAdditionalInformation(PaymentDataBuilder::TOKEN_ID);

        /** @var RedirectPaymentMethodSpecificInput $redirectPaymentMethodSpecificInput */
        $redirectPaymentMethodSpecificInput = $this->redirectPaymentMethodSpecificInputFactory->create();
        $redirectPaymentMethodSpecificInput->setToken($publicToken);
        $redirectPaymentMethodSpecificInput->setRequiresApproval(false);

        return $redirectPaymentMethodSpecificInput;
    }
}
