<?php
declare(strict_types=1);

namespace Worldline\RecurringPayments\Controller\Payment;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Worldline\RecurringPayments\Model\RenewToken\RedirectManager;

class RenewToken extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RedirectManager
     */
    private $renewTokenRedirectManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        RedirectManager $renewTokenRedirectManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->renewTokenRedirectManager = $renewTokenRedirectManager;
    }

    public function execute(): ResultInterface
    {
        $subscriptionId = (string)$this->getRequest()->getParam(ScheduleInterface::SUBSCRIPTION_ID);

        if (!$subscriptionId || !$this->customerSession->authenticate()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setHeader('Login-Required', 'true');
            return $resultPage;
        }

        $renewUrl = $this->renewTokenRedirectManager->getRenewTokenUrl($subscriptionId);

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($renewUrl);
    }
}
