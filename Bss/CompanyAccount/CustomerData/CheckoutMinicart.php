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
namespace Bss\CompanyAccount\CustomerData;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManager;

/**
 * Class Checkout Mini cart
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class CheckoutMinicart implements SectionSourceInterface
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var SessionManager
     */
    private $coreSession;

    /**
     * CheckoutMinicart constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     * @param SessionManager $coreSession
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        SessionManager $coreSession
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->coreSession = $coreSession;
    }

    /**
     * Disable button Checkout mini-cart & check is approved quote
     *
     * @return bool[]|false[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSectionData()
    {
        $output = [];
        $this->coreSession->getApproveQuoteId() !== null ?
            $output['approved_quote'] = true : $output['approved_quote'] = false;
        $output['check_order_role'] = !(bool)$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER);
        return $output;
    }
}
