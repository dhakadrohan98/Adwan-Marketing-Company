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
namespace Bss\CompanyAccount\Plugin\Customer;

use Magento\Customer\Model\Session;

/**
 * Class CustomerData
 *
 * @package Bss\CompanyAccount\Plugin\Customer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerData
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * CustomerData constructor.
     *
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * After get customer session data
     *
     * Will change full name of customer to sub-user name whenever
     * account login in is sub-user account
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param array $result
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($subUser) {
            $result['fullname'] = $subUser->getSubName() . " [" . $result['fullname'] . "]";
        }

        return $result;
    }
}
