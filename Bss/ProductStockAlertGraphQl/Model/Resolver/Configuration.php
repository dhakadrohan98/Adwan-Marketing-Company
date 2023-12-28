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
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Helper\Data;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Configuration implements ResolverInterface
{
    /**
     * const
     */
    const XML_PATH_STOCK_ALLOW = 'allow_stock';
    const XML_PATH_CUSTOMER_ALLOW = 'allow_customer';
    const XML_PATH_EMAIL_SEND_BASED_QTY = 'email_based_qty';
    const XML_PATH_SEND_LIMIT = 'send_limit';
    const XML_PATH_QTY_ALLOW = 'allow_stock_qty';
    const XML_PATH_NOTIFICATION_MESSAGE = 'message';
    const XML_PATH_STOP_NOTIFICATION_MESSAGE = 'stop_message';
    const XML_BUTTON_DESIGN_BUTTON_TEXT = 'button_text';
    const XML_BUTTON_DESIGN_STOP_BUTTON_TEXT = 'stop_button_text';
    const XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR = 'button_text_color';
    const XML_BUTTON_DESIGN_BUTTON_COLOR = 'button_color';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param ValueFactory $valueFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        GroupRepositoryInterface $groupRepository,
        ValueFactory $valueFactory,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->groupRepository = $groupRepository;
        $this->valueFactory = $valueFactory;
        $this->storeManager = $storeManager;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $configuration = [];

        $customerGroupId = 0;
        $currentUserId = $context->getUserId();
        $allowCustomer = $this->scopeConfig->getValue(
            Data::XML_PATH_CUSTOMER_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($currentUserId) {
            $customer = $this->customerRepositoryInterface->getById($currentUserId);
            $customerGroupId = (int) $customer->getGroupId();
        }

        $configuration[self::XML_PATH_STOCK_ALLOW] = (bool) $this->scopeConfig->getValue(
            Data::XML_PATH_STOCK_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        ) && in_array($customerGroupId, explode(',', $allowCustomer));

        $filter = $this->filterBuilder
            ->setValue(explode(',', $allowCustomer))
            ->setField('customer_group_id')
            ->setConditionType('in')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $customerGroups = $this->groupRepository->getList($searchCriteria);
        $customerGroupsArr = [];
        /** @var GroupInterface $group */
        array_map(function ($group) use (&$customerGroupsArr) {
            $customerGroupsArr[] = $group->getCode();
        }, $customerGroups->getItems());

        $configuration[self::XML_PATH_CUSTOMER_ALLOW] = $customerGroupsArr;
        $configuration[self::XML_PATH_EMAIL_SEND_BASED_QTY] = (bool)$this->scopeConfig->getValue(
            Data::XML_PATH_EMAIL_SEND_BASED_QTY,
            ScopeInterface::SCOPE_WEBSITE
        );

        $message = $this->scopeConfig->getValue(
            Data::XML_PATH_NOTIFICATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        !$message ? $message = __('Notify me when this product is in stock') : null;
        $configuration[self::XML_PATH_NOTIFICATION_MESSAGE] = $message;

        $stopMessage = $this->scopeConfig->getValue(
            Data::XML_PATH_STOP_NOTIFICATION_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        !$stopMessage ? $stopMessage = __('Stop Notify this product') : null;
        $configuration[self::XML_PATH_STOP_NOTIFICATION_MESSAGE] = $stopMessage;

        $configuration[self::XML_PATH_SEND_LIMIT] = (int)$this->scopeConfig->getValue(
            Data::XML_PATH_SEND_LIMIT,
            ScopeInterface::SCOPE_WEBSITE
        );
        $configuration[self::XML_PATH_QTY_ALLOW] = (int)$this->scopeConfig->getValue(
            Data::XML_PATH_QTY_ALLOW,
            ScopeInterface::SCOPE_WEBSITE
        );

        $buttonText = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        !$buttonText ? $buttonText = __('Notify Me') : null;
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_TEXT] = $buttonText;

        $stopButtonText = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        !$stopButtonText ? $stopButtonText = __('Stop notify') : null;
        $configuration[self::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT] = $stopButtonText;

        $buttonTextColor = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        !$buttonTextColor ? $buttonTextColor = '#FFFFFF' : null;
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR] = $buttonTextColor;

        $buttonColor = $this->scopeConfig->getValue(
            Data::XML_BUTTON_DESIGN_BUTTON_COLOR,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        !$buttonColor ? $buttonColor = '#2D7DB3' : null;
        $configuration[self::XML_BUTTON_DESIGN_BUTTON_COLOR] = $buttonColor;

        return $this->valueFactory->create(function () use ($configuration) {
            return $configuration;
        });
    }
}
