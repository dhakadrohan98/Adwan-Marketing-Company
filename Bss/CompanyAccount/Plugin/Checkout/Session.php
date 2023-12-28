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
namespace Bss\CompanyAccount\Plugin\Checkout;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Class Session
 *
 * @package Bss\CompanyAccount\Plugin\Checkout
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Session
{
    /**
     * Quote instance
     *
     * @var Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * A flag to track when the quote is being loaded and attached to the session object.
     *
     * Used in trigger_recollect infinite loop detection.
     *
     * @var bool
     */
    private $isLoading = false;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var RequestHttp
     */
    private $request;

    /**
     * @param bool
     */
    protected $isQuoteMasked;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * Session constructor.
     *
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param Data $helper
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param RemoteAddress $remoteAddress
     * @param RequestHttp $request
     * @param SubUserRepositoryInterface $subUserRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        Data $helper,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        RemoteAddress $remoteAddress,
        RequestHttp $request,
        SubUserRepositoryInterface $subUserRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->helper = $helper;
        $this->storeManager = $this->helper->getStoreManager();
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->remoteAddress = $remoteAddress;
        $this->request = $request;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Check quote for sub-user
     *
     * @param \Magento\Checkout\Model\Session $subject
     * @param callable $proceed
     *
     * @return \Magento\Quote\Api\Data\CartInterface|Quote|mixed
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundGetQuote(\Magento\Checkout\Model\Session $subject, callable $proceed)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();

        if ($subUser) {
            $this->eventManager->dispatch('sub_quote_process', ['checkout_session' => $this]);
            if ($this->_quote === null) {
                if ($this->isLoading) {
                    throw new \LogicException("Infinite loop detected, review the trace for the looping path");
                }
                $this->isLoading = true;
                /** @var \Magento\Quote\Api\Data\CartInterface|Quote $quote */
                $quote = $this->quoteFactory->create();

                $customerId = $this->customerSession->getCustomerId();
                if ($subject->getQuoteId()) {
                    try {
                        $quote = $this->quoteRepository->getActive($subject->getQuoteId());

                        if ($quote->getData('customer_id') &&
                            (int) $quote->getData('customer_id') !== (int) $customerId
                        ) {
                            $quote = $this->quoteFactory->create();
                            $subject->setQuoteId(null);
                        }

                        /**
                         * If current currency code of quote is not equal current currency code of store,
                         * need recalculate totals of quote. It is possible if customer use currency switcher or
                         * store switcher.
                         */
                        if ($quote->getQuoteCurrencyCode() !=
                            $this->storeManager->getStore()->getCurrentCurrencyCode()
                        ) {
                            $quote->setStore($this->storeManager->getStore());
                            $this->quoteRepository->save($quote->collectTotals());
                            /*
                             * We mast to create new quote object, because collectTotals()
                             * can to create links with other objects.
                             */
                            $quote = $this->quoteRepository->get($subject->getQuoteId());
                        }

                        if ($quote->getTotalsCollectedFlag() === false) {
                            $quote->collectTotals();
                        }
                    } catch (NoSuchEntityException $e) {
                        $subject->setQuoteId(null);
                    }
                }

                if (!$subject->getQuoteId()) {
                    if ($this->customerSession->isLoggedIn()) {
                        $quoteBySubUser = $this->subUserRepository->getQuoteBySubUser($subUser);
                        if ($quoteBySubUser !== null) {
                            $subject->setQuoteId($quoteBySubUser->getId());
                            $quote = $quoteBySubUser;
                        }
                    } else {
                        $quote->setIsCheckoutCart(true);
                        $quote->setCustomerIsGuest(1);
                        $this->eventManager->dispatch('checkout_sub_quote_init', ['quote' => $quote]);
                    }
                }

                $quote->setStore($this->storeManager->getStore());
                $quote->setData('bss_is_sub_quote', $customerId);
                try {
                    $quote->setCustomer($this->customerSession->getCustomerDataObject());
                    $quote->save();
                    $subUser->setQuoteId($quote->getId());
                    $this->subUserRepository->save($subUser);
                } catch (\Exception $e) {
                }
                $this->_quote = $quote;
                $this->isLoading = false;
            }
            if (!$this->isQuoteMasked() && !$this->customerSession->isLoggedIn() && $subject->getQuoteId()) {
                $quoteId = $subject->getQuoteId();

                /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
                if ($quoteIdMask->getMaskedId() === null) {
                    $quoteIdMask->setQuoteId($quoteId)->save();
                }
                $this->setIsQuoteMasked(true);
            }
            $remoteAddress = $this->remoteAddress->getRemoteAddress();
            if ($remoteAddress) {
                $this->_quote->setRemoteIp($remoteAddress);
                $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
                $this->_quote->setXForwardedFor($xForwardIp);
            }

            return $this->_quote;
        }

        return $proceed();
    }

    /**
     * Check if logged in user is sub-user then create quote for this sub-user
     *
     * @param \Magento\Checkout\Model\Session $subject
     * @param callable $proceed
     * @return $this
     */
    public function aroundLoadCustomerQuote(\Magento\Checkout\Model\Session $subject, callable $proceed)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        if ($subUser) {
            try {
                if ($subUser->getQuoteId()) {
                    /** @var \Magento\Quote\Api\Data\CartInterface $subUserQuote */
                    $subUserQuote = $this->subUserRepository->getQuoteBySubUser($subUser);
                    if (!$subUserQuote) {
                        $subUserQuote = $this->quoteFactory->create();
                    }
                } else {
                    $subUserQuote = $this->quoteFactory->create();
                }
                $subUserQuote->setStoreId($this->helper->getStoreManager()->getStore()->getId());

                if ($subUserQuote->getId() && $subject->getQuoteId() != $subUserQuote->getId()) {
                    if ($subject->getQuoteId()) {
                        $subUserQuote->setCustomerIsGuest(0);
                        $this->quoteRepository->save(
                            $subUserQuote->merge($subject->getQuote()->collectTotals())
                        );
                        $newQuote = $this->quoteRepository->get($subUserQuote->getId());
                        $this->quoteRepository->save(
                            $newQuote->collectTotals()
                        );
                        $subUserQuote = $newQuote;

                        $subject->setQuoteId($subUserQuote->getId());
                        if ($this->_quote) {
                            $this->quoteRepository->delete($this->_quote);
                        }
                        $this->_quote = $subUserQuote;
                    }
                } else {
                    $subject->getQuote()->getBillingAddress();
                    $subject->getQuote()->getShippingAddress();
                    $subject->getQuote()->setCustomer($this->customerSession->getCustomerDataObject())
                        ->setCustomerIsGuest(0)
                        ->setTotalsCollectedFlag(false)
                        ->collectTotals();
                    $this->quoteRepository->save($subject->getQuote());
                }
            } catch (\Exception $e) {
                $this->helper->getMessageManager()->addErrorMessage($e->getMessage());
            }
        } else {
            return $proceed();
        }
        return $this;
    }

    /**
     * Flag whether or not the quote uses a masked quote id
     *
     * @param bool $isQuoteMasked
     * @return void
     * @codeCoverageIgnore
     */
    protected function setIsQuoteMasked($isQuoteMasked)
    {
        $this->isQuoteMasked = $isQuoteMasked;
    }

    /**
     * Return if the quote has a masked quote id
     *
     * @return bool|null
     * @codeCoverageIgnore
     */
    protected function isQuoteMasked()
    {
        return $this->isQuoteMasked;
    }
}
