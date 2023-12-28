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
namespace Bss\CompanyAccount\Controller\Adminhtml\Customer\Role;

use Bss\CompanyAccount\Exception\EmptyInputException;
use Bss\CompanyAccount\Helper\ActionHelper;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\Role
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_ROLE_ADD = 'Bss_CompanyAccount::role_add';
    const ADMIN_ROLE_EDIT = 'Bss_CompanyAccount::role_edit';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Bss\CompanyAccount\Api\SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var ActionHelper
     */
    private $actionHelper;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory
     * @param ActionHelper $actionHelper
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository,
        \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory,
        ActionHelper $actionHelper,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->roleRepository = $roleRepository;
        $this->roleFactory = $roleFactory;
        $this->actionHelper = $actionHelper;
    }

    /**
     * Save role action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $roleId = $this->getRequest()->getParam('role_id', '');
        $error = false;
        $permissionMsg = '';
        if (empty($roleId) && !$this->_authorization->isAllowed(self::ADMIN_ROLE_ADD)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('create role'));
        } elseif (!empty($roleId) && !$this->_authorization->isAllowed(self::ADMIN_ROLE_EDIT)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('edit role'));
        }
        $customerId = $this->getRequest()->getParam('customer_id', false);

        try {
            $message = !$error ?
                $this->actionHelper->saveRole(
                    $this->getRequest(),
                    $this->roleFactory,
                    $this->roleRepository,
                    $customerId
                ) : $permissionMsg;

        } catch (EmptyInputException $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $error = true;
            $message = __('We can\'t save role right now.');
            $this->logger->critical($e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error
            ]
        );

        return $resultJson;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }
}
