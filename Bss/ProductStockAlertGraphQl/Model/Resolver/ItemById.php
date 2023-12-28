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

use Bss\ProductStockAlert\Model\ResourceModel\Stock;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ItemById implements ResolverInterface
{
    /**
     * Const
     */
    const ALERT_STOCK_ID = 'alert_stock_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_NAME = 'customer_name';
    const PRODUCT_SKU = 'product_sku';
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const ADD_DATE = 'add_date';
    const SEND_DATE = 'send_date';
    const SEND_COUNT = 'send_count';
    const STATUS = 'status';
    const PARENT_ID = 'parent_id';
    const STORE_ID = 'store_id';

    const COLUMNS = [
        self::ALERT_STOCK_ID,
        self::CUSTOMER_ID,
        self::CUSTOMER_EMAIL,
        self::CUSTOMER_NAME,
        self::PRODUCT_SKU,
        self::PRODUCT_ID,
        self::WEBSITE_ID,
        self::ADD_DATE,
        self::SEND_DATE,
        self::SEND_COUNT,
        self::STATUS,
        self::PARENT_ID,
        self::STORE_ID,
    ];

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * ItemById constructor.
     * @param Stock $stockResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ValueFactory $valueFactory
    ) {
        $this->stockResource = $stockResource;
        $this->customerRepository = $customerRepository;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $currentUserId = $context->getUserId();
        /**
         * @var \Magento\GraphQl\Model\Query\ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }

        if (!isset($args['stock_id'])) {
            throw new GraphQlInputException(__('Stock ID is required'));
        }
        $stockId = $args['stock_id'];

        $stockNotice = $this->getById($stockId, $currentUserId);

        if (empty($stockNotice) || !$stockNotice) {
            throw new GraphQlNoSuchEntityException(__('We cannot find any record match with ID = %1', $stockId));
        }

        return $this->valueFactory->create(
            function () use ($stockNotice) {
                if (isset($stockNotice['status'])) {
                    $stockStatus = $stockNotice['status'];
                    $stockNotice['status'] = $stockStatus == "0" || !$stockStatus ? __('PENDING') : __('SENT');
                }
                return ['items' => [$stockNotice]];
            }
        );
    }

    /**
     * @param $stockId
     * @param $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($stockId, $customerId): array
    {
        $customer = $this->customerRepository->getById($customerId);
        $conditions = [
            'alert_stock_id' => $stockId,
            'customer_id' => $customer->getId()
        ];
        $columns = self::COLUMNS;
        $stockList = $this->stockResource->getStockNotice($conditions, $columns);
        $stockItem = $stockList[array_key_first($stockList)] ?? [];
        return $stockItem;
    }
}
