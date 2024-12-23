<?php declare(strict_types=1);

namespace Uplo\ProductImport\Service;

use Uplo\ProductImport\Model\ResourceModel\Record;

class RecordIdChecker
{
    public function __construct(private Record $record)
    {
    }
}
