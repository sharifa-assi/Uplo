<?php

declare(strict_types=1);

namespace Uplo\ProductImport\ViewModel;

use Uplo\ProductImport\Model\Record;
use Uplo\ProductImport\Model\ResourceModel\Record\Collection;
use Uplo\ProductImport\Service\RecordsProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;

class Records implements ArgumentInterface
{
    public function __construct(
        private RecordsProvider $recordsProvider,
        private RequestInterface $request
    ) {}

    public function getRecords(int $limit): Collection
    {
        return $this->recordsProvider->getRecords($limit, $this->getCurrentPage());
    }

    private function getCurrentPage(): int
    {
        return (int) $this->request->getParam('p');
    }

    public function getPager(Collection $collection, Pager $pagerBlock): string
    {
        $pagerBlock->setUseContainer(false)
            ->setShowPerPage(false)
            ->setShowAmounts(false)
            ->setFrameLength(3)
            ->setLimit($collection->getPageSize())
            ->setCollection($collection);

        return $pagerBlock->toHtml();
    }

    public function getRecordHtml(Template $block, Record $record): string
    {
        $block->setData('record', $record);
        return $block->toHtml();
    }
}
