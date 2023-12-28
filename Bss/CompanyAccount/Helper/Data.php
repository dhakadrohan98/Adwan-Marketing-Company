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
namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Model\Config\Source\CompanyAccountValue;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Data
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_company_account/general/enable';
    const XML_ADMIN_EMAIL_SENDER = 'bss_company_account/email/email_sender';
    const XML_PATH_COMPANY_ACCOUNT_APPROVAL_EMAIL_TEMPLATE = 'bss_company_account/email/ca_approval';
    const XML_PATH_COMPANY_ACCOUNT_REMOVE_EMAIL_TEMPLATE = 'bss_company_account/email/ca_remove';
    const XML_PATH_APPROVAL_COPY_TO_EMAILS = 'bss_company_account/email/send_approval_copy_to';
    const XML_PATH_REMOVE_COPY_TO_EMAILS = 'bss_company_account/email/send_remove_copy_to';
    const XML_PATH_WELCOME_SUB_USER_EMAIL_TEMPLATE = 'bss_company_account/email/subuser_welcome';
    const XML_PATH_RESET_SUBUSER_PASSWORD_EMAIL_TEMPLATE = 'bss_company_account/email/subuser_reset_password';
    const XML_PATH_REMOVE_SUB_USER_EMAIL_TEMPLATE = 'bss_company_account/email/subuser_remove';
    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';
    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    const XML_PATH_B2BREGISTRATION_ENABLE_CONFIG = 'b2b/general/enable';

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * Data constructor.
     *
     * @param HelperData $helperData
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param StoreManager $storeManager
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Context $context
     */
    public function __construct(
        HelperData $helperData,
        RedirectInterface $redirect,
        ManagerInterface $messageManager,
        StoreManager $storeManager,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        Context $context
    ) {
        $this->helperData = $helperData;
        $this->dateTimeFactory = $this->helperData->getDateTimeFactory();
        $this->customerSession = $this->helperData->getCustomerSession();
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Get Currency object
     *
     * @param string $id
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrency($id = null)
    {
        if ($id) {
            return $this->helperData->getCurrency()->load($id);
        }
        return $this->helperData->getCurrency();
    }

    /**
     * Get base currency
     *
     * @return \Magento\Directory\Model\Currency
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseCurrency()
    {
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        return $this->getCurrency()->load($baseCurrencyCode);
    }

    /**
     * Get date time object
     *
     * @return \Magento\Framework\Intl\DateTimeFactory
     */
    public function getDateTimeFactory()
    {
        return $this->dateTimeFactory;
    }

    /**
     * Convert amount to specify currency
     *
     * @param float $amount
     * @param bool $toCurrent
     *
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertCurrency($amount, $toCurrent = true)
    {
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $rate = $this->storeManager->getStore()->getCurrentCurrencyRate();
        if ($currentCurrencyCode == $baseCurrencyCode) {
            return $amount;
        }
        if ($toCurrent) {
            return $this->getBaseCurrency()->convert($amount, $currentCurrencyCode);
        }
        return (float) $amount / $rate;
    }

    /**
     * Convert format amount
     *
     * @param float $amount
     * @return float|string
     */
    public function convertFormatCurrency($amount)
    {
        return $this->helperData->getPriceHelper()->currency($amount, true);
    }

    /**
     * Get resource object
     *
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get data helper
     *
     * @return HelperData
     */
    public function getDataHelper()
    {
        return $this->helperData;
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * Check module is enable with website scope
     *
     * @param null|int $website
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEnable($website = null)
    {
        if ($website === null) {
            $website = $this->getStoreManager()->getWebsite()->getId();
        }
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * True if customer is company account
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer|null $customer
     * @return bool
     */
    public function isCompanyAccount($customer = null)
    {
        if ($customer == null) {
            $customer = $this->customerSession->getCustomer();
        }
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $companyAccountAttr = $customer->getData('bss_is_company_account');
        } else {
            $companyAccountAttr = $customer->getCustomAttribute('bss_is_company_account');
        }
        if ($companyAccountAttr) {
            return is_string($companyAccountAttr) ?
                (int)$companyAccountAttr === CompanyAccountValue::IS_COMPANY_ACCOUNT :
                (int)$companyAccountAttr->getValue() === CompanyAccountValue::IS_COMPANY_ACCOUNT;
        }
        return false;
    }

    /**
     * Get email sender
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailSender()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['email'];
    }

    /**
     * Get sender email name
     *
     * @return string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmailSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResolver()->resolve($from);
        return $result['name'];
    }

    /**
     * Get new company account approval mail template
     *
     * @return string
     */
    public function getCompanyAccountApprovalEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_APPROVAL_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get new company account remove mail template
     *
     * @return string
     */
    public function getCompanyAccountRemoveEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COMPANY_ACCOUNT_REMOVE_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get reset sub-user reset password email template
     *
     * @return string
     */
    public function getResetSubUserPasswordEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RESET_SUBUSER_PASSWORD_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get welcome sub-user to company account email template
     *
     * @return string
     */
    public function getWelcomeSubUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WELCOME_SUB_USER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get remove sub-user email template
     *
     * @return string
     */
    public function getRemoveSubUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REMOVE_SUB_USER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get approval emails copy to
     *
     * @return string
     */
    public function getCaApprovalCopyToEmails()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_APPROVAL_COPY_TO_EMAILS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get remove emails copy to
     *
     * @return string
     */
    public function getCaRemoveCopyToEmails()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REMOVE_COPY_TO_EMAILS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    public function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    public function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return parent::_getRequest();
    }

    /**
     * Retrieve redirect object
     *
     * @return RedirectInterface
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Retrieve message manager object
     *
     * @return ManagerInterface
     */
    public function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Retrieve store manager object
     *
     * @return StoreManager
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get current website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get scope config object
     *
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    public function getCookieManager()
    {
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    public function getCookieMetadataFactory()
    {
        return $this->cookieMetadataFactory;
    }
}
