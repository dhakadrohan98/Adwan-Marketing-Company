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
namespace Bss\CompanyAccount\Observer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserOrderInterface;
use Bss\CompanyAccount\Api\Data\SubUserOrderInterfaceFactory;
use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\QuoteExtensionFactory;
use Bss\CompanyAccount\Model\ResourceModel\QuoteExtension\CollectionFactory as QuoteExtensionCollection;
use Bss\CompanyAccount\Model\ResourceModel\SubUserOrder\CollectionFactory as SubUserOrderCollection;
use Bss\CompanyAccount\Model\SubUserOrderService;
use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class OrderPlaced
 *
 * @package Bss\CompanyAccount\Observer
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class OrderPlaced implements ObserverInterface
{
    /**
     * @var QuoteExtensionCollection
     */
    protected $quoteExtensionCollecion;
    /**
     * @var QuoteExtensionFactory
     */
    protected $quoteExtension;
    /**
     * @var State
     */
    protected $sate;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var SubUserOrderCollection
     */
    protected $subUserCollection;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubUserOrderInterfaceFactory
     */
    private $userOrderFactory;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $userOrderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * OrderPlaced constructor.
     *
     * @param QuoteExtensionCollection $quoteExtensionCollecion
     * @param QuoteExtensionFactory $quoteExtension
     * @param State $sate
     * @param CartRepositoryInterface $quoteRepository
     * @param SubUserOrderCollection $subUserCollection
     * @param LoggerInterface $logger
     * @param Json $serializer
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserOrderInterfaceFactory $userOrderFactory
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     * @param Data $helper
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        QuoteExtensionCollection $quoteExtensionCollecion,
        QuoteExtensionFactory $quoteExtension,
        State $sate,
        CartRepositoryInterface $quoteRepository,
        SubUserOrderCollection $subUserCollection,
        LoggerInterface $logger,
        Json $serializer,
        SubUserRepositoryInterface $subUserRepository,
        SubUserOrderInterfaceFactory $userOrderFactory,
        SubUserOrderRepositoryInterface $userOrderRepository,
        Data $helper
    ) {
        $this->quoteExtensionCollecion = $quoteExtensionCollecion;
        $this->quoteExtension = $quoteExtension;
        $this->sate = $sate;
        $this->quoteRepository = $quoteRepository;
        $this->subUserCollection = $subUserCollection;
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->userOrderFactory = $userOrderFactory;
        $this->userOrderRepository = $userOrderRepository;
        $this->logger = $logger;
        $this->subUserRepository = $subUserRepository;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getOrder();
        if ($this->sate->getAreaCode() == "adminhtml") {
            $this->saveOrderQuoteBackend($order);
        } else {
            /** @var SubUserInterface $subUser */
            $subUser = $this->customerSession->getSubUser();
            try {
                if ($this->helper->isEnable() && $subUser) {
                    /** @var SubUserOrderInterface $userOrder */
                    $subUserId = $subUser->getSubId();
                    $orderId = $order->getEntityId();
                    if (!$this->checkExitsSubUserOrder($subUserId, $orderId)) {
                        $this->saveSubUserOrder($subUser, $order);
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Save Sub User Order
     *
     * @param SubUserInterface $subUser
     * @param OrderInterface $order
     * @deprecated v1.0.7 use SubUserOrderService instead
     * @see SubUserOrderService
     */
    public function saveSubUserOrder($subUser, $order)
    {
        try {
            $subUserId = $subUser->getSubId();
            $orderId = $order->getEntityId();
            $userOrder = $this->userOrderFactory->create();
            $userOrder->setSubId($subUserId);
            $userOrder->setOrderId($orderId);
            $userOrder->setGrandTotal($order->getBaseGrandTotal());
            $subUser = $this->subUserRepository->getById($subUserId);
            $subUserInfo[SubUserInterface::NAME] = $subUser->getSubName();
            $subUserInfo[SubUserInterface::EMAIL] = $subUser->getSubEmail();
            $subUserInfo['role_name'] = $subUser->getData('role_name');
            $userOrder->setSubUserInfo(
                $this->serializer->serialize($subUserInfo)
            );
            $this->userOrderRepository->save($userOrder);
        } catch (CouldNotSaveException $e) {
            $this->addErrorMsg($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->addErrorMsg(__('Something went wrong. Please try again later.'));
        }
    }

    /**
     * Add error message
     *
     * @param string $text
     */
    protected function addErrorMsg($text)
    {
        $this->helper->getMessageManager()->addErrorMessage($text);
    }

    /**
     * Check exits sub user order
     *
     * @param string $subUserId
     * @param string $orderId
     * @return bool
     * @deprecated v1.0.7 use SubUserOrderService instead
     * @see SubUserOrderService
     */
    public function checkExitsSubUserOrder($subUserId, $orderId)
    {
        $subUserCollection = $this->subUserCollection->create()
            ->addFieldToFilter('sub_id', $subUserId)
            ->addFieldToFilter('order_id', $orderId);
        if ($subUserCollection->getSize() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Save table order_created_by_admin
     *
     * @param OrderInterface $order
     */
    public function saveOrderQuoteBackend($order)
    {
        try {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getQuoteExtension()) {
                $quoteExtensionCollection = $this->quoteExtensionCollecion->create()
                    ->addFieldToFilter('backend_quote_id', $quoteId)->getLastItem();
                $subUserId = $quoteExtensionCollection->getSubId();
                if ($subUserId) {
                    $subUser = $this->subUserRepository->getById($subUserId);
                    $this->saveSubUserOrder($subUser, $order);
                }
            }
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
}
