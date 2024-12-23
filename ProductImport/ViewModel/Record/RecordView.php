<?php declare(strict_types=1);

namespace Uplo\ProductImport\ViewModel\Record;

use Uplo\ProductImport\Model\Record;
use Uplo\ProductImport\Model\ResourceModel\Record\Collection;
use Uplo\ProductImport\Model\ResourceModel\Record\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class RecordView implements ArgumentInterface
{
    public function __construct(
        private RequestInterface $request,
        private CollectionFactory $collectionFactory,
        private StoreManagerInterface $storeManager
    ) {
    }

    public function getRecord(): Record
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('record_id', (int)$this->request->getParam('record_id'));

        return $collection->getFirstItem();
    }

    public function getFeaturedFileUrl(Record $record): string
    {
        $fileName = $record->getData('csv_file');

        $csvPath = 'tmp/fileUploader/files';
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . $csvPath . '/' . $fileName;
    }
}
