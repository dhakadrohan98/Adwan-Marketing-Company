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
namespace Bss\CompanyAccount\Block\Sales\Order\View;

use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Framework\View\Element\Template;

/**
 * Class SubUserInfo
 *
 * @package Bss\CompanyAccount\Block\Sales\Order\View
 */
class SubUserInfo extends Template
{
    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * SubInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        Template\Context $context,
        array $data = []
    ) {
        $this->subUserInfoHelper = $subUserInfoHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUserInfo()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        return $this->subUserInfoHelper->getSubUserInfo($orderId);
    }
}
