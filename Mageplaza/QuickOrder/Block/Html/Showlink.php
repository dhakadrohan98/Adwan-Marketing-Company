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

namespace Mageplaza\QuickOrder\Block\Html;

use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\QuickOrder\Helper\Data;
use Mageplaza\QuickOrder\Model\Config\Source\Location;

/**
 * Class Showlink
 * @package Mageplaza\QuickOrder\Block\Html
 */
class Showlink extends Link
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * Showlink constructor.
     *
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Data $helperData
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_helperData->isEnabled() || !$this->_helperData->checkPermissionAccess()) {
            return '';
        }

        $type = $this->getType();
        $storeId = $this->_helperData->getStoreId();
        $aTag = '<a href=' . $this->getLink() . '>' . $this->_helperData->getQuickOrderButtonLabel($storeId) . '</a>';

        if ($this->_helperData->getShowLinkPosition() === $type) {
            if ($type === Location::LOCATION_TOP && $this->_helperData->getCustomerLogedIn()) {
                return '';
            }

            if ($type === Location::LOCATION_OTHER) {
                return '<div id="quickorder-top-link"><div id="quickorder-link" style="background: ' .
                    $this->_helperData->getQuickOrderBackgroundButton($storeId) . '">' . $aTag . '</div></div>';
            }

            return "<li>{$aTag}</li>";
        }

        if ($type === Location::LOCATION_CUSTOMERWELCOME && $this->_helperData->getCustomerLogedIn()) {
            if ($this->_helperData->getShowLinkPosition() !== Location::LOCATION_TOP) {
                return '';
            }

            return "<li>{$aTag}</li>";
        }

        return '';
    }

    /**
     * get url quick order suffix
     *
     * @return string
     */
    public function getLink()
    {
        return $this->getUrl($this->_helperData->getUrlSuffix());
    }
}
