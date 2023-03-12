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

namespace Mageplaza\QuickOrder\Helper;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\QuickOrder\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'quickorder';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Customer session
     *
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param HttpContext $httpcontext
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        HttpContext $httpcontext
    ) {
        $this->_customerSession = $customerSession;
        $this->_httpContext = $httpcontext;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getShowLinkPosition($storeId = null)
    {
        return $this->getConfigGeneral('show_quickorder_button', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->getConfigGeneral('route_name', $storeId) ?: 'quick-order';
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCustomerGroupAllowAccess($storeId = null)
    {
        return $this->getConfigGeneral('allow_customer_group', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPageTitle($storeId = null)
    {
        return $this->getConfigGeneral('page_title', $storeId) ?: __('Quick Order');
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getQuickOrderLabel($storeId = null)
    {
        return $this->getConfigGeneral('quickorder_label', $storeId) ?: __('Quick Order');
    }

    /**
     * @return bool
     */
    public function checkPermissionAccess()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $customerAllowAccess = explode(',', $this->getCustomerGroupAllowAccess());
        if (empty($customerAllowAccess)) {
            return false;
        }

        if (!$this->getCustomerLogedIn()) {
            $customerGroupNotlogedIn = '0';

            return in_array($customerGroupNotlogedIn, $customerAllowAccess, true);
        }

        $customerGroupId = $this->getCustomerGroupId();
        if (!in_array($customerGroupId, $customerAllowAccess, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHeadingBackgroundColor($storeId = null)
    {
        return $this->getModuleConfig('design/heading_background_color', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHeadingTextColor($storeId = null)
    {
        return $this->getModuleConfig('design/heading_text_color', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHeadingBackgroundButton($storeId = null)
    {
        return $this->getModuleConfig('design/heading_background_button', $storeId);
    }

    /**
     * @return bool
     */
    public function getCustomerLogedIn()
    {
        return $this->_httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function getCustomerGroupId()
    {
        return $this->_customerSession->getCustomer()->getGroupId();
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getQuickOrderButtonLabel($storeId = null)
    {
        return $this->getConfigGeneral('quickorder_button_label', $storeId) ?: __('Quick Order');
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getQuickOrderBackgroundButton($storeId = null)
    {
        return $this->getModuleConfig('design/button_quickorder_background_color', $storeId);
    }
}
