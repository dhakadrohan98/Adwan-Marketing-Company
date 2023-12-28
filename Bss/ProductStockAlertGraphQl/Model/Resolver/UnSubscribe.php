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
use Bss\ProductStockAlert\Model\StockFactory;
use Bss\ProductStockAlertGraphQl\Model\Product\Validate as ProductValidate;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;

class UnSubscribe implements ResolverInterface
{
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
     * UnSubscribe constructor.
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
     */
    public function __construct(
        StockFactory $stockFactory,
        ProductValidate $productValidate,
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        JsonSerialize $jsonSerialize,
        ValueFactory $valueFactory,
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository
    ) {
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
        $resultData = [];
        $currentUserId = $context->getUserId();
        if (!isset($args['cart_id']) || empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $this->validate($args, $resultData);
        $productId = $args['product_id'];
        $parentId = $args['parent_id'];
        $maskedCartId = $args['cart_id'];

        $store = $context->getExtensionAttributes()->getStore();
        $websiteId = (int) $store->getWebsiteId();
        $storeId = (int) $store->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $dataRender = $this->unsubscribeStockNotice(
            $productId,
            $parentId,
            $websiteId,
            $currentUserId,
            $resultData,
            $cart
        );
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
     * @param int $productId
     * @param int $parentId
     * @param int $websiteId
     * @param int|null $customerId
     * @param array $resultData
     * @param Quote $cart
     * @return array
     */
    public function unsubscribeStockNotice(
        $productId,
        $parentId,
        $websiteId,
        $customerId,
        $resultData,
        $cart
    ): array {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $customerId ? $this->customerRepository->getById($customerId) : null;
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if (!$this->productValidate->validateChildProduct($product, $parent)) {
                $resultData[] = [
                    'message' => __('Product ID %PRODUCT is not child of product ID %PARENT_ID'),
                    'params' => $this->jsonSerialize->serialize([
                        'PRODUCT' => $productId,
                        'PARENT_ID' => $parentId
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

            if (!$hasEmail) {
                $resultData[] = [
                    'message' => __('You did not subscribe on product %SKU in website %WEBSITE.'),
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            if (empty($resultData)) {
                $stockModel = $this->doLoadModel($customer, $product, $parent, $websiteId, $cart);

                if ($stockModel && $stockModel->getAlertStockId()) {
                    $stockModel->delete();
                    $resultData[] = [
                        'message' => __('You will no longer receive stock alerts for this product.'),
                        'params' => 'success'
                    ];
                } else {
                    $resultData[] = [
                        'message' => __('We could not find any record match with product %SKU in website %WEBSITE.'),
                        'params' => $this->jsonSerialize->serialize([
                            'SKU: ' => $product->getSku(),
                            'WEBSITE' => $website->getCode()
                        ])
                    ];
                }
            }
        } catch (Exception $exception) {
            $resultData[] = [
                'message' => $exception->getMessage(),
                'params' => ''
            ];
        }
        return $resultData;
    }

    /**
     * @param Customer|null $customer
     * @param Product $product
     * @param Product $parent
     * @param int $websiteId
     * @param Quote $cart
     * @return \Bss\ProductStockAlert\Model\Stock
     */
    private function doLoadModel($customer, $product, $parent, $websiteId, $cart)
    {
        $stockModel = null;
        $productId = $product->getId();

        if ($customer) {
            $stockModel = $this->stockFactory->create()
                ->setCustomerId($customer->getId())
                ->setProductId($productId)
                ->setWebsiteId(
                    $websiteId
                )->setStoreId(
                    $product->getStoreId()
                )->setParentId(
                    $parent->getId()
                )
                ->loadByParam();
        } else {
            $stockAlert = $cart->getStockAlert() ? $this->jsonSerialize->unserialize($cart->getStockAlert()) : [];

            if ($stockAlert && isset($stockAlert[$productId])) {
                $email = $stockAlert[$productId]['email'];
                
                $stockModel = $this->stockFactory->create()
                    ->setCustomerEmail($email)
                    ->setProductId($productId)
                    ->setWebsiteId(
                        $websiteId
                    )->setStoreId(
                        $product->getStoreId()
                    )
                    ->loadByParamGuest();

                unset($stockAlert[$productId]);
                $cart->setStockAlert($this->jsonSerialize->serialize($stockAlert));
                $this->cartRepository->save($cart);
            }
        }

        return $stockModel;
    }
}
