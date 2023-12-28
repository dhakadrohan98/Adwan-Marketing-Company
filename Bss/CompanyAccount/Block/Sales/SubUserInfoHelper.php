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
namespace Bss\CompanyAccount\Block\Sales;

use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;

/**
 * Class SubUserInfoHelper
 *
 * @package Bss\CompanyAccount\Block\Sales
 */
class SubUserInfoHelper
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $userOrderRepository;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * SubUserInfoHelper constructor.
     *
     * @param Data $helper
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        Data $helper,
        SubUserOrderRepositoryInterface $userOrderRepository,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->helper = $helper;
        $this->userOrderRepository = $userOrderRepository;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Get SubUser information
     *
     * @param int $orderId
     * @return bool|array
     */
    public function getSubUserInfo($orderId)
    {
        try {
            if ($this->helper->isEnable()) {
                /** @var \Bss\CompanyAccount\Api\Data\SubUserOrderInterface $subUserOrder */
                $subUserOrder = $this->userOrderRepository->getByOrderId($orderId);
                if ($subUserOrder) {
                    return $subUserOrder->getSubUserInfo();
                }
            }
        } catch (\Exception $exception) {
            $this->helper->getMessageManager()->addErrorMessage($exception->getMessage());
        }
        return false;
    }
}
