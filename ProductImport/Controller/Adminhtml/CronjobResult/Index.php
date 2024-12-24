<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Controller\Adminhtml\CronjobResult;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Execute method to render the cronjobresult grid page
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Uplo_ProductImport::cronjobresult');  // Use appropriate ACL
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Cronjob Results'));  // Page title

        return $resultPage;
    }
}
