<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Service;

use Uplo\ProductImport\Model\ResourceModel\Record\Collection;
use Uplo\ProductImport\Model\ResourceModel\Record\CollectionFactory;
use Magento\Framework\DB\Select;

class RecordsProvider
{
    public function __construct(private CollectionFactory $collectionFactory)
    {}

    public function getRecords(int $limit, int $currentPage): Collection
    {
        $collection = $this->getCollection();
        $collection->setOrder('creation_time', Select::SQL_DESC);
        $collection->setPageSize($limit);
        $collection->setCurPage($currentPage);

        return $collection;
    }

    private function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }
}
