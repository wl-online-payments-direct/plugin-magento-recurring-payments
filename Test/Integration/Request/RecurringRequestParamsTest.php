<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Test\Integration\Request;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Worldline\CreditCard\Service\Payment\CreatePaymentRequestBuilder;
use Worldline\CreditCard\Ui\ConfigProvider as CCConfigProvider;
use Worldline\HostedCheckout\Service\HostedCheckout\CreateHostedCheckoutRequestBuilder;
use Worldline\HostedCheckout\Ui\ConfigProvider as HCConfigProvider;
use Worldline\RedirectPayment\Service\HostedCheckout\CreateHostedCheckoutRequestBuilder as RPCreateRequestBuilder;
use Worldline\RedirectPayment\Ui\ConfigProvider as PRConfigProvider;

/**
 * Test case for recurring request params
 */
class RecurringRequestParamsTest extends TestCase
{
    private const SUBSCRIBE_KEY = 'subscribe';

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var CreatePaymentRequestBuilder
     */
    private $createRequestBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CreateHostedCheckoutRequestBuilder
     */
    private $createHostedCheckoutRequestBuilder;

    /**
     * @var RPCreateRequestBuilder
     */
    private $rpCreateRequestBuilder;

    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->createRequestBuilder = $objectManager->get(CreatePaymentRequestBuilder::class);
        $this->createHostedCheckoutRequestBuilder = $objectManager->get(CreateHostedCheckoutRequestBuilder::class);
        $this->rpCreateRequestBuilder = $objectManager->get(RPCreateRequestBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoConfigFixture current_store currency/options/allow EUR
     * @magentoConfigFixture current_store currency/options/base EUR
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store payment/worldline_cc/active 1
     * @magentoConfigFixture current_store payment/worldline_cc/cart_lines 0
     * @magentoConfigFixture current_store payment/worldline_cc/payment_action authorize_capture
     * @magentoDbIsolation disabled
     */
    public function testCCRequestParams(): void
    {
        $quote = $this->getQuote();
        $quote->getPayment()->setMethod(CCConfigProvider::CODE);

        $this->addRecurringProduct($quote);

        $request = $this->createRequestBuilder->build($quote);

        $this->assertFalse(
            $request->getCardPaymentMethodSpecificInput()->getSkipAuthentication()
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoConfigFixture current_store currency/options/allow EUR
     * @magentoConfigFixture current_store currency/options/base EUR
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store payment/worldline_hosted_checkout/active 1
     * @magentoConfigFixture current_store payment/worldline_hosted_checkout/cart_lines 0
     * @magentoConfigFixture current_store payment/worldline_hosted_checkout/payment_action authorize_capture
     * @magentoDbIsolation disabled
     */
    public function testHCRequestParams(): void
    {
        $quote = $this->getQuote();
        $quote->getPayment()->setMethod(HCConfigProvider::HC_CODE);

        $this->addRecurringProduct($quote);

        $request = $this->createHostedCheckoutRequestBuilder->build($quote);

        $this->assertFalse(
            $request->getCardPaymentMethodSpecificInput()->getSkipAuthentication()
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoConfigFixture current_store currency/options/allow EUR
     * @magentoConfigFixture current_store currency/options/base EUR
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store payment/worldline_redirect_payment/active 1
     * @magentoConfigFixture current_store payment/worldline_redirect_payment/cart_lines 0
     * @magentoConfigFixture current_store payment/worldline_redirect_payment/payment_action authorize_capture
     * @magentoDbIsolation disabled
     */
    public function testRPRequestParams(): void
    {
        $quote = $this->getQuote();
        $quote->getPayment()->setMethod(PRConfigProvider::CODE . '_1');

        $this->addRecurringProduct($quote);

        $request = $this->rpCreateRequestBuilder->build($quote);

        $this->assertFalse(
            $request->getCardPaymentMethodSpecificInput()->getSkipAuthentication()
        );

        $this->assertFalse(
            $request->getRedirectPaymentMethodSpecificInput()->getRequiresApproval()
        );
    }

    private function getQuote(): CartInterface
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->setOrder(CartInterface::KEY_ENTITY_ID);
        $quoteCollection->getSelect()->limit(1);
        return $quoteCollection->getLastItem();
    }

    private function addRecurringProduct(CartInterface $quote): void
    {
        $product = $this->productRepository->get('simple');
        $request = new DataObject(
            [
                self::SUBSCRIBE_KEY => self::SUBSCRIBE_KEY,
                ProductRecurringAttributesInterface::SUBSCRIPTION_PLAN_ID => 1
            ]
        );

        $quote->addProduct($product, $request);
    }
}
