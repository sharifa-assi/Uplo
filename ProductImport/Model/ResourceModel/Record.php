<?php

declare(strict_types=1);

namespace Uplo\ProductImport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Record extends AbstractDb
{
    private const TABLE_NAME = 'uplo_productimport_record';
    private const PRIMARY_KEY = 'record_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::PRIMARY_KEY);
    }
}
