<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Controller\Adminhtml\Record;

use Uplo\ProductImport\Model\RecordFactory;
use Uplo\ProductImport\Model\ResourceModel\Record as RecordResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private RecordResource $resource,
        private RecordFactory $recordFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        $productValidationResults = $this->_getSession()->getProductUploadResults();
        $categoryValidationResults = $this->_getSession()->getCategoryUploadResults();
    
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data && (!empty($productValidationResults) || !empty($categoryValidationResults))) {
            if (!empty($productValidationResults)) {
                $this->saveCsvRecord('products_csv_file_' . time(), $productValidationResults);
            }

            if (!empty($categoryValidationResults)) {
                $this->saveCsvRecord('categories_csv_file_' . time(), $categoryValidationResults);
            }

            try {
                $this->messageManager->addSuccessMessage(__('Files are successfully uploaded for validation.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $exception) {
                $this->messageManager->addExceptionMessage($exception);
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the record.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    private function saveCsvRecord(string $csvFileName, array $validationResults): void
    {
        $model = $this->recordFactory->create();
        $model->setData([
            'csv_file' => $csvFileName,
            'creation_time' => date('Y-m-d H:i:s'),
            'validation_result' => $validationResults['invalidRowsCount'] > 0 ? 'failed' : 'success',
            'failure_reason' => json_encode($validationResults['failureReasons'] ?? []),
            'error_count' => $validationResults['invalidRowsCount'] ?? 0,
            'valid_rows' => json_encode($validationResults['validRows'] ?? []),
            'invalid_rows' => json_encode($validationResults['invalidRows'] ?? []),
        ]);

        $this->resource->save($model);
    }
}
