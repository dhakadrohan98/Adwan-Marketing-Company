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
use Bss\ProductStockAlertGraphQl\Model\Product\Validate as ProductValidate;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Bss\ProductStockAlertGraphQl\Model\Resolver\ProductData;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\CustomerFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Subscribe implements ResolverInterface
{
    /**
     * @var MultiSourceInventory
     */
    protected $msiHelper;

    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var ProductValidate
     */
    protected $productValidate;

    /**
     * @var Stock
     */
    protected $stockResource;

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
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Subscribe constructor.
     * @param MultiSourceInventory $multiSourceInventory
     * @param StockFactory $stockFactory
     * @param ProductValidate $productValidate
     * @param Stock $stockResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param JsonSerialize $jsonSerialize
     * @param ValueFactory $valueFactory
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param ProductData $productData
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        MultiSourceInventory $multiSourceInventory,
        StockFactory $stockFactory,
        ProductValidate $productValidate,
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        JsonSerialize $jsonSerialize,
        ValueFactory $valueFactory,
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        ProductData $productData,
        CustomerFactory $customerFactory
    ) {
        $this->msiHelper = $multiSourceInventory;
        $this->stockFactory = $stockFactory;
        $this->productValidate = $productValidate;
        $this->stockResource = $stockResource;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonSerialize = $jsonSerialize;
        $this->valueFactory = $valueFactory;
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->productData = $productData;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
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
        $resultData = [];
        $currentUserId = $context->getUserId();
        /**
         * @var \Magento\GraphQl\Model\Query\ContextInterface $context
         */

        if (!isset($args['cart_id']) || empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $this->validate($args, $resultData);

        $store = $context->getExtensionAttributes()->getStore();
        $productId = $args['product_id'];
        $parentId = $args['parent_id'];
        $email = $args['email'];
        $maskedCartId = $args['cart_id'];
        $websiteId = (int) $store->getWebsiteId();
        $storeId = (int)$store->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        if ($email &&
            strlen($email) > 1 &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultData[] = [
                'message' => __('Please correct the email address: %EMAIL'),
                'params' => $this->jsonSerialize->serialize([
                    'EMAIL' => $email
                ])
            ];
        }

        $dataRender = $resultData;
        if (empty($resultData)) {
            $dataRender = $this->subscribeStockNotice(
                $productId,
                $parentId,
                $websiteId,
                $email,
                $currentUserId,
                $resultData,
                $cart
            );
        }
        return $this->valueFactory->create(
            function () use ($dataRender) {
                return $dataRender;
            }
        );
    }

    /**
     * @param array $args
     * @param array $resultData
     */
    protected function validate(array $args, array &$resultData): void
    {
        if (!isset($args['product_id'])) {
            $resultData[] = [
                'message' => __('Product ID is required'),
                'params' => ''
            ];
        }
        if (!isset($args['parent_id'])) {
            $resultData[] = [
                'message' => __('Parent ID is required'),
                'params' => ''
            ];
        }
    }

    /**
     * @param $productId
     * @param $parentId
     * @param $websiteId
     * @param $email
     * @param $customerId
     * @param $resultData
     * @param Quote $cart
     * @return array
     */
    public function subscribeStockNotice(
        $productId,
        $parentId,
        $websiteId,
        $email,
        $customerId,
        $resultData,
        $cart
    ): array {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $customerId ? $this->getCustomer($customerId) : null;
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if ($customer && (!$email || strlen($email) === 0 || $email === '')) {
                $email = $customer->getEmail();
            }

            if (!$this->productValidate->validateChildProduct($product, $parent)) {
                $resultData[] = [
                    'message' => __('Product ID %PRODUCT is not child of product ID %PARENT_ID'),
                    'params' => $this->jsonSerialize->serialize([
                        'PRODUCT' => $productId,
                        'PARENT_ID' => $parent->getId()
                    ])
                ];
            }

            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($websiteId, $stockResolver, $salableQty);
            $isInStock = $this->isInStock(
                $product->getSku(),
                $product->getIsSalable(),
                $stockId,
                $salableQty
            );
            if ($isInStock || !$this->isProductEnabledNotice($product)) {
                $resultData[] = [
                    'message' => __('Product with sku %SKU in website %WEBSITE is not allow to subscribe a stock notice right now.'),
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            if ($customer) {
                $hasEmail = $this->stockResource->hasEmail(
                    $customerId,
                    $productId,
                    $websiteId
                );
            } else {
                $stockAlert = $cart->getStockAlert() ? $this->jsonSerialize->unserialize($cart->getStockAlert()) : [];
                $hasEmail = false;

                if ($stockAlert && isset($stockAlert[$productId])) {
                    $hasEmail = true;
                }
            }

            if ($hasEmail) {
                $resultData[] = [
                    'message' => __('You already subscribed for product %SKU in website %WEBSITE.'),
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            if (empty($resultData)) {
                $customerIdUpdate = $customer ? $customer->getId() : 0;
                $customerName = $customer ? $customer->getName() : "Guest";

                $model = $this->stockFactory->create()
                    ->setCustomerId($customerIdUpdate)
                    ->setCustomerEmail($email)
                    ->setCustomerName($customerName)
                    ->setProductSku($product->getSku())
                    ->setProductId($productId)
                    ->setWebsiteId(
                        $websiteId
                    )
                    ->setStoreId(
                        $website->getDefaultStore()->getId()
                    )
                    ->setParentId($parent->getId());
                $model->save();

                $alert = $cart->getStockAlert() ? $this->jsonSerialize->unserialize($cart->getStockAlert()) : [];

                if (!$customer && !isset($alert[$productId])) {
                    $alert[$productId] = [
                        "email" => $email,
                        "website" => $websiteId
                    ];
                    $cart->setStockAlert($this->jsonSerialize->serialize($alert));
                    $this->cartRepository->save($cart);
                }

                $resultData[] = [
                    'message' => __('Alert subscription has been saved.'),
                    'params' => 'success'
                ];
            }
        } catch (NoSuchEntityException $noEntityException) {
            $resultData[] = [
                'message' => __('There are not enough parameters.'),
                'params' => ''
            ];
        } catch (AlreadyExistsException $alreadyExistsException) {
            $resultData[] = [
                'message' => __('This email has been subscribed.'),
                'params' => ''
            ];
        } catch (Exception $exception) {
            $resultData[] = [
                'message' => $exception->getMessage(),
                'params' => ''
            ];
        }
        return $resultData;
    }

    /**
     * @param int $websiteId
     * @param null|StockResolverInterface $stockResolver
     * @param null|GetProductSalableQtyInterface $salableQty
     * @return int
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
     * @param string $sku
     * @param bool $childStock
     * @param int $stockId
     * @param GetProductSalableQtyInterface $salableQty
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
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    protected function isProductEnabledNotice($product): bool
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            return (bool)$product->getCustomAttribute('product_stock_alert')->getValue();
        }
        return true;
    }

    /**
     * @param int|string $currentUserId
     * @return Magento\Customer\Model\Customer
     */
    private function getCustomer($currentUserId)
    {
        $currentUser = $this->customerRepository->getById($currentUserId);
        $customerId = $currentUser->getId();
        $customer = $this->customerFactory->create()->load($customerId);

        return $customer;
    }
}
