<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\Record;

use Uplo\ProductImport\Model\Record;
use Uplo\ProductImport\Model\RecordFactory;
use Uplo\ProductImport\Model\ResourceModel\Record as RecordResource;
use Uplo\ProductImport\Model\ResourceModel\Record\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var array
     */
    private array $loadedData;

    /**
     * @var ReadInterface 
     */
    private ReadInterface $mediaDirectory;

    /**
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RecordResource $resource
     * @param RecordFactory $recordFactory
     * @param RequestInterface $request
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Mime $mime
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private RecordResource $resource,
        private RecordFactory $recordFactory,
        private RequestInterface $request,
        Filesystem $filesystem,
        private StoreManagerInterface $storeManager,
        private Mime $mime,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $collectionFactory->create();
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
    
        $record = $this->getCurrentRecord();
        $recordData = $record->getData();
    
        if (isset($recordData['csv_file']) && $recordData['csv_file']) {
            $file = $recordData['csv_file'];
            
            $csvDir = 'tmp/fileUploader/files';
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    
            $fullFilePath = $this->mediaDirectory->getAbsolutePath($csvDir) . '/' . $file;
            $fileUrl = $baseUrl . $csvDir . '/' . $file;
            $stat = $this->mediaDirectory->stat($fullFilePath);
            
            $recordData['csv_file'] = [];
            $recordData['csv_file'][0]['url'] = $fileUrl;
            $recordData['csv_file'][0]['name'] = $file;
            $recordData['csv_file'][0]['size'] = $stat['size'];
            $recordData['csv_file'][0]['type'] = $this->mime->getMimeType($fullFilePath);
        } else {
            $recordData['csv_file'] = null;
        }
    
        $this->loadedData[$record->getId()] = $recordData;
    
        return $this->loadedData;
    }
    

    /**
     * @return Record
     */
    private function getCurrentRecord(): Record
    {
        $recordId = $this->getRecordId();
        $record = $this->recordFactory->create();
        if (!$recordId) {
            return $record;
        }

        $this->resource->load($record, $recordId);

        return $record;
    }

    /**
     * @return int
     */
    private function getRecordId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
    }
}
