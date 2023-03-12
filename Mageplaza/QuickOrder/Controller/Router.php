<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\QuickOrder\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;
use Magento\Framework\App\RouterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\QuickOrder\Helper\Data;

/**
 * Class Router
 * @package Mageplaza\QuickOrder\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ActionList
     */
    private $actionList;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ConfigInterface
     */
    protected $routeConfig;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $routeConfig
     * @param ActionList $actionList
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigInterface $routeConfig,
        ActionList $actionList,
        Data $helperData,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->routeConfig = $routeConfig;
        $this->actionList = $actionList;
        $this->_helperData = $helperData;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if ($identifier !== 'quickorder'
            && $identifier !== 'quickorder/index/index'
            && $identifier !== $this->_helperData->getUrlSuffix()
        ) {
            return null;
        }

        $modules = $this->routeConfig->getModulesByFrontName('quickorder');
        if (empty($modules)) {
            return null;
        }

        $actionClassName = $this->actionList->get($modules[0], null, 'index', 'index');

        return $this->actionFactory->create($actionClassName);
    }
}
