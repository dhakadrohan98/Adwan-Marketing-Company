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

namespace Mageplaza\QuickOrder\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\QuickOrder\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\QuickOrder\Controller\Index
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $storeId = $this->_helperData->getStore()->getStoreId();
        $identifier = trim($this->_request->getPathInfo(), '/');
        if ($this->_helperData->checkPermissionAccess() === false
            || in_array($identifier, ['quickorder', 'quickorder/index/index'])) {
            $this->_redirect('cms/noroute/');
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('quickorder_index_index');
        $resultPage->getConfig()->getTitle()->set($this->_helperData->getPageTitle($storeId));

        return $resultPage;
    }
}
