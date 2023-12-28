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
 * Interface DesignConfigInterface
 * @package Mageplaza\QuickOrder\Api\Data
 */
interface DesignConfigInterface
{
    /**
     * @return string
     */
    public function getHeadingBackgroundColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setHeadingBackgroundColor($value);

    /**
     * @return string
     */
    public function getHeadingTextColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setHeadingTextColor($value);

    /**
     * @return string
     */
    public function getHeadingBackgroundButton();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setHeadingBackgroundButton($value);
}
