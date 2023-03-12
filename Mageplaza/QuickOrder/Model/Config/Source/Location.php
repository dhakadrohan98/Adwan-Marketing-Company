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

namespace Mageplaza\QuickOrder\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Location
 * @package Mageplaza\QuickOrder\Model\Config\Source
 */
class Location implements ArrayInterface
{
    const LOCATION_TOP = 'top_menu';
    const LOCATION_FOOTER = 'footer_link';
    const LOCATION_OTHER = 'next_searchbox';
    const LOCATION_CUSTOMERWELCOME = 'position_customer_welcome';

    /**
     * get available locations.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::LOCATION_TOP => __('Top menu'),
            self::LOCATION_FOOTER => __('Footer link'),
            self::LOCATION_OTHER => __('Next to Search Box'),
        ];
    }
}
