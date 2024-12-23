<?php declare(strict_types=1);

namespace Uplo\ProductImport\Controller\Adminhtml\Record;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\File\Csv;
use Uplo\ProductImport\Model\RecordFactory;
use Uplo\ProductImport\Model\ResourceModel\Record as RecordResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;

class FileTempUpload extends Action implements HttpPostActionInterface
{
    private WriteInterface $mediaDirectory;
    private ResourceConnection $resourceConnection;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        private UploaderFactory $uploaderFactory,
        private StoreManagerInterface $storeManager,
        private Csv $csvProcessor,
        private RecordFactory $recordFactory,
        private RecordResource $recordResource,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): ResultInterface
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $validProducts = [];
            $invalidProducts = [];
            $validCategories = [];
            $invalidCategories = [];
            $failureReasons = [];
            $invalidRowsCount = 0;

            $productCsvFile = $this->getRequest()->getFiles('products_csv_file');
            if ($productCsvFile) {
                $fileUploader = $this->uploaderFactory->create(['fileId' => 'products_csv_file']);
                $fileUploader->setAllowedExtensions(['csv']);
                $fileUploader->setAllowRenameFiles(true);
                $fileUploader->setAllowCreateFolders(true);
                $fileUploader->setFilesDispersion(false);

                $csvPath = 'tmp/csvUploader/files';
                $result = $fileUploader->save($this->mediaDirectory->getAbsolutePath($csvPath));
                $filePath = $this->mediaDirectory->getAbsolutePath($csvPath . '/' . $result['file']);
                $data = $this->csvProcessor->getData($filePath);

                if (empty($data) || !isset($data[0])) {
                    throw new LocalizedException(__('Invalid CSV structure.'));
                }

                $header = $data[0];
                unset($data[0]);

                foreach ($data as $rowIndex => $row) {
                    $productData = array_combine($header, $row);
                    $isValid = true;
                    $rowFailures = [];

                    if (empty($productData['sku']) || !ctype_alnum($productData['sku'])) {
                        $isValid = false;
                        $rowFailures[] = 'Invalid SKU format.';
                    }
                    if (empty($productData['url']) || !filter_var($productData['url'], FILTER_VALIDATE_URL)) {
                        $isValid = false;
                        $rowFailures[] = 'Invalid URL format.';
                    }

                    if ($isValid) {
                        $validProducts[] = $productData;
                    } else {
                        $invalidProducts[] = $productData;
                        $failureReasons[] = 'Row ' . ($rowIndex + 1) . ': ' . implode(' ', $rowFailures);
                        $invalidRowsCount++;
                    }
                }

                $this->_getSession()->setProductUploadResults([
                    'validRows' => $validProducts,
                    'invalidRows' => $invalidProducts,
                    'failureReasons' => $failureReasons,
                    'invalidRowsCount' => $invalidRowsCount,
                ]);
                $this->saveProducts($validProducts);
            }

            $categoryCsvFile = $this->getRequest()->getFiles('categories_csv_file');
            if ($categoryCsvFile) {
                $fileUploader = $this->uploaderFactory->create(['fileId' => 'categories_csv_file']);
                $fileUploader->setAllowedExtensions(['csv']);
                $fileUploader->setAllowRenameFiles(true);
                $fileUploader->setAllowCreateFolders(true);
                $fileUploader->setFilesDispersion(false);

                $csvPath = 'tmp/csvUploader/files';
                $result = $fileUploader->save($this->mediaDirectory->getAbsolutePath($csvPath));
                $filePath = $this->mediaDirectory->getAbsolutePath($csvPath . '/' . $result['file']);
                $data = $this->csvProcessor->getData($filePath);

                if (empty($data) || !isset($data[0])) {
                    throw new LocalizedException(__('Invalid CSV structure.'));
                }

                $header = $data[0];
                unset($data[0]);

                foreach ($data as $rowIndex => $row) {
                    $categoryData = array_combine($header, $row);
                    $isValid = true;
                    $rowFailures = [];

                    if (empty($categoryData['category_name'])) {
                        $isValid = false;
                        $rowFailures[] = 'Category name is required.';
                    }

                    if (empty($categoryData['url_key']) || !filter_var($categoryData['url_key'], FILTER_VALIDATE_URL)) {
                        $isValid = false;
                        $rowFailures[] = 'Invalid URL format.';
                    }

                    if ($isValid) {
                        $validCategories[] = $categoryData;
                    } else {
                        $invalidCategories[] = $categoryData;
                        $failureReasons[] = 'Row ' . ($rowIndex + 1) . ': ' . implode(' ', $rowFailures);
                        $invalidRowsCount++;
                    }
                }

                $this->_getSession()->setCategoryUploadResults([
                    'validRows' => $validCategories,
                    'invalidRows' => $invalidCategories,
                    'failureReasons' => $failureReasons,
                    'invalidRowsCount' => $invalidRowsCount,
                ]);
                $this->saveCategories($validCategories);
            }

            return $jsonResult->setData([
                'success' => true,
                'message' => __('Files uploaded and processed successfully.'),
                'validation_result' => $invalidRowsCount > 0 ? 'failed' : 'success',
                'failed_rows' => $invalidRowsCount,
                'failure_reasons' => $failureReasons
            ]);

        } catch (LocalizedException $exception) {
            return $jsonResult->setData(['errorcode' => 0, 'error' => $exception->getMessage()]);
        } catch (\Exception $e) {
            return $jsonResult->setData(['errorcode' => 0, 'error' => __('An error occurred, please try again later.')]);
        }
    }

    private function saveProducts(array $products): void
    {
        $connection = $this->resourceConnection->getConnection();
        $productTable = $this->resourceConnection->getTableName('uplo_productimport_product');

        foreach ($products as $product) {
            try {
                $connection->insert($productTable, [
                    'sku' => $product['sku'] ?? '',
                    'name' => $product['name'] ?? '',
                    'title' => $product['title'] ?? '',
                    'short_description' => $product['short_description'] ?? '',
                    'long_description' => $product['long_description'] ?? '',
                    'url_key' => $product['url_key'] ?? '',
                    'creation_time' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                $this->_logger->error('Error saving product: ' . json_encode($product) . '. Error: ' . $e->getMessage());
                $this->messageManager->addErrorMessage(__('Failed to save product: %1', $product['sku']));
            }
        }
    }

    private function saveCategories(array $categories): void
    {
        $connection = $this->resourceConnection->getConnection();
        $categoryTable = $this->resourceConnection->getTableName('uplo_productimport_category');

        foreach ($categories as $category) {
            try {
                $connection->insert($categoryTable, [
                    'category_name' => $category['category_name'] ?? '',
                    'url_key' => $category['url_key'] ?? '',
                    'creation_time' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Failed to save category: %1', $category['category_name']));
            }
        }
    }
}
