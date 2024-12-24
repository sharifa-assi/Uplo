<?php

namespace Uplo\ProductImport\Cron;

use Psr\Log\LoggerInterface;
use Uplo\ProductImport\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Uplo\ProductImport\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;

class ProcessCsv
{
    protected $logger;
    protected $categoryCollectionFactory;
    protected $productCollectionFactory;
    protected $categoryFactory;
    protected $productFactory;
    protected $resource;

    public function __construct(
        LoggerInterface $logger,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->resource = $resource;
    }

    public function execute()
    {
        try {
            $this->logger->info('Starting CsvCronjob execution...');
            $this->processCategories();
            $this->processProducts();
            $this->logger->info('Cron job executed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Error executing cron job: ' . $e->getMessage());
        }
    }

    protected function processCategories()
    {
        $categoryCollection = $this->categoryCollectionFactory->create(); 
        
        if ($categoryCollection->getSize() === 0) {
            $this->logger->info('No categories to process.');
            return;
        }
    
        foreach ($categoryCollection as $categoryData) {
            try {
                $category = $this->categoryFactory->create()
                    ->loadByAttribute('name', $categoryData->getCategoryName());
    
                if ($category) {
                    $category->setData('url_key', $categoryData->getUrlKey());
                } else {
                    $category = $this->categoryFactory->create();
                    $category->setName($categoryData->getCategoryName())
                        ->setUrlKey($categoryData->getUrlKey())
                        ->setCreatedAt($categoryData->getCreationTime()); 
                }
    
                $category->save();
                $this->logOperation('category', $categoryData->getCategoryId(), 'success');
                $this->logger->info('Category processed: ' . $categoryData->getCategoryName());
            } catch (\Exception $e) {
                $this->logOperation('category', $categoryData->getCategoryId(), 'failure', $e->getMessage());
                $this->logger->error('Error processing category: ' . $e->getMessage());
            }
        }
    }
    
    protected function processProducts()
    {
        $productCollection = $this->productCollectionFactory->create();
    
        if ($productCollection->getSize() === 0) {
            $this->logger->info('No products to process.');
            return;
        }
    
        foreach ($productCollection as $productData) {
            try {
                $product = $this->productFactory->create()
                    ->loadByAttribute('sku', $productData->getSku());
    
                if ($product) {
                    $product->setName($productData->getName())
                        ->setTitle($productData->getTitle())
                        ->setShortDescription($productData->getShortDescription())
                        ->setLongDescription($productData->getLongDescription())
                        ->setUrlKey($productData->getUrlKey());
                } else {
                    $product = $this->productFactory->create();
                    $product->setSku($productData->getSku())
                        ->setName($productData->getName())
                        ->setTitle($productData->getTitle())
                        ->setShortDescription($productData->getShortDescription())
                        ->setLongDescription($productData->getLongDescription())
                        ->setUrlKey($productData->getUrlKey())
                        ->setTypeId('simple')
                        ->setAttributeSetId(4)
                        ->setStatus(1)
                        ->setVisibility(4);
                }
                $product->save();
                $this->logOperation('product', $productData->getProductId(), 'success');
                $this->logger->info('Product processed: ' . $productData->getSku());
            } catch (\Exception $e) {
                $this->logOperation('product', $productData->getProductId(), 'failure', $e->getMessage());
                $this->logger->error('Error processing product (SKU: ' . $productData->getSku() . '): ' . $e->getMessage());
            }
        }
    }

    protected function logOperation($type, $entityId, $status, $message = null)
    {
        try {
            $connection = $this->resource->getConnection();
    
            $entityName = null;
            if ($type === 'category') {
                $category = $this->categoryFactory->create()->load($entityId);
                if ($category) {
                    $entityName = $category->getName();
                }
            } elseif ($type === 'product') {
                $product = $this->productFactory->create()->load($entityId);
                if ($product) {
                    $entityName = $product->getName();
                }
            }
    
            $select = $connection->select()
                ->from('uplo_productimport_cronjobresult')
                ->where('type = ?', $type)
                ->where('entity_id = ?', $entityId)
                ->where('created_at > ?', (new \DateTime())->modify('-5 minutes')->format('Y-m-d H:i:s'));
    
            $existingLog = $connection->fetchRow($select);
    
            if ($existingLog) {
                $this->logger->info("Skipping log entry for {$type} with ID {$entityId}, already processed in the last 5 minutes.");
                return;
            }
    
            $connection->insert('uplo_productimport_cronjobresult', [
                'type' => $type,
                'entity_id' => $entityId,
                'entity_name' => $entityName,
                'status' => $status,
                'message' => $message,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error logging operation: ' . $e->getMessage());
        }
    }
}
