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

use Bss\ProductStockAlert\Helper\MultiSourceInventory;
use Bss\ProductStockAlert\Model\ResourceModel\Stock;
use Bss\ProductStockAlert\Model\StockFactory;
use Exception;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Quote;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductData implements ResolverInterface
{
    /**
     * Const
     */
    const PRODUCT_STOCK_ALERT = 'product_stock_alert';
    const PRODUCT_STOCK_STATUS = 'product_stock_status';
    const HAS_EMAIL_SUBSCRIBED = 'has_email_subscribed';
    const PRODUCT_ID = 'product_id';
    const PARENT_ID = 'parent_id';
    const PRODUCT_TYPE = 'product_type';
    const CUSTOMER_EMAIL = 'customer_email';

    /**
     * @var MultiSourceInventory
     */
    protected $msiHelper;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var Status
     */
    protected $stockStatusResource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
    * @var Json
    */
    protected $json;

    /**
     * ProductData constructor.
     * @param MultiSourceInventory $multiSourceInventory
     * @param StockFactory $stockFactory
     * @param Stock $stockResource
     * @param Status $stockStatus
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     * @param GetCartForUser $getCartForUser
     * @param Json $json
     */
    public function __construct(
        MultiSourceInventory $multiSourceInventory,
        StockFactory $stockFactory,
        Stock $stockResource,
        Status $stockStatus,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory,
        GetCartForUser $getCartForUser,
        Json $json
    ) {
        $this->msiHelper = $multiSourceInventory;
        $this->stockFactory = $stockFactory;
        $this->stockResource = $stockResource;
        $this->stockStatusResource = $stockStatus;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
        $this->getCartForUser = $getCartForUser;
        $this->json = $json;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($args['cart_id']) || empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!isset($args['product_id'])) {
            throw new GraphQlInputException(__('Product ID is required'));
        }

        $currentUserId = $context->getUserId();
        $store = $context->getExtensionAttributes()->getStore();
        $websiteId = (int) $store->getWebsiteId();
        $productId = $args['product_id'];
        $maskedCartId = $args['cart_id'];
        $storeId = (int) $store->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $productData = $this->getProductData($productId, $websiteId, $currentUserId, $cart);

        return $this->valueFactory->create(
            function () use ($productData) {
                if (is_array($productData) &&
                    !isset($productData['product_stock_alert'])) {
                    return ['product_data' => $productData];
                }
                return ['product_data' => [$productData]];
            }
        );
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @param int|string $customerId
     * @param Quote $cart
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData($productId, $websiteId, $customerId, $cart): array
    {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $customerId ? $this->customerRepository->getById($customerId) : null;

            $website = $this->storeManager->getWebsite($websiteId);
            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($website->getId(), $stockResolver, $salableQty);
            $productType = $product->getTypeId();
            $result = [];

            if ($this->checkProductType($productType, 'simple')) {
                $result = $this->buildSimple($product, $customer, $stockId, $website->getId(), $salableQty, $cart);
            } elseif ($this->checkProductType($productType, 'configurable')) {
                $result = $this->buildConfigurable($product, $customer, $stockId, $website->getId(), $salableQty, $cart);
            } elseif ($this->checkProductType($productType, 'grouped')) {
                $result = $this->buildGrouped($product, $customer, $stockId, $website->getId(), $salableQty, $cart);
            } elseif ($this->checkProductType($productType, 'bundle')) {
                $result = $this->buildBundle($product, $customer, $stockId, $website->getId(), $salableQty, $cart);
            }
            return $result;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

    /**
     * @param int $websiteId
     * @param null|StockResolverInterface $stockResolver
     * @param null|GetProductSalableQtyInterface $salableQty
     * @return int|null
     */
    protected function getStockId(
        $websiteId,
        $stockResolver,
        $salableQty
    ): int {
        try {
            $wsCode = $this->storeManager->getWebsite($websiteId)->getCode();
            if ($stockResolver && $stockResolver instanceof StockResolverInterface &&
                $salableQty && $salableQty instanceof GetProductSalableQtyInterface) {
                return $stockResolver->execute('website', $wsCode)->getStockId();
            }
            return 0;
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @param string $type
     * @param string $compareType
     * @return bool
     */
    protected function checkProductType($type, $compareType): bool
    {
        if ($compareType == "simple") {
            return in_array($type, ['simple', 'virtual', 'downloadable']);
        }
        return $type == $compareType;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface|null $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @param Quote $cart
     * @return array
     * @throws LocalizedException
     */
    public function buildSimple($product, $customer, $stockId, $websiteId, $salableQty, $cart): array
    {
        $productId = $product->getId();
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $isInStock = $this->isInStock(
            $product->getSku(),
            $stockItem->getIsInStock(),
            $stockId,
            $salableQty
        );
        if (!$isInStock && $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->hasEmail($customer, $product, $websiteId, $cart);

            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $productId,
                self::PARENT_ID => $productId,
                self::PRODUCT_TYPE => 'simple',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $this->getEmail($customer, $productId, $cart)
            ];
        }
        return [];
    }

    /**
     * @param string $sku
     * @param bool $childStock
     * @param int $stockId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @return bool
     */
    protected function isInStock(
        $sku,
        $childStock,
        $stockId,
        $salableQty
    ): bool {
        if (!$stockId) {
            return $childStock;
        }
        try {
            return (bool)$salableQty->execute($sku, (int)$stockId);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param ProductInterface $product
     */
    protected function isProductEnabledNotice($product): bool
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            return (bool)$product->getCustomAttribute('product_stock_alert')->getValue();
        }
        return true;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface|null $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @param Quote $cart
     * @return array
     * @throws LocalizedException
     */
    public function buildConfigurable($product, $customer, $stockId, $websiteId, $salableQty, $cart): array
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            $productId = $product->getId();
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $productId,
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $productId,
                self::PARENT_ID => $productId,
                self::PRODUCT_TYPE => 'configurable',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $this->getEmail($customer, $productId, $cart)
            ];
        }
        /** @var Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getUsedProductCollection($product);
        $childItems->addAttributeToSelect('product_stock_alert');
        $this->stockStatusResource->addStockDataToCollection($childItems, false);
        $renderData = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock &&
                $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->hasEmail($customer, $childItem, $websiteId, $cart);
                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem['entity_id'],
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'configurable',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $this->getEmail($customer, $childItem['entity_id'], $cart)
                ];
            }
        }
        return $renderData;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface|null $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @param Quote $cart
     * @return array
     * @throws LocalizedException
     */
    public function buildGrouped($product, $customer, $stockId, $websiteId, $salableQty, $cart): array
    {
        if (!$product->isAvailable() && $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'grouped',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $this->getEmail($customer, $product->getId(), $cart)
            ];
        }
        /** @var Grouped $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getAssociatedProductCollection($product);
        $childItems->addAttributeToSelect(
            'product_stock_alert'
        );
        $renderData = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock && $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->hasEmail($customer, $childItem, $websiteId, $cart);
                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem->getId(),
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'grouped',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $this->getEmail($customer, $childItem->getId(), $cart)
                ];
            }
        }
        return $renderData;
    }

    /**
     * @param ProductInterface $product
     * @param CustomerInterface|null $customer
     * @param int $stockId
     * @param int $websiteId
     * @param GetProductSalableQtyInterface|null $salableQty
     * @param Quote $cart
     * @return array
     * @throws LocalizedException
     */
    public function buildBundle($product, $customer, $stockId, $websiteId, $salableQty, $cart): array
    {
        if (!$product->getExtensionAttributes()->getStockItem()->getIsInStock() &&
            $this->isProductEnabledNotice($product)) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
            return [
                self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($product),
                self::PRODUCT_STOCK_STATUS => false,
                self::PRODUCT_ID => $product->getId(),
                self::PARENT_ID => $product->getId(),
                self::PRODUCT_TYPE => 'bundle',
                self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                self::CUSTOMER_EMAIL => $this->getEmail($customer, $product->getId(), $cart)
            ];
        }
        /** @var Type $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );
        $selectionItems = $productTypeInstance->getSelectionsCollection(
            $productTypeInstance->getOptionsIds($product),
            $product
        )->addFieldToSelect(
            'product_id'
        )->addFieldToSelect(
            'option_id'
        )->addFieldToSelect(
            'selection_id'
        )->addAttributeToSelect(
            'product_stock_alert'
        );
        $selectionItems->getSelect()->joinInner(
            ['bundleOption' => $selectionItems->getTable('catalog_product_bundle_option')],
            'selection.option_id = bundleOption.option_id',
            ['type']
        );
        $renderData = [];
        foreach ($selectionItems as $childItem) {
            $isInStock = $this->isInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock && $this->isProductEnabledNotice($childItem)) {
                $hasEmail = $this->hasEmail($customer, $childItem, $websiteId, $cart);

                $renderData[] = [
                    self::PRODUCT_STOCK_ALERT => $this->isProductEnabledNotice($childItem),
                    self::PRODUCT_STOCK_STATUS => false,
                    self::PRODUCT_ID => $childItem->getId(),
                    self::PARENT_ID => $product->getId(),
                    self::PRODUCT_TYPE => 'bundle',
                    self::HAS_EMAIL_SUBSCRIBED => (bool)$hasEmail,
                    self::CUSTOMER_EMAIL => $this->getEmail($customer, $childItem->getId(), $cart)
                ];
            }
        }
        return $renderData;
    }

    /**
     * @param CustomerInterface|null $customer
     * @param ProductInterface $product
     * @param int $websiteId
     * @param Quote $cart
     * @return bool
     */
    private function hasEmail($customer, $product, $websiteId, $cart): bool
    {
        if ($customer) {
            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );
        } else {
            $stockAlert = $cart->getStockAlert() ? $this->json->unserialize($cart->getStockAlert()) : [];
            $productId = $product->getId();
            $hasEmail = false;

            if ($stockAlert && isset($stockAlert[$productId])) {
                $hasEmail = true;
            }
        }

        return $hasEmail;
    }

    /**
     * @param CustomerInterface|null $customer
     * @param int $productId
     * @param Quote $cart
     * @return string
     */
    private function getEmail($customer, $productId, $cart): string
    {
        $email = '';

        if ($customer) {
            $email = $customer->getEmail();
        } else {
            $stockAlert = $cart->getStockAlert() ? $this->json->unserialize($cart->getStockAlert()) : [];

            if ($stockAlert && isset($stockAlert[$productId])) {
                $email = $stockAlert[$productId]['email'];
            }
        }

        return $email;
    }
}
