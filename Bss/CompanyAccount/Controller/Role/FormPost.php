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

use Bss\CompanyAccount\Exception\EmptyInputException;
use Bss\CompanyAccount\Helper\FormHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 *
 * @package Bss\CompanyAccount\Controller\Role
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPost extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Bss\CompanyAccount\Api\SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $helper;

    /**
     * @var \Bss\CompanyAccount\Helper\ActionHelper
     */
    private $actionHelper;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * FormPost constructor.
     *
     * @param Context $context
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory
     * @param LoggerInterface $logger
     * @param FormHelper $formHelper
     * @param \Bss\CompanyAccount\Helper\Data $helper
     * @param \Bss\CompanyAccount\Helper\ActionHelper $actionHelper
     * @param \Magento\Customer\Model\Url $url
     */
    public function __construct(
        Context $context,
        \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository,
        \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory,
        LoggerInterface $logger,
        FormHelper $formHelper,
        \Bss\CompanyAccount\Helper\Data $helper,
        \Bss\CompanyAccount\Helper\ActionHelper $actionHelper,
        \Magento\Customer\Model\Url $url
    ) {
        $this->helper = $helper;
        $this->actionHelper = $actionHelper;
        $this->roleRepository = $roleRepository;
        $this->roleFactory = $roleFactory;
        $this->logger = $logger;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->url = $url;
        $this->formHelper = $formHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Save role action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->formHelper->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()
                ->setUrl($this->_redirect->getRefererUrl());
        }
        if (!$this->helper->isCompanyAccount() ||
            !$this->helper->isEnable($this->customerSession->getCustomer()->getWebsiteId())
        ) {
            return $this->resultRedirectFactory->create()
                ->setPath('customer/account/');
        }
        $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
            $this->getRequest()->getPost()
        );
        try {
            $this->helper->getDataHelper()->getCoreSession()->unsRoleFormData();
            $customerId = $this->customerSession->getCustomerId();
            $message = $this->actionHelper->saveRole(
                $this->getRequest(),
                $this->roleFactory,
                $this->roleRepository,
                $customerId
            );
            $this->messageManager->addSuccessMessage($message);
            return $this->resultRedirectFactory->create()
                ->setPath('companyaccount/role');
        } catch (EmptyInputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
                $this->getRequest()->getPost()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t save role right now.')
            );
            $this->logger->critical($e);
            $this->helper->getDataHelper()->getCoreSession()->setRoleFormData(
                $this->getRequest()->getPost()
            );
        }

        return $this->resultRedirectFactory->create()
            ->setUrl($this->_redirect->getRefererUrl());
    }
}
