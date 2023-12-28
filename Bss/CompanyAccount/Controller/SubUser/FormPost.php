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
namespace Bss\CompanyAccount\Controller\SubUser;

use Bss\CompanyAccount\Exception\EmailValidateException;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Helper\FormHelper;
use Bss\CompanyAccount\Helper\SubUserHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Psr\Log\LoggerInterface;

/**
 * Class FormPost
 *
 * @package Bss\CompanyAccount\Controller\SubUser
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * FormPost constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param FormHelper $formHelper
     * @param SubUserHelper $subUserHelper
     * @param \Magento\Customer\Model\Url $url
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        FormHelper $formHelper,
        SubUserHelper $subUserHelper,
        \Magento\Customer\Model\Url $url,
        Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->logger = $logger;
        $this->subUserHelper = $subUserHelper;
        $this->url = $url;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->formHelper = $formHelper;
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
     * Save sub-user
     *
     * @return \Magento\Framework\Controller\Result\Redirect
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
        $customerId = $this->customerSession->getCustomerId();
        $messageErrorEmail = "";
        try {
            $this->helper->getDataHelper()->getCoreSession()->unsSubUserFormData();

            $message = $this->subUserHelper->createSubUser($this->getRequest(), $customerId, $messageErrorEmail);
            if ($messageErrorEmail) {
                $this->messageManager->addErrorMessage($messageErrorEmail);
            }
            $this->messageManager->addSuccessMessage($message);
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('companyaccount/subuser/');
        } catch (AlreadyExistsException $e) {
            $message = $e->getMessage();
        } catch (EmailValidateException $exception) {
            $message = $exception->getMessage();
        } catch (\Exception $exception) {
            $message = __('We can\'t save sub-user right now.');
            $this->logger->critical($exception);
        }
        $this->helper->getDataHelper()->getCoreSession()->setSubUserFormData(
            $this->getRequest()->getPost()
        );
        $this->messageManager->addErrorMessage($message);
        return $this->resultRedirectFactory->create()
            ->setUrl($this->_redirect->getRefererUrl());
    }
}
