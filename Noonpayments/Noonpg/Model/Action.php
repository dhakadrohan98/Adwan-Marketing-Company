<?php
// noon payments v2.1.1...
namespace Noonpayments\Noonpg\Model;


class Action implements \Magento\Framework\Option\ArrayInterface
{
    const ACTION_AUTH    = 'AUTHORIZE';
    const ACTION_SALE    = 'SALE';

    /**
     * Possible action types
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTION_AUTH,
                'label' => 'Authorize',
            ],
            [
                'value' => self::ACTION_SALE,
                'label' => 'Sale'
            ]
        ];
    }
}
