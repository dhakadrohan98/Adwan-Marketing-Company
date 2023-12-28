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
 * Interface UpdateRequestInterface
 * @package Mageplaza\QuickOrder\Api\Data
 */
interface UpdateRequestInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const ITEMS = 'items';

    /**
     * @return \Mageplaza\QuickOrder\Api\Data\ItemUpdateRequestInterface[]
     */
    public function getItems();

    /**
     * @param string[] $value
     *
     * @return $this
     */
    public function setItems($value);
}
