<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\ResourceModel\Record;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Uplo\ProductImport\Model\Record;
use Uplo\ProductImport\Model\ResourceModel\Record as RecordResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Record::class, RecordResource::class);
    }
}
