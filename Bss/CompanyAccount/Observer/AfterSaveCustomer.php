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

namespace Bss\CompanyAccount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class BeforeSaveCustomer
 *
 * @package Bss\CompanyAccount\Observer
 */
class AfterSaveCustomer implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CompanyAccount\Helper\EmailHelper
     */
    private $emailHelper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * AfterSaveCustomer constructor.
     *
     * @param \Bss\CompanyAccount\Helper\EmailHelper $emailHelper
     * @param \Magento\Framework\Registry $registry
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Bss\CompanyAccount\Helper\EmailHelper $emailHelper,
        \Magento\Framework\Registry $registry,
        ManagerInterface $messageManager
    ) {
        $this->emailHelper = $emailHelper;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
    }

    /**
     * Before save customer observer
     *
     * Get send mail from before save, check and send mail
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Customer\Model\Backend\Customer $customer */
        $customer = $observer->getCustomer();
        $isSendActiveCaEmail = $this->registry->registry('bss_send_mail');
        if ($isSendActiveCaEmail !== null) {
            try {
                if ($isSendActiveCaEmail) {
                    $this->emailHelper->sendActiveCompanyAccountToCustomer($customer);
                } else {
                    $this->emailHelper->sendDeactiveCompanyAccountToCustomer($customer);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Oops.. Something went wrong when we send mail to customer.'));
            }
        }
    }
}
