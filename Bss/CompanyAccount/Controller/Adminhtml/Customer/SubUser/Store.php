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
namespace Bss\CompanyAccount\Controller\Adminhtml\Customer\SubUser;

use Bss\CompanyAccount\Exception\EmailValidateException;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Class Store
 *
 * @package Bss\CompanyAccount\Controller\Adminhtml\customer\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Store extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_SUB_USER_ADD = 'Bss_CompanyAccount::sub_user_add';
    const ADMIN_SUB_USER_EDIT = 'Bss_CompanyAccount::sub_user_edit';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Bss\CompanyAccount\Helper\EmailHelper
     */
    private $emailHelper;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * Store constructor.
     *
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param SubUserHelper $subUserHelper
     * @param CustomerRepository $customerRepository
     * @param \Bss\CompanyAccount\Helper\EmailHelper $emailHelper
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        SubUserHelper $subUserHelper,
        CustomerRepository $customerRepository,
        \Bss\CompanyAccount\Helper\EmailHelper $emailHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->emailHelper = $emailHelper;
        $this->subUserHelper = $subUserHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Save sub-user
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id', false);
        $subId = $this->getRequest()->getParam('sub_id', '');
        $error = false;
        $permissionMsg = '';
        if (empty($subId) && !$this->_authorization->isAllowed(self::ADMIN_SUB_USER_ADD)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('create sub-user'));
        } elseif (!empty($subId) && !$this->_authorization->isAllowed(self::ADMIN_SUB_USER_EDIT)) {
            $error = true;
            $permissionMsg = __('Sorry, you need permissions to %1.', __('edit sub-user'));
        }
        try {
            $massageErrorEmail = "";
            $createdSubUser = null;
            $message = !$error ?
                $this->subUserHelper
                    ->createSubUser($this->getRequest(), $customerId, $massageErrorEmail, $createdSubUser) :
                $permissionMsg;

            $this->_eventManager->dispatch(
                'adminhtml_controller_subuser_save_after',
                [
                    'sub_user' => $createdSubUser,
                    'subject' => $this
                ]
            );
        } catch (AlreadyExistsException | NotFoundException | EmailValidateException $exception) {
            $error = true;
            $message = $exception->getMessage();
        } catch (\Exception $exception) {
            $error = true;
            $message = __('We can\'t save sub-user right now.');
            $this->logger->critical($exception);
        }

        $subId = empty($subId) ? null : $subId;
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'messageErrorEmail' => $massageErrorEmail,
                'error' => $error,
                'data' => [
                    'sub_id' => $subId
                ]
            ]
        );

        return $resultJson;
    }
}
