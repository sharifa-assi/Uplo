<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\ResourceModel\Product;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Uplo\ProductImport\Model\Product;
use Uplo\ProductImport\Model\ResourceModel\Product as ProductResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Product::class, ProductResource::class);
    }
}
