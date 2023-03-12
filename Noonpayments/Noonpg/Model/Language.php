<?php
// noon payments v2.1.1...
namespace Noonpayments\Noonpg\Model;


class Language implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'en',
                'label' => 'English',
            ],
            [
                'value' => 'ar',
                'label' => 'Arabic'
            ]
        ];
    }
}
