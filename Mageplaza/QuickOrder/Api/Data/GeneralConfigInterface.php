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

namespace Mageplaza\QuickOrder\Api\Data;

/**
 * Interface GeneralConfigInterface
 * @package Mageplaza\QuickOrder\Api\Data
 */
interface GeneralConfigInterface
{
    /**
     * @return boolean
     */
    public function getEnabled();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setEnabled($value);

    /**
     * @return string
     */
    public function getRouteName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setRouteName($value);

    /**
     * @return string
     */
    public function getPageTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPageTitle($value);

    /**
     * @return string
     */
    public function getAllowCustomerGroup();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAllowCustomerGroup($value);

    /**
     * @return string
     */
    public function getShowQuickorderButton();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowQuickorderButton($value);

    /**
     * @return string
     */
    public function getQuickorderLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setQuickorderLabel($value);
}
