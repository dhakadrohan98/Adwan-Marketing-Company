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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Item
 * @package Mageplaza\QuickOrder\Helper
 */
class Item extends Data
{
    /**
     * @var Option
     */
    protected $_customOption;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var Config
     */
    protected $catalogConfig;

    /**
     * @var PricingHelper
     */
    protected $_priceHelper;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ProductFactory
     */
    protected $_productRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var Image
     */
    protected $_helperImage;

    /**
     * Item constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param HttpContext $httpcontext
     * @param Product $product
     * @param PricingHelper $priceHelper
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param StockItemRepository $stockItemRepository
     * @param Option $customOption
     * @param Image $helperImage
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        HttpContext $httpcontext,
        Product $product,
        PricingHelper $priceHelper,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        PriceCurrencyInterface $priceCurrency,
        StockItemRepository $stockItemRepository,
        Option $customOption,
        Image $helperImage
    ) {
        $this->_priceHelper         = $priceHelper;
        $this->productVisibility    = $catalogProductVisibility;
        $this->catalogConfig        = $catalogConfig;
        $this->_productFactory      = $productFactory;
        $this->_productRepository   = $productRepository;
        $this->priceCurrency        = $priceCurrency;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_customOption        = $customOption;
        $this->_product             = $product;
        $this->_helperImage         = $helperImage;

        parent::__construct($context, $objectManager, $storeManager, $customerSession, $httpcontext);
    }

    /**
     * @return mixed
     */
    public function getMediaHelper()
    {
        return $this->objectManager->get(Media::class);
    }

    /**
     * @param $sku
     * @param $store
     * @param $group
     *
     * @return Collection
     */
    public function getProductCollectionForStore($sku, $store, $group)
    {
        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite();

        $collection->addAttributeToFilter('sku', $sku)
            ->addAttributeToFilter('status', Status::STATUS_ENABLED);

        return $collection;
    }

    /**
     * @param $productId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductAttributeOptions($productId)
    {
        $product                 = $this->_productRepository->getById($productId);
        $productAttribute        = $this->objectManager->get(Configurable::class);
        $productAttributeOptions = $productAttribute->getConfigurableAttributesAsArray($product);

        return $productAttributeOptions;
    }

    /**
     * @param $code
     * @param $attributeOption
     *
     * @return bool
     */
    public function checkAttributeCode($code, $attributeOption)
    {
        $attrCode = [];
        foreach ($attributeOption as $op) {
            $attrCode[] = $op['attribute_code'];
        }

        if (is_array($attrCode) && isset($attrCode)) {
            if (in_array($code, $attrCode, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $code
     * @param $attributeOption
     *
     * @return string
     */
    public function getcheckAttributeCodeId($code, $attributeOption)
    {
        $attrCodeId = '';

        foreach ($attributeOption as $op) {
            if ($op['attribute_code'] === $code) {
                $attrCodeId = $op['attribute_id'];
            }
        }

        return $attrCodeId;
    }

    /**
     * @param $value
     * @param $attributeOption
     *
     * @return bool
     */
    public function checkValueOfAttributeCode($value, $attributeOption)
    {
        $attrCodeValues = [];
        $values         = [];

        foreach ($attributeOption as $op) {
            $attrCodeValues[] = $op['values'];
        }

        foreach ($attrCodeValues as $key => $val) {
            foreach ($val as $cv) {
                $values[] = $cv['store_label'];
            }
        }

        if (is_array($values) && isset($values)) {
            if (in_array($value, $values, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $value
     * @param $attributeOption
     *
     * @return string
     */
    public function getcheckIdValueOfAttributeCode($value, $attributeOption)
    {
        $attrCodeValues = [];
        $valueId        = '';

        foreach ($attributeOption as $op) {
            $attrCodeValues[] = $op['values'];
        }

        foreach ($attrCodeValues as $key => $val) {
            foreach ($val as $cv) {
                if ($value === $cv['store_label']) {
                    $valueId = $cv['value_index'];
                }
            }
        }

        return $valueId;
    }

    /**
     * @param $productId
     * @param $productName
     * @param $sku
     * @param $skuChild
     * @param $qty
     * @param $getFinalPrice
     * @param $store
     * @param $typeId
     * @param $options
     * @param $optionIds
     * @param $optionSelectValue
     * @param $getSelectValueIdKey
     * @param $superAttribute
     * @param null $customOption
     * @param null $customOptionValue
     * @param null $childProduct
     * @param null $bundleOption
     * @param null $bundleProduct
     * @param null $bundleSelectOption
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getPreItemDataArray(
        $productId,
        $productName,
        $sku,
        $skuChild,
        $qty,
        $getFinalPrice,
        $store,
        $typeId,
        $options,
        $optionIds,
        $optionSelectValue,
        $getSelectValueIdKey,
        $superAttribute,
        $customOption = null,
        $customOptionValue = null,
        $childProduct = null,
        $bundleOption = null,
        $bundleProduct = null,
        $bundleSelectOption = null
    ) {
        if ($typeId === 'bundle') {
            foreach ($bundleProduct as $key => $item) {
                $bundleProduct[$key]['price'] = $this->priceCurrency->round(
                    $this->_priceHelper->currencyByStore($item['price'], $store, false, false)
                );
            }
        }

        return [
            'item_id'                 => $this->generateRandomString(9),
            'product_id'              => $productId,
            'name'                    => $productName,
            'sku'                     => $sku,
            'sku_child'               => $skuChild,
            'qty'                     => $qty,
            'qtystock'                => $this->getProductQtyStock($skuChild, $productId),
            'qty_salable'             => $this->getProductQtySalable($skuChild, $productId),
            'price'                   => $this->priceCurrency->round(
                $this->_priceHelper->currencyByStore($getFinalPrice, $store, false, false)
            ),
            'imageUrl'                => $this->getProductImageUrl($skuChild, $productId, $store),
            'type_id'                 => $typeId,
            'product_url'             => $this->getProductUrl($productId),
            'customOptionValue'       => $customOptionValue,
            'options'                 => $options,
            'optionIds'               => $optionIds,
            'options_select_value'    => $optionSelectValue,
            'options_select_value_id' => $getSelectValueIdKey,
            'super_attribute'         => $superAttribute,
            'outofstock'              => $this->getProductOutofStock($productId),
            'customOptions'           => $customOption,
            'childProduct'            => (array)$childProduct,
            'bundleOption'            => (array)$bundleOption,
            'bundleProduct'           => (array)$bundleProduct,
            'bundleSelectOption'      => (array)$bundleSelectOption,
            'customerGroup'           => $this->getCustomerGroup(),
            'tier_price'              => $this->getTierPrices($sku, $skuChild, $typeId)
        ];
    }

    /**
     * @return int
     */
    public function getCustomerGroup()
    {
        return $this->_customerSession->getCustomerGroupId();
    }

    /**
     * @param string $sku
     * @param string $skuChild
     * @param string $typeId
     *
     * @return array
     */
    public function getTierPrices($sku, $skuChild, $typeId)
    {
        $data = [];

        if ($typeId !== 'grouped') {
            $cus_groupId       = $this->getCustomerGroup();
            $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

            if ($sku !== '') {
                $product = $productRepository->get($sku, ['edit_mode' => true]);
            }

            if ($skuChild !== '') {
                $product = $productRepository->get($skuChild, ['edit_mode' => true]);
            }

            $allTiers = $product->getData('tier_price'); //returns all the tier prices of product

            if (count($allTiers) > 0) {
                foreach ($allTiers as $price) {
                    if ((int)$price['cust_group'] === $cus_groupId || (int)$price['cust_group'] === 32000) {
                        $data[] = [
                            'price'     => (float)($typeId === 'bundle' ? $price['percentage_value'] : $price['price']),
                            'price_qty' => (float)$price['price_qty']
                        ];
                    }
                }
            }

            return $data;
        }

        return $data;
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws Exception
     */
    public function generateRandomString($length = 4)
    {
        $characters       = '0123456789';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param $skuChild
     * @param $productId
     * @param $store
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductImageUrl($skuChild, $productId, $store)
    {
        $prdoduct = $this->_productFactory->create()->load($productId);
        if ($skuChild != '') {
            $productChildBySku = $this->_productRepository->get($skuChild);
            $productChildId    = $productChildBySku->getId();
            $prdoduct          = $this->_productFactory->create()->load($productChildId);
        }

        $defaultImage    = $this->_helperImage->getDefaultPlaceholderUrl('image');
        $productImageUrl = ($prdoduct->getImage() && $prdoduct->getImage() !== 'no_selection')
            ? $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $prdoduct->getImage()
            : $defaultImage;

        return $productImageUrl;
    }

    /**
     * @param $productId
     *
     * @return string
     */
    public function getProductUrl($productId)
    {
        $product = $this->_productFactory->create()->load($productId);

        return $product->getProductUrl();
    }

    /**
     * @param $productAttributeId
     * @param $productId
     *
     * @return mixed
     */
    public function getchidrenSimpleProudctByAttribute($productAttributeId, $productId)
    {
        $product         = $this->_productFactory->create()->load($productId);
        $productChildren = $this->objectManager->create(Configurable::class)
            ->getProductByAttributes($productAttributeId, $product);

        return $productChildren;
    }

    /**
     * @param $productId
     *
     * @return bool|int
     * @throws NoSuchEntityException
     */
    public function getProductOutofStock($productId)
    {
        $objectManager = ObjectManager::getInstance();
        $product       = $objectManager->create(StockRegistryInterface::class)
            ->getStockItem($productId);

        return $product->getIsInStock();
    }

    /**
     * @param $skuChild
     * @param $productId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductQtyStock($skuChild, $productId)
    {
        $objectManager = ObjectManager::getInstance();
        if ($skuChild !== '') {
            $productChildId  = $this->_productRepository->get($skuChild)->getId();
            $productQtyStock = $objectManager->create(StockStateInterface::class)
                ->getStockQty($productChildId);
        } else {
            $productQtyStock = $objectManager->create(StockStateInterface::class)
                ->getStockQty($productId);
        }

        return $productQtyStock;
    }

    /**
     * @param $skuChild
     * @param $productId
     *
     * @return bool
     */
    public function getProductQtySalable($skuChild, $productId)
    {
        if ($this->versionCompare('2.3.0')) {
            $objectManager = ObjectManager::getInstance();
            /** @var  $getSalableQuantityDataBySku */
            $getSalableQuantityDataBySku = $objectManager->create('Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');

            if ($skuChild !== '') {
                $salable = $getSalableQuantityDataBySku->execute($skuChild);
            } else {
                $product = $this->_productFactory->create()->load($productId);
                $sku     = $product->getSku();
                $salable = $getSalableQuantityDataBySku->execute($sku);
            }

            if (is_array($salable) && count($salable)) {
                return $salable[0]['qty'];
            }

            return false;
        }

        return false;
    }

    /**
     * @param string $sku
     * @param string|int $qty
     *
     * @return array|void
     * @throws NoSuchEntityException
     */
    public function getPreItemNotMeetConditionsFilter($sku, $qty)
    {
        $product = $this->_productRepository->get($sku);

        if ($product->getStatus() === '2' || !in_array($store->getWebsiteId(), $product->getWebsiteIds(), false)) {
            throw new NoSuchEntityException(
                __("The product that was requested doesn't exist or disabled. Verify the product and try again.")
            );
        }

        $productId     = $product->getId();
        $productName   = $product->getName();
        $getFinalPrice = $product->getFinalPrice();
        $typeId        = $product->getTypeId();
        $store         = $this->storeManager->getStore();

        if ($typeId !== 'bundle' && $typeId !== 'grouped') {
            $preItem = [
                'item_id'                 => $this->generateRandomString(9),
                'product_id'              => $productId,
                'name'                    => $productName,
                'sku'                     => $sku,
                'sku_child'               => '',
                'qty'                     => $qty,
                'qtystock'                => $this->getProductQtyStock($skuChild = '', $productId),
                'qty_salable'             => $this->getProductQtySalable($skuChild = '', $productId),
                'price'                   => $this->priceCurrency->round(
                    $this->_priceHelper->currencyByStore($getFinalPrice, $store, false, false)
                ),
                'imageUrl'                => $this->getProductImageUrl('', $productId, $store),
                'type_id'                 => $typeId,
                'product_url'             => $this->getProductUrl($productId),
                'options'                 => '',
                'optionIds'               => '',
                'options_select_value'    => '',
                'options_select_value_id' => '',
                'super_attribute'         => '',
                'outofstock'              => $this->getProductOutofStock($productId),
                'tier_price'              => $this->getTierPrices($sku, $skuChild, $typeId),
                'customOptionValue'       => '',
                'customOptions'           => ''
            ];

            return $preItem;
        }
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getProductOptionDefaultValue($attributeOption)
    {
        $options      = [];
        $valueDefault = '';

        foreach ($attributeOption as $op) {
            $attrCode         = $op['attribute_code'];
            $attrCodeValues[] = $op['values'];
            foreach ($attrCodeValues as $key => $val) {
                foreach ($val as $cv) {
                    $valueDefault = $cv['store_label'];
                    break;
                }
            }
            $options[] = $attrCode . ':' . $valueDefault;
        }

        return $options;
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getSuperAttribute($attributeOption)
    {
        $options = [];
        foreach ($attributeOption as $op) {
            $attributeId = $op['attribute_id'];
            $attrCode    = $op['attribute_code'];
            $options[]   = $attributeId . ':' . $attrCode;
        }

        return $options;
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getOptionIdsDefaultValue($attributeOption)
    {
        $options      = [];
        $valueDefault = '';

        foreach ($attributeOption as $op) {
            $attrId         = $op['attribute_id'];
            $attrIdValues[] = $op['values'];
            foreach ($attrIdValues as $key => $val) {
                foreach ($val as $cv) {
                    $valueDefault = $cv['value_index'];
                    break;
                }
            }
            $options[] = $attrId . ':' . $valueDefault;
        }

        return $options;
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getOptionIdsDefaultParam($attributeOption)
    {
        $options      = [];
        $valueDefault = '';

        foreach ($attributeOption as $op) {
            $attrId         = $op['attribute_id'];
            $attrIdValues[] = $op['values'];
            foreach ($attrIdValues as $key => $val) {
                foreach ($val as $cv) {
                    $valueDefault = $cv['value_index'];
                    break;
                }
            }
            $options += [$attrId => $valueDefault];
        }

        return $options;
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getSelectValueDefault($attributeOption)
    {
        $optionSelect = [];
        foreach ($attributeOption as $op => $opval) {
            $label            = [];
            $attrCodeValues   = [];
            $attrCode         = $opval['attribute_code'];
            $attrCodeValues[] = $opval['values'];
            foreach ($attrCodeValues as $key => $val) {
                foreach ($val as $cv) {
                    $label[] = $cv['store_label'];
                }
            }

            $optionSelect[$attrCode] = $label;
            $store_label             = null;
        }

        return $optionSelect;
    }

    /**
     * @param array $attributeOption
     *
     * @return array
     */
    public function getSelectValueIdKey($attributeOption)
    {
        $optionSelect = [];
        foreach ($attributeOption as $op => $opval) {
            $label            = [];
            $attrCodeValues   = [];
            $attrCode         = $opval['attribute_code'];
            $attrCodeValues[] = $opval['values'];
            foreach ($attrCodeValues as $key => $val) {
                foreach ($val as $cv) {
                    $label[] = $cv['value_index'] . ':' . $cv['store_label'];
                }
            }

            $store_label             = implode(',', $label);
            $optionSelect[$attrCode] = explode(',', $store_label);
            $store_label             = null;
        }

        return $optionSelect;
    }

    /**
     * @param $attrcode
     * @param $valueOfAttrCode
     * @param $arrayConvert
     * @param $getSelectValueDefault
     *
     * @return mixed
     */
    public function getSelectValueConvertOption($attrcode, $valueOfAttrCode, $arrayConvert, $getSelectValueDefault)
    {
        foreach ($getSelectValueDefault as $attribute => $values) {
            if ($attrcode === $attribute) {
                foreach ($values as $key => $value) {
                    if ($valueOfAttrCode === $value) {
                        unset($values[$key]);
                        array_unshift($values, $valueOfAttrCode);
                    }
                }
                $arrayConvert[$attribute] = $values;
            }
        }

        return $arrayConvert;
    }

    /**
     * @param string $productId
     *
     * @return array|string
     */
    public function getCustomOptions($productId)
    {
        $product           = $this->_productFactory->create()->load($productId);
        $customOptions     = $this->_customOption->getProductOptionCollection($product);
        $customOptionValue = [];
        $customOption      = [];

        foreach ($customOptions as $result) {
            if ($result !== null) {
                $optionValue   = [];
                $selectPrice   = [];
                $selectOptions = [];
                $selectTitle   = [];
                $coPrice       = $result->getPrice();
                if ($result->getPriceType() === 'percent') {
                    $coPrice = ($coPrice * $product->getFinalPrice()) / 100;
                }
                if ($result->getGroupByType() === 'select') {
                    foreach ($result->getValuesCollection() as $key => $value) {
                        $optionValue[]                          = $value->getOptionTypeId();
                        $selectPrice[$value->getOptionTypeId()] = $value->getPrice();
                        $selectTitle[$value->getOptionTypeId()] = $value->getTitle();
                        if ($value->getPriceType() === 'percent') {
                            $selectPrice[$value->getOptionTypeId()] =
                                ($selectPrice[$value->getOptionTypeId()] * $product->getFinalPrice()) / 100;
                        }
                    }
                    $coPrice = $selectPrice;
                }
                $customOptionValue[$result->getOptionId()] = '';
                $customOption[]                            = [
                    'title'          => $result->getTitle(),
                    'price'          => $coPrice,
                    'amount'         => 0,
                    'optionId'       => $result->getOptionId(),
                    'type'           => $result->getType(),
                    'groupType'      => $result->getGroupByType(),
                    'isRequire'      => $result->getIsRequire(),
                    'selectOptions'  => $selectOptions,
                    'selectTitle'    => $selectTitle,
                    'optionTypeId'   => $optionValue,
                    'max_characters' => $result->getMaxCharacters()
                ];
            }
        }

        return [$customOptionValue, $customOption];
    }
}
