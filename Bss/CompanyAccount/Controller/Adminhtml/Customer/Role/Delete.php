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

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Exception\CantDeleteAssignedRole;
use Bss\CompanyAccount\Helper\ActionHelper;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\Role
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_ROLE_DELETE = 'Bss_CompanyAccount::role_delete';

    /**
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ActionHelper
     */
    private $actionHelper;

    /**
     * Delete constructor.
     *
     * @param SubRoleRepositoryInterface $roleRepository
     * @param LoggerInterface $logger
     * @param ActionHelper $actionHelper
     * @param JsonFactory $resultJsonFactory
     * @param Action\Context $context
     */
    public function __construct(
        SubRoleRepositoryInterface $roleRepository,
        LoggerInterface $logger,
        ActionHelper $actionHelper,
        JsonFactory $resultJsonFactory,
        Action\Context $context
    ) {
        $this->roleRepository = $roleRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->actionHelper = $actionHelper;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Delete role action
     */
    public function execute()
    {
        $error = false;
        if ($this->_authorization->isAllowed(self::ADMIN_ROLE_DELETE)) {
            try {
                $roleId = $this->getRequest()->getParam('id');
                $message = $this->actionHelper->destroyRole($this->roleRepository, $roleId);
            } catch (CantDeleteAssignedRole $e) {
                $error = true;
                $message = $e->getMessage();
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the role right now.');
                $this->logger->critical($e);
            }
        } else {
            $error = true;
            $message = __('Sorry, you need permissions to %1.', __('delete role'));
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
