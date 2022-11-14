<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use Worldline\CreditCard\Gateway\Request\PaymentDataBuilder;

class CardPaymentMethodSpecificInputDataBuilder
{
    public const AUTHORIZATION_MODE_SALE = 'SALE';

    /**
     * @var CardPaymentMethodSpecificInputFactory
     */
    private $cardPaymentMethodSpecificInputFactory;

    public function __construct(
        CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory
    ) {
        $this->cardPaymentMethodSpecificInputFactory = $cardPaymentMethodSpecificInputFactory;
    }

    public function build(CartInterface $quote): CardPaymentMethodSpecificInput
    {
        $publicToken = $quote->getPayment()->getAdditionalInformation(PaymentDataBuilder::TOKEN_ID);
        /** @var CardPaymentMethodSpecificInput $cardPaymentMethodSpecificInput */
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();

        $cardPaymentMethodSpecificInput->setToken($publicToken);
        $cardPaymentMethodSpecificInput->setIsRecurring(true);
        $cardPaymentMethodSpecificInput->setTransactionChannel('ECOMMERCE');
        $cardPaymentMethodSpecificInput->setAuthorizationMode(self::AUTHORIZATION_MODE_SALE);

        return $cardPaymentMethodSpecificInput;
    }
}
