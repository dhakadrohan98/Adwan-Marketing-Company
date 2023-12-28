<?php
// noon payments v2.1.1...
namespace Noonpayments\Noonpg\Model;


class Mode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'redirect',
                'label' => 'Redirect',
            ],
            [
                'value' => 'lightbox',
                'label' => 'Lightbox'
            ]
        ];
    }
}
