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

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManager;

/**
 * Class EmailHelper
 *
 * @package Bss\CompanyAccount\Helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailHelper
{
    /**
     * @var GetType
     */
    private $getType;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var SubUserRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $helper;

    /**
     * EmailHelper constructor.
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Data $helper
     * @param GetType $getType
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubUserRepositoryInterface $subUserRepository
     * @param StoreManager $storeManager
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Bss\CompanyAccount\Helper\Data $helper,
        \Bss\CompanyAccount\Helper\GetType $getType,
        CustomerRepositoryInterface $customerRepository,
        SubUserRepositoryInterface $subUserRepository,
        StoreManager $storeManager
    ) {
        $this->getType = $getType;
        $this->customerRepository = $customerRepository;
        $this->subUserRepository = $subUserRepository;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->helper = $helper;
    }

    /**
     * Get customer object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer|int $customer
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer($customer)
    {
        if (is_int($customer)) {
            $customer = $this->customerRepository->getById($customer);
        }

        return $customer;
    }

    /**
     * Get sub-user object
     *
     * @param \Bss\CompanyAccount\Model\SubUser|int $subUser
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSubUser($subUser)
    {
        if (is_int($subUser)) {
            $subUser = $this->subUserRepository->getById($subUser);
        }

        return $subUser;
    }

    /**
     * Send remove notification mail to sub-user
     *
     * @param \Magento\Customer\Model\Customer|int $customer
     * @param \Bss\CompanyAccount\Model\SubUser|int $subUser
     * @throws LocalizedException
     */
    public function sendRemoveNotificationMailToSubUser($customer, $subUser)
    {
        try {
            $subUser = $this->getSubUser($subUser);
            if ($customer) {
                $customer = $this->getCustomer($customer);
            } else {
                $customer = $this->getCustomer($subUser->getCustomerId());
            }
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getRemoveSubUserEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send welcome mail to sub-user
     *
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \Bss\CompanyAccount\Model\SubUser|\Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @throws LocalizedException
     */
    public function sendWelcomeMailToSubUser($customer, $subUser)
    {
        try {
            $customer = $this->getCustomer($customer);
            $subUser = $this->getSubUser($subUser);
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getWelcomeSubUserEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send reset password mail to sub-user
     *
     * @param \Magento\Customer\Model\Customer|\Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \Bss\CompanyAccount\Model\SubUser|\Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @throws LocalizedException
     */
    public function sendResetPasswordMailToSubUser($customer, $subUser)
    {
        try {
            $customer = $this->getCustomer($customer);
            $subUser = $this->getSubUser($subUser);
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $this->sendMail(
                $subUser->getSubEmail(),
                null,
                $this->helper->getResetSubUserPasswordEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send active company account notification for specific customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Backend\Customer $customer
     * @throws LocalizedException
     */
    public function sendActiveCompanyAccountToCustomer($customer)
    {
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $this->sendMail(
                $customer->getEmail(),
                $this->helper->getCaApprovalCopyToEmails(),
                $this->helper->getCompanyAccountApprovalEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getPrefix() . ' ' . $customer->getLastname()
                ]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send deactive company account notification for specific customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Backend\Customer $customer
     * @throws LocalizedException
     */
    public function sendDeactiveCompanyAccountToCustomer($customer)
    {
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->getCustomer($customer);
            $store = $this->storeManager->getStore($customer->getStoreId());
            $this->sendMail(
                $customer->getEmail(),
                $this->helper->getCaRemoveCopyToEmails(),
                $this->helper->getCompanyAccountRemoveEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->getType->getStoreManager()->getStore()->getId(),
                ],
                [
                    'store' => $store,
                    'name' => $customer->getPrefix() . ' ' . $customer->getLastname()
                ]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Send email
     *
     * @param string|null $receiver
     * @param string|null $ccMails
     * @param string $mailTemplate
     * @param array $options
     * @param array $vars
     *
     * @return bool
     * @throws LocalizedException
     */
    private function sendMail($receiver = null, $ccMails = null, $mailTemplate = '', $options = [], $vars = [])
    {
        try {
            $senderEmail = $this->helper->getEmailSender();
            $senderName = $this->helper->getEmailSenderName();
            $sender = [
                'name' => $senderName,
                'email' => $senderEmail,
            ];
            $this->inlineTranslation->suspend();
            $this->transportBuilder
                ->setTemplateIdentifier($mailTemplate)
                ->setTemplateOptions($options)
                ->setTemplateVars($vars)
                ->setFrom($sender)
                ->addTo($receiver);
            if ($ccMails !== null) {
                if (strpos($ccMails, ',') !== false) {
                    $ccMails = explode(',', $ccMails);
                    foreach ($ccMails as $mail) {
                        trim($mail) !== "" ? $this->transportBuilder->addCc(trim($mail)) : false;
                    }
                } else {
                    $this->transportBuilder->addCc(trim($ccMails));
                }
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t send email now. Please try again.'));
        }
    }
}
