<?php declare(strict_types=1);

namespace Uplo\ProductImport\Controller;

use Uplo\ProductImport\Service\RecordIdChecker;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @param RecordIdChecker $recordIdChecker
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        private RecordIdChecker $recordIdChecker,
        private ActionFactory $actionFactory
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $pathInfo = trim((string) $request->getPathInfo(), '/');

        $parts = explode('/', $pathInfo);
        if (!empty($parts[0]) && 'productimport' === $parts[0] && !empty($parts[1])) {
            $urlKey = $parts[1];
        } else {
            return null;
        }

        $request
            ->setModuleName('productimport')
            ->setControllerName('record')
            ->setActionName('view')
            ->setParam('record_id', $recordId);
        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $pathInfo);
        $request->setPathInfo($urlKey);

        return $this->actionFactory->create(Forward::class);
    }
}
