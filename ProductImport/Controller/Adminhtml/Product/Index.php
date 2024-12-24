<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Execute method to render the product grid page
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Uplo_ProductImport::product');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Products'));  

        return $resultPage;
    }
}
