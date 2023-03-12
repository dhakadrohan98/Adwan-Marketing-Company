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
namespace Bss\CompanyAccount\Plugin\Quote;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Class ParamOverriderCartId
 *
 * @package Bss\CompanyAccount\Plugin\Quote
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ParamOverriderCartId
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * ParamOverriderCartId constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $helper
     * @param UserContextInterface $userContext
     * @param CartManagementInterface $cartManagement
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        \Psr\Log\LoggerInterface $logger,
        Data $helper,
        UserContextInterface $userContext,
        CartManagementInterface $cartManagement,
        RequestInterface $request,
        CheckoutSession $checkoutSession
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->subUserRepository = $subUserRepository;
        $this->logger = $logger;
        $this->userContext = $userContext;
        $this->cartManagement = $cartManagement;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * { @inheritDoc }
     */
    public function getOverriddenValue()
    {
        try {
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();
                if ($this->checkoutSession->getCheckoutIsQuoteExtension()) {
                    return $this->checkoutSession->getCheckoutIsQuoteExtension();
                }

                $referer = $this->request->getHeader('Referer');
                if (strpos($referer, 'quoteextension') !== false && $this->checkoutSession->getIsQuoteExtension()) {
                    return $this->checkoutSession->getIsQuoteExtension();
                }

                /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
                if ($subUser = $this->customerSession->getSubUser()) {
                    $cart = $this->subUserRepository->getQuoteBySubUser($subUser);
                    if ($cart) {
                        return $cart->getId();
                    }
                }

                /** @var \Magento\Quote\Api\Data\CartInterface */
                $cart = $this->cartManagement->getCartForCustomer($customerId);
                if ($cart) {
                    return $cart->getId();
                }
            }
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Current customer does not have an active cart.'));
        }
        return null;
    }
}
