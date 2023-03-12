<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\QuickOrder\Helper;

use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\QuickOrder\Helper\Item as HelperItem;
use RuntimeException;

/**
 * Class AddToCart
 * @package Mageplaza\QuickOrder\Helper
 */
class AddToCart extends Data
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Item
     */
    protected $helperItem;

    /**
     * @var StockRegistryInterface
     */
    protected $stockObject;

    /**
     * AddToCart constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param HttpContext $httpcontext
     * @param Cart $cart
     * @param ProductFactory $productFactory
     * @param Item $helperItem
     * @param StockRegistryInterface $stockObject
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        HttpContext $httpcontext,
        Cart $cart,
        ProductFactory $productFactory,
        HelperItem $helperItem,
        StockRegistryInterface $stockObject
    ) {
        $this->cart           = $cart;
        $this->productFactory = $productFactory;
        $this->helperItem     = $helperItem;
        $this->stockObject    = $stockObject;
        parent::__construct($context, $objectManager, $storeManager, $customerSession, $httpcontext);
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function addToCart($data)
    {
        if (!$data) {
            $currentItems = $this->cart->getItemsCount();

            if (!$currentItems) {
                throw new NoSuchEntityException(__('No Item Found'));
            }
        }

        try {
            foreach ($data as $item) {
                $product        = $this->productFactory->create()->load($item['product_id']);
                $productInstock = $this->helperItem->getProductOutofStock($product->getId());

                if (!$productInstock) {
                    continue;
                }

                /** Get and check the 'Minimum Qty Allowed in Shopping Cart' configuration */
                $qty = $item['qty'];

                if ($item['type_id'] !== 'grouped') {
                    $minimumQty = $this->stockObject->getStockItem($item['product_id'])->getMinSaleQty();

                    if ($qty < $minimumQty) {
                        throw new LocalizedException(__(
                            'Something went wrong when adding %1 to cart.The fewest you may purchase is %2. Please check it again.',
                            $product->getName(),
                            $minimumQty
                        ));
                    }
                }

                $productType = $product->getTypeId();

                if ($productType === 'bundle') {
                    $bundle_option        = [];
                    $bundle_option_qty    = [];
                    $checkboxChildProduct = [];
                    $multiChildProduct    = [];

                    foreach ($item['bundle_option'] as $option) {
                        if ($option['required'] === '1') {
                            $requireoption = false;

                            if (array_key_exists('select_product', $item)) {
                                foreach ($item['select_product'] as $bundleProduct) {
                                    if ($bundleProduct['option_id'] === $option['option_id']) {
                                        $requireoption = true;
                                    }
                                }
                            }
                            if ($requireoption === false) {
                                throw new ValidatorException(__('Please select all necessary options'));
                            }
                        }
                    }

                    if (array_key_exists('select_product', $item)) {
                        foreach ($item['bundle_option'] as $option) {
                            foreach ($item['select_product'] as $bundleProduct) {
                                if ($bundleProduct['option_id'] === $option['option_id'] && $option['type'] === 'radio'
                                    || $bundleProduct['option_id'] === $option['option_id'] && $option['type'] === 'select') {

                                    $bundle_option[$bundleProduct['option_id']] = $bundleProduct['selection_id'];
                                    if ($bundleProduct['selection_can_change_qty'] === '1') {
                                        $bundle_option_qty[$bundleProduct['option_id']] = (int) $bundleProduct['selection_qty'];
                                    }
                                }

                                if ($bundleProduct['option_id'] === $option['option_id'] && $option['type'] === 'checkbox') {
                                    $checkboxChildProduct[$bundleProduct['product_id']] = $bundleProduct['selection_id'];
                                }

                                if ($bundleProduct['option_id'] === $option['option_id'] && $option['type'] === 'multi') {
                                    $multiChildProduct[] = $bundleProduct['selection_id'];
                                }
                            }

                            if ($option['type'] === 'checkbox') {
                                $bundle_option[$option['option_id']] = $checkboxChildProduct;
                            }

                            if ($option['type'] === 'multi') {
                                $bundle_option[$option['option_id']] = $multiChildProduct;
                            }
                        }
                    }

                    $bundleparam = [
                        'product'       => $item['product_id'],
                        'bundle_option' => $bundle_option,
                        'qty'           => $item['qty']

                    ];

                    if (count($bundle_option_qty) > 0) {
                        $bundleparam['bundle_option_qty'] = $bundle_option_qty;
                    }

                    try {
                        $this->cart->addProduct($product, $bundleparam);
                    } catch (Exception $e) {
                        throw new LocalizedException(__(
                            'Something went wrong when adding %1 to cart. Please check it again.',
                            $product->getName()
                        ));
                    }
                } elseif ($productType === 'grouped') {
                    $productQty    = [];
                    $checkChildQty = false;
                    $childProducts = $item['childProduct'];

                    foreach ($childProducts as $value) {
                        /** Get and check the 'Minimum Qty Allowed in Shopping Cart' configuration for group product*/
                        $minimumQty = $this->stockObject->getStockItem($value['product_id'])->getMinSaleQty();
                        $childQty   = $value['qty'];

                        if ($childQty !== '0' && $childQty < $minimumQty) {
                            throw new LocalizedException(__(
                                'Something went wrong when adding %1 to cart.The fewest you may purchase is %2. Please check it again.',
                                $value['name'],
                                $minimumQty
                            ));
                        }

                        $productQty[$value['product_id']] = $value['qty'];
                    }

                    foreach ($productQty as $value) {
                        if ($value !== '0') {
                            $checkChildQty = true;
                            break;
                        }
                    }

                    if ($checkChildQty === true) {
                        $params = [
                            'product'     => $product->getId(),
                            'super_group' => $productQty
                        ];
                    } else {
                        throw new LocalizedException(__(
                            'There is no value in Group product %1 . Please check it again.',
                            $product->getName()
                        ));
                    }

                    try {
                        $this->cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        throw new LocalizedException(__(
                            'Something went wrong when adding %1 to cart. Please check it again. %2',
                            $product->getName(),
                            $e->getMessage()
                        ));
                    }
                } elseif ($productType === 'configurable') {
                    $idsAttrAddcart = [];

                    foreach ($item['optionIds'] as $optionchoose) {
                        $attribute = explode(':', $optionchoose);
                        foreach ($attribute as $attr) {
                            $idsAttrAddcart[] = $attr;
                        }
                    }

                    /** prepare data fore super_attribute to add option to cart*/
                    $optionAddcart = [];

                    for ($i = 0; $i <= sizeof($idsAttrAddcart); $i++) {
                        $iIn = $i++;

                        if (isset($idsAttrAddcart[$i])) {
                            $optionAddcart += [intval($idsAttrAddcart[$iIn]) => $idsAttrAddcart[$i]];
                        }
                    }

                    $params = [
                        'product'         => $product->getId(),
                        'qty'             => $qty,
                        'super_attribute' => $optionAddcart
                    ];

                    if ($this->getCustomOptions($item) !== false) {
                        $params = [
                            'product'         => $product->getId(),
                            'qty'             => $qty,
                            'options'         => $this->getCustomOptions($item),
                            'super_attribute' => $optionAddcart
                        ];
                    }

                    try {
                        $this->cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        throw new LocalizedException(__(
                            'Something went wrong when adding %1 to cart. Please check it again. %2',
                            $product->getName(),
                            $e->getMessage()
                        ));
                    }
                } else {
                    /** other type product like simple, vitual, downloadable ...*/
                    $params = [
                        'product' => $product->getId(),
                        'qty'     => $qty
                    ];

                    if ($this->getCustomOptions($item) !== false) {
                        $params = [
                            'product' => $product->getId(),
                            'qty'     => $qty,
                            'options' => $this->getCustomOptions($item)
                        ];
                    }

                    try {
                        $this->cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        throw new LocalizedException(__(
                            'Something went wrong when adding %1 to cart. Please check it again. %2',
                            $product->getName(),
                            $e->getMessage()
                        ));
                    }
                }
            }

            try {
                $this->cart->save();
                $data[] = ['quote_id' => $this->cart->getQuote()->getId()];

                return $data;
            } catch (Exception $e) {
                throw new LocalizedException(__(
                    'Something went wrong when adding product to cart. Please check it again. %1',
                    $e->getMessage()
                ));
            }
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @param array $item
     *
     * @return array|bool
     */
    public function getCustomOptions($item)
    {
        if (isset($item['customOptions']) && count($item['customOptionValue']) > 0) {
            return $item['customOptionValue'];
        }

        return false;
    }
}
