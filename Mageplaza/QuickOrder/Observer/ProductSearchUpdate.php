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

namespace Mageplaza\QuickOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\QuickOrder\Helper\Data;
use Mageplaza\QuickOrder\Helper\Search;

/**
 * Class ProductSearchUpdate
 * @package Mageplaza\QuickOrder\Observer
 */
class ProductSearchUpdate implements ObserverInterface
{
    /**
     * @var Search
     */
    protected $_helperSearch;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * ProductSearchUpdate constructor.
     *
     * @param Search $helperSearch
     * @param Data $helperData
     */
    public function __construct(
        Search $helperSearch,
        Data $helperData
    ) {
        $this->_helperSearch = $helperSearch;
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $this->_helperSearch->getMediaHelper()->removeJsPath();

        return $this;
    }
}
