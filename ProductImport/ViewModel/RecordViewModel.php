<?php

declare(strict_types=1);

namespace Uplo\ProductImport\ViewModel;

use Uplo\ProductImport\Model\Record;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class RecordViewModel implements ArgumentInterface
{
    public function __construct(
        private UrlInterface $url,
        private StoreManagerInterface $storeManager
    ) {}
    
    public function getFeaturedFileUrl(Record $record): string
    {
        $fileName = $record->getData('csv_file');

        $csvPath = 'tmp/fileUploader/files';
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . $csvPath . '/' . $fileName;
    }
}
