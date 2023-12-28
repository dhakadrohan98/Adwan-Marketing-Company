<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model\Config\Source;

/**
 * Class Permissions
 *
 * @package Bss\CompanyAccount\Model\Config\Source
 */
class Permissions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var SubRole\Source
     */
    private $subRoleConfig;

    /**
     * Permissions constructor.
     *
     * @param SubRole\Source $subRoleConfig
     */
    public function __construct(
        \Bss\CompanyAccount\Model\Config\Source\SubRole\Source $subRoleConfig
    ) {
        $this->subRoleConfig = $subRoleConfig;
    }

    const ADMIN = 0;
    const VIEW_ACCOUNT_DASHBOARD = 1;
    const VIEW_DOWNLOADABLE_PRODUCT = 2;
    const ADD_VIEW_ACCOUNT_WISHLIST = 3;
    const ADD_VIEW_ADDRESS_BOOK = 4;
    const VIEW_STORED_PAYMENT_METHOD = 5;
    const MANAGE_SUB_USER_AND_ROLES = 6;
    const VIEW_ALL_ORDER = 7;
    const ADD_TO_QUOTE = 9;
    const VIEW_QUOTES = 10;
    const MAX_ORDER_AMOUNT = 'max_order_amount';
    const MAX_ORDER_PERDAY = 'order_per_day';

    /**
     * Return array of permission options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->subRoleConfig->getRuleOptionArray();
    }

    /**
     * Get mapped rules data array for tree js
     *
     * @return array
     */
    public function mappedDataArray()
    {
        $output['attr']['data-id'] = 0;
        $output['data'] = __('Bss Company Account');
        $output['state'] = 'open';
        $output['children'][] = $this->mapRules($this->subRoleConfig->getRuleOptionArray());
        return $output;
    }

    /**
     * Map rule data to tree js
     *
     * @param array $rules
     * @return array
     */
    protected function mapRules($rules)
    {
        $output = [];
        foreach ($rules as $rule) {
            if (isset($rule['remove']) && $rule['remove'] === true) {
                continue;
            }
            $item = [];
            $item['attr']['data-id'] = $rule['value'];
            $text = __($rule['label']);
            if ((int) $rule['value'] > 0) {
                $text .= " (" . $rule['value'] . ")";
            }
            $item['data'] = $text;
            $item['children'] = [];
            if (isset($rule['children'])) {
                $item['state'] = 'open';
                $item['children'] = $this->mapRules($rule['children']);
            }
            $output[] = $item;
        }
        return $output;
    }
}
