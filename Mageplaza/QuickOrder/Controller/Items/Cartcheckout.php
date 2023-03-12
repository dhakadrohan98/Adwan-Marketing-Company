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

namespace Mageplaza\QuickOrder\Controller\Items;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Mageplaza\QuickOrder\Helper\Item as QodItemHelper;
use Psr\Log\LoggerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

/**
 * Class Cartcheckout
 * @package Mageplaza\QuickOrder\Controller\Items
 */
class Cartcheckout extends Action
{
    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var FormKey
     */
    protected $_formKey;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var QodItemHelper
     */
    protected $_itemHelper;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockObject;

    /**
     * Cartcheckout constructor.
     *
     * @param Context $context
     * @param Cart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param FormKey $formKey
     * @param LoggerInterface $logger
     * @param QodItemHelper $itemHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        FormKey $formKey,
        LoggerInterface $logger,
        QodItemHelper $itemHelper,
        JsonHelper $jsonHelper,
        StockRegistryInterface $stockRegistryInterface
    ) {
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->_formKey = $formKey;
        $this->_logger = $logger;
        $this->_itemHelper = $itemHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->stockObject = $stockRegistryInterface;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('listitem');
        if (!$data) {
            $currentItems = $this->_cart->getItemsCount();
            if ($currentItems > 0) {
                return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode([
                    'hasItems' => $currentItems
                ]));
            }

            return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode(['noItem' => 'No Item found']));
        }

        try {
            foreach ($data as $item) {
                $product = $this->_objectManager->create(Product::class)->load($item['product_id']);
                $productInstock = $this->_itemHelper->getProductOutofStock($product->getId());
                if (!$productInstock) {
                    continue;
                }

                /** Get and check the 'Minimum Qty Allowed in Shopping Cart' configuration */
                $qty = $item['qty'];
                if ($item['type_id'] != 'grouped') {
                    $minimumQty = $this->stockObject->getStockItem($item['product_id'])->getMinSaleQty();
                    if ($qty < $minimumQty) {
                        $this->messageManager->addErrorMessage(__(
                            'Something went wrong when adding %1 to cart.The fewest you may purchase is %2. Please check it again.',
                            $product->getName(),
                            $minimumQty
                        ));

                        return $this->getResponse()->setBody(false);
                    }
                }

                $productType = $product->getTypeId();
                if ($productType === 'bundle') {
                    $bundle_option = [];
                    $bundle_option_qty = [];
                    $checkboxChildProduct = [];
                    $multiChildProduct = [];

                    foreach ($item['bundle_option'] as $option) {
                        if ($option['required'] == '1') {
                            $requireoption = false;
                            if (array_key_exists('select_product', $item)) {
                                foreach ($item['select_product'] as $bundleProduct) {
                                    if ($bundleProduct['option_id'] == $option['option_id']) {
                                        $requireoption = true;
                                    }
                                }
                            }
                            if ($requireoption === false) {
                                return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode([
                                    'requireoption' => $requireoption
                                ]));
                            }
                        }
                    }

                    if (array_key_exists('select_product', $item)) {
                        foreach ($item['bundle_option'] as $option) {
                            foreach ($item['select_product'] as $bundleProduct) {
                                if ($bundleProduct['option_id'] == $option['option_id'] && $option['type'] == 'radio'
                                    || $bundleProduct['option_id'] == $option['option_id'] && $option['type'] == 'select') {
                                    $bundle_option[$bundleProduct['option_id']] = $bundleProduct['selection_id'];
                                    if ($bundleProduct['selection_can_change_qty'] == 1) {
                                        $bundle_option_qty[$bundleProduct['option_id']] = (int)$bundleProduct['selection_qty'];
                                    }
                                }
                                if ($bundleProduct['option_id'] == $option['option_id'] && $option['type'] == 'checkbox') {
                                    $checkboxChildProduct[$bundleProduct['product_id']] = $item['product_id'];
                                }
                                if ($bundleProduct['option_id'] == $option['option_id'] && $option['type'] == 'multi') {
                                    $multiChildProduct[] = $bundleProduct['selection_id'];
                                }
                            }
                            if ($option['type'] == 'checkbox') {
                                $bundle_option[$option['option_id']] = $checkboxChildProduct;
                            }
                            if ($option['type'] == 'multi') {
                                $bundle_option[$option['option_id']] = $multiChildProduct;
                            }
                        }
                    }

                    $bundleparam = [
                        'product' => $item['product_id'],
                        'bundle_option' => $bundle_option,
                        'qty' => $item['qty']

                    ];

                    if (count($bundle_option_qty) > 0) {
                        $bundleparam['bundle_option_qty'] = $bundle_option_qty;
                    }

                    try {
                        $this->_cart->addProduct($product, $bundleparam);
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(__(
                            'Something went wrong when adding %1 to cart. Please check it again.',
                            $product->getName()
                        ));

                        return $this->getResponse()->setBody(false);
                    }
                } elseif ($productType == 'grouped') {
                    $productQty = [];
                    $checkChildQty = false;
                    $childProducts = $item['childProduct'];
                    foreach ($childProducts as $value) {
                        /** Get and check the 'Minimum Qty Allowed in Shopping Cart' configuration for group product*/
                        $minimumQty = $this->stockObject->getStockItem($value['product_id'])->getMinSaleQty();
                        $childQty = $value['qty'];
                        if ($childQty != 0 && $childQty < $minimumQty) {
                            $this->messageManager->addErrorMessage(__(
                                'Something went wrong when adding %1 to cart.The fewest you may purchase is %2. Please check it again.',
                                $value['name'],
                                $minimumQty
                            ));

                            return $this->getResponse()->setBody(false);
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
                            'product' => $product->getId(),
                            'super_group' => $productQty
                        ];
                    } else {
                        $this->messageManager->addErrorMessage(__(
                            'There is no value in Group product %1 . Please check it again.',
                            $product->getName()
                        ));

                        return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode([
                            'requireQty' => false
                        ]));
                    }

                    try {
                        $this->_cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(__(
                            'Something went wrong when adding %1 to cart. Please check it again.',
                            $product->getName()
                        ));

                        return $this->getResponse()->setBody(false);
                    }
                } elseif ($productType == 'configurable') {
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
                        'product' => $product->getId(),
                        'qty' => $qty,
                        'super_attribute' => $optionAddcart
                    ];
                    if ($this->getCustomOptions($item) !== false) {
                        $params = [
                            'product' => $product->getId(),
                            'qty' => $qty,
                            'options' => $this->getCustomOptions($item),
                            'super_attribute' => $optionAddcart
                        ];
                    }
                    try {
                        $this->_cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(__(
                            'Something went wrong when adding %1 to cart. Please check it again.',
                            $product->getName()
                        ));

                        return $this->getResponse()->setBody(false);
                    }
                } else {
                    /** other type product like simple, vitual, downloadable ...*/
                    $params = [
                        'product' => $product->getId(),
                        'qty' => $qty
                    ];
                    if ($this->getCustomOptions($item) !== false) {
                        $params = [
                            'product' => $product->getId(),
                            'qty' => $qty,
                            'options' => $this->getCustomOptions($item)
                        ];
                    }
                    try {
                        $this->_cart->addProduct($product, $params);
                    } catch (Exception $e) {
                        $this->messageManager->addErrorMessage(__(
                            'Something went wrong when adding %1 to cart. Please check it again.',
                            $product->getName()
                        ));

                        return $this->getResponse()->setBody(false);
                    }
                }
            }

            try {
                $this->_cart->save();
                $this->messageManager->addSuccessMessage(__('Added products to cart successfully!'));

                return $this->getResponse()->setBody(true);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong when adding product to cart. Please check it again.'));

                return $this->getResponse()->setBody(false);
            }
        } catch (Exception $e) {
            $writer = new Stream(BP . '/var/log/quickOrder.log');
            $logger = new Logger();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
        }
    }

    /**
     * @param $item
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
