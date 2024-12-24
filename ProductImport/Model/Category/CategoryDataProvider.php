<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\Category;

use Uplo\ProductImport\Model\ResourceModel\Category\CollectionFactory;
use Uplo\ProductImport\Model\ResourceModel\Category;
use Uplo\ProductImport\Model\CategoryFactory;
use Uplo\ProductImport\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Magento\Framework\App\Filesystem\DirectoryList;

class CategoryDataProvider extends ModifierPoolDataProvider
{
    private array $loadedData;
    private Filesystem\Directory\ReadInterface $mediaDirectory;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private CategoryResource $resource,
        private CategoryFactory $categoryFactory,
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
     * Get all categories for the grid
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $categoryData = [];
        $categories = $this->collection->getItems();  // Get all categories

        foreach ($categories as $category) {
            $categoryData[] = $category->getData();  // Add category data to the array
        }

        $this->loadedData = $categoryData;

        return $this->loadedData;
    }
}
