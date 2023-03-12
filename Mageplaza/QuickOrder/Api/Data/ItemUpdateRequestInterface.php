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
 * Interface RequestsInterface
 * @package Mageplaza\QuickOrder\Api\Data
 */
interface ItemUpdateRequestInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const ITEM_ID = 'item_id';
    const VALUE   = 'value';

    /**
     * @return string
     */
    public function getItemId();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setItemId($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value);
}
