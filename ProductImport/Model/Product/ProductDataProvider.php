<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\Product;

use Uplo\ProductImport\Model\Product;
use Uplo\ProductImport\Model\ProductFactory;
use Uplo\ProductImport\Model\ResourceModel\Product as ProductResource;
use Uplo\ProductImport\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class ProductDataProvider extends ModifierPoolDataProvider
{
    private array $loadedData;

    private ReadInterface $mediaDirectory;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private ProductResource $resource,
        private ProductFactory $productFactory,
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

    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $product = $this->getCurrentProduct();
        $productData = $product->getData();

        $this->loadedData[$product->getId()] = $productData;

        return $this->loadedData;
    }

    private function getCurrentProduct(): Product
    {
        $productId = $this->getProductId();
        $product = $this->productFactory->create();
        if (!$productId) {
            return $product;
        }

        $this->resource->load($product, $productId);

        return $product;
    }

    private function getProductId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
    }
}
