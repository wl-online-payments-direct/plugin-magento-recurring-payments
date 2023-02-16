<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model;

use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Save quote to context for use custom quote
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteContext
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartInterface|null
     */
    private $quote;

    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function getQuote(): CartInterface
    {
        if ($this->quote) {
            return $this->quote;
        }

        return $this->checkoutSession->getQuote();
    }

    public function setQuote(CartInterface $quote): void
    {
        $this->quote = $quote;
    }
}
