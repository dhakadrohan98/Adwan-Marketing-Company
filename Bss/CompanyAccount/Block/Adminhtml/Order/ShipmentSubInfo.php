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

namespace Bss\CompanyAccount\Block\Adminhtml\Order;

use Bss\CompanyAccount\Block\Sales\SubUserInfoHelper;
use Magento\Backend\Block\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class ShipmentSubInfo
 *
 * @package Bss\CompanyAccount\Block\Adminhtml\Order
 */
class ShipmentSubInfo extends Template
{
    /**
     * @var SubUserInfoHelper
     */
    private $subUserInfoHelper;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * ShipmentSubInfo constructor.
     *
     * @param SubUserInfoHelper $subUserInfoHelper
     * @param Template\Context $context
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param array $data
     */
    public function __construct(
        SubUserInfoHelper $subUserInfoHelper,
        Template\Context $context,
        ShipmentRepositoryInterface $shipmentRepository,
        array $data = []
    ) {
        $this->subUserInfoHelper = $subUserInfoHelper;
        $this->shipmentRepository = $shipmentRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserOrderInterface
     */
    public function getSubUserInfo()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            return $this->subUserInfoHelper->getSubUserInfo($shipment->getOrderId());
        } catch (NoSuchEntityException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
