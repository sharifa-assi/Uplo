<?php declare(strict_types=1);

namespace Uplo\ProductImport\Controller\Record;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class View implements HttpGetActionInterface
{
    public function __construct(
        private PageFactory $pageFactory
    ) {
    }

    public function execute(): ResultInterface
    {
        return $this->pageFactory->create();
    }
}