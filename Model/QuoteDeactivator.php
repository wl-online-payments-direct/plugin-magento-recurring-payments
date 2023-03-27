<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Model;

use Magento\Quote\Api\CartRepositoryInterface;

class QuoteDeactivator
{
    /**
     * @var QuoteContext
     */
    private $quoteContext;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(QuoteContext $quoteContext, CartRepositoryInterface $cartRepository)
    {
        $this->quoteContext = $quoteContext;
        $this->cartRepository = $cartRepository;
    }

    public function deactivateQuote(): void
    {
        $quote = $this->quoteContext->getQuote();
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);
    }
}
