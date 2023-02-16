<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Controller\Returns;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Worldline\RecurringPayments\Model\RenewToken\TokenReplacement;

class ReturnUrl extends Action implements HttpGetActionInterface
{
    /**
     * @var TokenReplacement
     */
    private $tokenReplacement;

    public function __construct(Context $context, TokenReplacement $tokenReplacement)
    {
        parent::__construct($context);
        $this->tokenReplacement = $tokenReplacement;
    }

    public function execute(): Page
    {
        $returnId = (string) $this->getRequest()->getParam('RETURNMAC');
        $subscriptionId = (string) $this->getRequest()->getParam('subscription_id');
        $hostedCheckoutId = (string) $this->getRequest()->getParam('hostedCheckoutId');

        $this->tokenReplacement->replace($hostedCheckoutId, $returnId, $subscriptionId);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Subscription has been successfully renewed'));

        return $resultPage;
    }
}
