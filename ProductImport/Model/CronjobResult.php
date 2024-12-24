<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model;

use Magento\Framework\Model\AbstractModel;
use Uplo\ProductImport\Model\ResourceModel\CronjobResult as CronjobResultResource;

class CronjobResult extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(CronjobResultResource::class);
    }
}
