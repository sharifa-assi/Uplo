<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\CronjobResult;

use Uplo\ProductImport\Model\ResourceModel\CronjobResult\CollectionFactory;
use Uplo\ProductImport\Model\ResourceModel\CronjobResult;
use Uplo\ProductImport\Model\CronjobResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Framework\App\Filesystem\DirectoryList;

class CronjobResultDataProvider extends ModifierPoolDataProvider
{
    private array $loadedData;
    private Filesystem\Directory\ReadInterface $mediaDirectory;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private CronjobResultResource $resource,
        private CronjobResultFactory $cronjobResultFactory,
        private RequestInterface $request,
        Filesystem $filesystem,
        private StoreManagerInterface $storeManager,
        private \Magento\Framework\Filesystem\Driver\File\Mime $mime,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $collectionFactory->create();
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Get all cronjobresults for the grid
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $cronjobResultData = [];
        $cronjobresults = $this->collection->getItems(); 

        foreach ($cronjobresults as $cronjobResult) {
            $cronjobResultData[] = $cronjobResult->getData();
        }

        $this->loadedData = $cronjobResultData;

        return $this->loadedData;
    }
}
