<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\ResourceModel\CronjobResult;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Uplo\ProductImport\Model\CronjobResult;
use Uplo\ProductImport\Model\ResourceModel\CronjobResult as CronjobResultResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(CronjobResult::class, CronjobResultResource::class);
    }
}
