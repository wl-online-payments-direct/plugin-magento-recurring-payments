<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Cancel;

use Amasty\RecurringPayments\Api\Subscription\CancelProcessorInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Psr\Log\LoggerInterface;
use Worldline\PaymentCore\Api\Service\Token\DeleteTokenServiceInterface;
use Worldline\RecurringPayments\Api\SubscriptionRepositoryInterface;

class CancelSubscriptionHandler implements CancelProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeleteTokenServiceInterface
     */
    private $deleteTokenService;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $wlSubscriptionRepository;

    public function __construct(
        LoggerInterface $logger,
        DeleteTokenServiceInterface $deleteTokenService,
        SubscriptionRepositoryInterface $wlSubscriptionRepository
    ) {
        $this->logger = $logger;
        $this->deleteTokenService = $deleteTokenService;
        $this->wlSubscriptionRepository = $wlSubscriptionRepository;
    }

    public function process(SubscriptionInterface $subscription): void
    {
        try {
            $subscriptionId = $subscription->getSubscriptionId();
            $wlSubscription = $this->wlSubscriptionRepository->getBySubscriptionId($subscriptionId);

            $this->deleteTokenService->execute($wlSubscription->getToken(), (int)$subscription->getStoreId());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
