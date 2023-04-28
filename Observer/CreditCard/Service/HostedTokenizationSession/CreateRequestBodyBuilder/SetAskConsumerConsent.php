<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Observer\CreditCard\Service\HostedTokenizationSession\CreateRequestBodyBuilder;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use Worldline\CreditCard\Service\HostedTokenization\CreateRequestBodyBuilder;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SetAskConsumerConsent implements ObserverInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(Session $checkoutSession, QuoteValidate $quoteValidate)
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @see \Worldline\CreditCard\Service\HostedTokenization\CreateRequestBodyBuilder::build()
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        /** @var CreateHostedTokenizationRequest $createHostedTokenizationRequest */
        $createHostedTokenizationRequest = $observer->getData(
            CreateRequestBodyBuilder::CREATE_HOSTED_TOKENIZATION_REQUEST
        );

        if (!$createHostedTokenizationRequest) {
            return;
        }

        if (!$this->quoteValidate->validateQuote($this->checkoutSession->getQuote())) {
            return;
        }

        $createHostedTokenizationRequest->setAskConsumerConsent(false);
    }
}
