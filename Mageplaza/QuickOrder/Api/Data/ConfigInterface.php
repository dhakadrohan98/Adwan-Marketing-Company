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
 * Interface ConfigInterface
 * @package Mageplaza\QuickOrder\Api\Data
 */
interface ConfigInterface
{
    /**
     * @return \Mageplaza\QuickOrder\Api\Data\GeneralConfigInterface
     */
    public function getGeneral();

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\GeneralConfigInterface $value
     *
     * @return $this
     */
    public function setGeneral($value);

    /**
     * @return \Mageplaza\QuickOrder\Api\Data\SearchConfigInterface
     */
    public function getSearch();

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\SearchConfigInterface $value
     *
     * @return $this
     */
    public function setSearch($value);

    /**
     * @return \Mageplaza\QuickOrder\Api\Data\DesignConfigInterface
     */
    public function getDesign();

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\DesignConfigInterface $value
     *
     * @return $this
     */
    public function setDesign($value);
}
