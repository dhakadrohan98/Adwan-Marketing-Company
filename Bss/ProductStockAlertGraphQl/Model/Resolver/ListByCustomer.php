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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;

class ListByCustomer implements ResolverInterface
{
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
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * ListByCustomer constructor.
     * @param Stock $stockResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param ValueFactory $valueFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ImageFactory $imageHelperFactory
     */
    public function __construct(
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ValueFactory $valueFactory,
        ProductRepositoryInterface $productRepository,
        ImageFactory $imageHelperFactory
    ) {
        $this->stockResource = $stockResource;
        $this->customerRepository = $customerRepository;
        $this->valueFactory = $valueFactory;
        $this->productRepository = $productRepository;
        $this->imageHelperFactory = $imageHelperFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        $stockNoticeList = $this->getListByCustomer($currentUserId);

        return $this->valueFactory->create(
            function () use ($stockNoticeList) {
                array_walk($stockNoticeList, function (&$item) {
                    $product = $this->productRepository->getById($item['product_id']);
                    $item['status'] = $product->getIsSalable() ? __("In Stock") : __("Out Of Stock");
                    $item['product_name'] = $product->getName();
                    $item['product_image'] = $product->getSmallImage();
                    $item['product_url'] = $product->getUrlKey();
                });
                return ['items' => $stockNoticeList];
            }
        );
    }

    /**
     * @param $customerId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getListByCustomer($customerId): array
    {
        $customer = $this->customerRepository->getById($customerId);
        $conditions = [
            'customer_id' => $customer->getId()
        ];
        $columns = ItemById::COLUMNS;
        $stockList = $this->stockResource->getStockNotice($conditions, $columns);

        return $stockList;
    }
}
