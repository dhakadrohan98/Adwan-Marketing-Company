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
namespace Bss\CompanyAccount\Block\SubUser;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory as Collection;
use Magento\Framework\View\Element\Template;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Block\SubUser
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var Collection
     */
    private $subUserCollection;

    /**
     * Index constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param SubUserRepositoryInterface $subUserRepository
     * @param Collection $subUserCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        SubUserRepositoryInterface $subUserRepository,
        Collection $subUserCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->subUserRepository = $subUserRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->subUserCollection = $subUserCollection;
        parent::__construct($context, $data);
    }

    /**
     * Manage sub-user constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $collection = $this->subUserCollection->create();

        $collection->addFieldToFilter('customer_id', $this->currentCustomer->getCustomerId())
        ->addOrder('sub_id', 'desc');
        $this->setItems($collection);
    }

    /**
     * Enter description here...
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'companyaccount.subuser.index'
        )->setCollection(
            $this->getItems()
        )->setPath('companyaccount/subuser/');
        $this->setChild('pager', $pager);
        $this->getItems()->load();

        return $this;
    }

    /**
     * Get edit link
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @return string
     */
    public function getEditUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/edit', ['sub_id' => $subUser->getSubId()]);
    }

    /**
     * Get create url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('companyaccount/subuser/create');
    }

    /**
     * Get delete link
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @return string
     */
    public function getDeleteUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/delete', ['sub_id' => $subUser->getSubId()]);
    }

    /**
     * Get reset password link
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     *
     * @return string
     */
    public function getResetPasswordUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/resetpassword', ['sub_id' => $subUser->getSubId()]);
    }
}
