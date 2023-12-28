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
namespace Bss\CompanyAccount\Plugin\Order\Email\Container;

/**
 * Class Container
 *
 * @package Bss\CompanyAccount\Plugin\Order\Email\Container
 */
class Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Container constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * If order was placed by sub-user will return sub-user email
     *
     * @param \Magento\Sales\Model\Order\Email\Container\Container $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomerEmail($subject, $result)
    {
        $subUser = $this->getSubUser();
        if ($subUser) {
            return $subUser->getSubEmail();
        }
        return $result;
    }

    /**
     * If order was placed by sub-user will return sub-user name
     *
     * @param \Magento\Sales\Model\Order\Email\Container\Container $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomerName($subject, $result)
    {
        $subUser = $this->getSubUser();
        if ($subUser) {
            return $subUser->getSubName();
        }
        return $result;
    }

    /**
     * Get registered sub-user
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    protected function getSubUser()
    {
        return $this->registry->registry('bss_is_send_mail_to_sub_user');
    }
}
