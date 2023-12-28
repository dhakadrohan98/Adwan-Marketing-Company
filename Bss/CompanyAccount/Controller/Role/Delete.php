<?php
declare(strict_types=1);
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
namespace Bss\CompanyAccount\Controller\Role;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Exception\CantDeleteAssignedRole;
use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Bss\CompanyAccount\Helper\ActionHelper
     */
    private $actionHelper;

    /**
     * @var \Bss\CompanyAccount\Helper\FormHelper
     */
    private $formHelper;

    /**
     * Delete constructor.
     *
     * @param SubRoleRepositoryInterface $roleRepository
     * @param LoggerInterface $logger
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     * @param \Bss\CompanyAccount\Helper\FormHelper $formHelper
     * @param \Bss\CompanyAccount\Helper\ActionHelper $actionHelper
     * @param Context $context
     */
    public function __construct(
        SubRoleRepositoryInterface $roleRepository,
        LoggerInterface $logger,
        JsonFactory $jsonFactory,
        Data $helper,
        \Bss\CompanyAccount\Helper\FormHelper $formHelper,
        \Bss\CompanyAccount\Helper\ActionHelper $actionHelper,
        Context $context
    ) {
        $this->roleRepository = $roleRepository;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->actionHelper = $actionHelper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->jsonFactory = $jsonFactory;
        $this->formHelper = $formHelper;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return $this->jsonFactory->create()->setData(['remove_row' => false]);
        }
        $removeRow = true;
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->customerSession->getCustomer()->getWebsiteId())
        ) {
            return $this->resultRedirectFactory->create()
                ->setPath('customer/account/');
        }
        try {
            if ($this->getRequest()->isPost()) {
                $roleId = $this->getRequest()->getParam('role_id');
                $message = $this->actionHelper->destroyRole($this->roleRepository, $roleId);
                $this->messageManager->addSuccessMessage($message);
            }
        } catch (CantDeleteAssignedRole $e) {
            $removeRow = false;
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete the sub-user right now.'));
            $removeRow = false;
            $this->logger->critical($e);
        }

        return $this->jsonFactory->create()
            ->setData(
                ['remove_row' => $removeRow]
            );
    }
}
