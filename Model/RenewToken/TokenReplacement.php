<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\RenewToken;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\HostedCheckout\Service\HostedCheckout\GetHostedCheckoutStatusService;
use Worldline\PaymentCore\Api\Service\Token\DeleteTokenServiceInterface;
use Worldline\PaymentCore\Model\ResourceModel\Quote as QuoteResource;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;

class TokenReplacement
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var GetHostedCheckoutStatusService
     */
    private $getRequest;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var DeleteTokenServiceInterface
     */
    private $deleteTokenService;

    public function __construct(
        QuoteResource $quoteResource,
        GetHostedCheckoutStatusService $getRequest,
        LoggerInterface $logger,
        SubscriptionRepositoryInterface $subscriptionRepository,
        DeleteTokenServiceInterface $deleteTokenService
    ) {
        $this->quoteResource = $quoteResource;
        $this->getRequest = $getRequest;
        $this->logger = $logger;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->deleteTokenService = $deleteTokenService;
    }

    public function replace(string $paymentId, string $returnId, string $subscriptionId): void
    {
        if (!$paymentId || !$returnId) {
            throw new LocalizedException(__('Invalid request'));
        }

        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($paymentId);
        $storeId = (int)$quote->getStoreId();

        try {
            $response = $this->getRequest->execute($paymentId, $storeId);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('The payment has failed, please, try again'));
        }

        $wlSubscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        $oldToken = (string)$wlSubscription->getToken();

        $cardPaymentMethodSpecificOutput = $response->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getCardPaymentMethodSpecificOutput();

        $wlSubscription->setToken((string)$cardPaymentMethodSpecificOutput->getToken());
        $wlSubscription->setPaymentProductId((int)$cardPaymentMethodSpecificOutput->getPaymentProductId());
        $this->subscriptionRepository->save($wlSubscription);

        if (!$this->subscriptionRepository->isReusableToken($oldToken)) {
            $this->deleteToken($oldToken, $storeId);
        }
    }

    private function deleteToken(string $token, int $storeId): void
    {
        try {
            $this->deleteTokenService->execute($token, $storeId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
