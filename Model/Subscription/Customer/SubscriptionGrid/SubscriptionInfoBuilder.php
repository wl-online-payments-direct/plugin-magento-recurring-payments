<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model\Subscription\Customer\SubscriptionGrid;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterfaceFactory;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Worldline\RecurringPayments\Model\Subscription\Customer\SubscriptionDataContainer;

class SubscriptionInfoBuilder
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var SubscriptionInfoInterfaceFactory
     */
    private $subscriptionInfoFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var SubscriptionInfoFormatter
     */
    private $subscriptionInfoFormatter;

    /**
     * @var SubscriptionInfoStatusDataAssigner
     */
    private $subscriptionInfoStatusDataAssigner;

    public function __construct(
        UrlInterface $urlBuilder,
        SubscriptionInfoInterfaceFactory $subscriptionInfoFactory,
        AddressRepositoryInterface $addressRepository,
        SubscriptionInfoFormatter $subscriptionInfoFormatter,
        SubscriptionInfoStatusDataAssigner $subscriptionInfoStatusDataAssigner
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->subscriptionInfoFactory = $subscriptionInfoFactory;
        $this->addressRepository = $addressRepository;
        $this->subscriptionInfoFormatter = $subscriptionInfoFormatter;
        $this->subscriptionInfoStatusDataAssigner = $subscriptionInfoStatusDataAssigner;
    }

    public function build(
        SubscriptionDataContainer $subscriptionDataContainer
    ): SubscriptionInfoInterface {
        $subscription = $subscriptionDataContainer->getSubscription();

        /** @var SubscriptionInfoInterface $subscriptionInfo */
        $subscriptionInfo = $this->subscriptionInfoFactory->create();
        $subscriptionInfo->setSubscription($subscription);
        $subscriptionInfo->setStartDate(
            $this->subscriptionInfoFormatter->formatSubscriptionDate(strtotime($subscription->getStartDate()))
        );

        if ($address = $this->getAddress($subscription)) {
            $subscriptionInfo->setAddress($address);
        }

        $order = $subscriptionDataContainer->getOrder();
        $currency = $order ? $order->getOrderCurrencyCode() : null;
        $this->subscriptionInfoStatusDataAssigner->addData($subscription, $subscriptionInfo, $currency);

        $this->setOrderData($subscriptionInfo, $subscriptionDataContainer);
        $this->setLastTransactionData($subscriptionInfo, $subscription, $subscriptionDataContainer);
        if ($product = $subscriptionDataContainer->getProduct()) {
            $subscriptionInfo->setSubscriptionName($product->getName());
        }

        return $subscriptionInfo;
    }

    private function getAddress(SubscriptionInterface $subscription): ?AddressInterface
    {
        if (!$addressId = $subscription->getAddressId()) {
            return null;
        }

        try {
            $address = $this->addressRepository->getById($addressId);
            $this->subscriptionInfoFormatter->addStreetToAddress($address);
            $this->subscriptionInfoFormatter->addCountryToAddress($address);
            return $address;
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }

    private function setOrderData(
        SubscriptionInfoInterface $subscriptionInfo,
        SubscriptionDataContainer $subscriptionDataContainer
    ): void {
        if (!$order = $subscriptionDataContainer->getOrder()) {
            return;
        }

        $subscriptionInfo->setOrderIncrementId($order->getIncrementId());
        $subscriptionInfo->setOrderLink(
            $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
        );
    }

    private function setLastTransactionData(
        SubscriptionInfoInterface $subscriptionInfo,
        SubscriptionInterface $subscription,
        SubscriptionDataContainer $subscriptionDataContainer
    ): void {
        $lastTransaction = $subscriptionDataContainer->getLastTransaction();
        if (!$lastTransaction || !$subscription->getLastPaymentDate()) {
            return;
        }

        $subscriptionInfo->setLastBilling(
            $this->subscriptionInfoFormatter->formatSubscriptionDate(
                strtotime($subscription->getLastPaymentDate())
            )
        );
        $subscriptionInfo->setLastBillingAmount(
            $this->subscriptionInfoFormatter->formatSubscriptionPrice(
                (float)$lastTransaction->getBillingAmount(),
                $lastTransaction->getBillingCurrencyCode()
            )
        );
    }
}
