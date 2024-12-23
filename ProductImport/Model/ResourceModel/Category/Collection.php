<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Uplo\ProductImport\Model\Category;
use Uplo\ProductImport\Model\ResourceModel\Category as CategoryResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Category::class, CategoryResource::class);
    }
}
