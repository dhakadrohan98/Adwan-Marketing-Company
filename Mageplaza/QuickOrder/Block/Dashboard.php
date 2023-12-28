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

namespace Mageplaza\QuickOrder\Block;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\QuickOrder\Helper\Data;

/**
 * Class Dashboard
 * @package Mageplaza\QuickOrder\Block
 */
class Dashboard extends Template
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * Dashboard constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param FormatInterface $localeFormat
     * @param Session $session
     */
    public function __construct(
        Context $context,
        Data $helperData,
        FormatInterface $localeFormat,
        Session $session
    ) {
        $this->_helperData      = $helperData;
        $this->localeFormat     = $localeFormat;
        $this->_customerSession = $session;
        parent::__construct($context);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPageTitle()
    {
        $storeId = $this->_helperData->getStoreId();

        return $this->_helperData->getPageTitle($storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getQuickOrderLabel()
    {
        $storeId = $this->_helperData->getStoreId();

        return $this->_helperData->getQuickOrderLabel($storeId);
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuickOrderConfig()
    {
        $data = new DataObject([
            'changeOption'  => $this->getUrl('quickorder/items/changeoption'),
            'customerGroup' => $this->_customerSession->getCustomerGroupId(),
            'buildItemUrl'  => $this->getUrl('quickorder/items/preitem/'),
            'buildDataXml'  => $this->getUrl('quickorder/index/builddataxml'),
            'downloadCsv'   => $this->getUrl('quickorder/index/downloadcsv'),
            'downloadXml'   => $this->getUrl('quickorder/index/downloadxml'),
            'addCartAction' => $this->getUrl('quickorder/items/cartcheckout/'),
            'cartpage'      => $this->getUrl('checkout/cart/'),
            'checkoutStep'  => $this->getCheckOutUrl(),
            'itemqty'       => $this->getUrl('quickorder/items/itemqty'),
            'bundleitemqty' => $this->getUrl('quickorder/items/bundleitemqty'),
            'lazyload'      => $this->_assetRepo->getUrlWithParams('images/loader-1.gif', [
                '_secure' => $this->_request->isSecure()
            ]),
            'priceFormat'   => $this->localeFormat->getPriceFormat()
        ]);

        return Data::jsonEncode($data);
    }

    /**
     * return Checkout or OneStepCheckOut Url.
     * @return string
     */
    protected function getCheckOutUrl()
    {
        $objectManager = ObjectManager::getInstance();
        try {
            $oscHelper = $objectManager->create('Mageplaza\Osc\Helper\Data');
        } catch (Exception $e) {
            $oscHelper = false;
        }
        if ($oscHelper && $oscHelper->isEnabled()) {
            $routeCheckout = $oscHelper->getOscRoute();
        } else {
            $routeCheckout = 'checkout';
        }

        return $this->getUrl($routeCheckout);
    }
}
