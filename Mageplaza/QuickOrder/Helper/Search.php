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
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Search\Model\Product\Url;
use Magento\CatalogRule\Model\RuleFactory;

/**
 * Class Search
 * @package Mageplaza\QuickOrder\Helper
 */
class Search extends Data
{
    const CONFIG_MODULE_PATH = 'quickorder';

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
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var CollectionFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var RuleFactory
     */
    protected $catalogPriceRule;

    /**
     * Search constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param Session $customerSession
     * @param HttpContext $httpcontext
     * @param CollectionFactory $customerGroupCollectionFactory
     * @param Escaper $escaper
     * @param PricingHelper $priceHelper
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param FormatInterface $localeFormat
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        HttpContext $httpcontext,
        CollectionFactory $customerGroupCollectionFactory,
        Escaper $escaper,
        PricingHelper $priceHelper,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        FormatInterface $localeFormat,
        CategoryFactory $categoryFactory,
        RuleFactory $catalogPriceRule
    ) {
        $this->_customerGroupFactory = $customerGroupCollectionFactory;
        $this->_escaper = $escaper;
        $this->_priceHelper = $priceHelper;
        $this->productVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->localeFormat = $localeFormat;
        $this->categoryFactory = $categoryFactory;
        $this->catalogPriceRule = $catalogPriceRule;
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
     * Create json file to contain product data
     */
    public function createJsonFile()
    {
        $errors = [];
        $customerGroups = $this->_customerGroupFactory->create();
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($customerGroups as $group) {
                try {
                    $this->createJsonFileForStore($store, $group->getId());
                } catch (Exception $e) {
                    $errors[] = __(
                        'Cannot generate data for store %1 and customer group %2, %3',
                        $store->getCode(),
                        $group->getCode(),
                        $e->getMessage()
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * @param $store
     * @param $group
     *
     * @return $this
     */
    public function createJsonFileForStore($store, $group)
    {
        $productList = [];

        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());

        $objectManager = ObjectManager::getInstance();
        try {
            $cfpHelper = $objectManager->create('Mageplaza\CallForPrice\Helper\Rule');
        } catch (\Exception $e) {
            $cfpHelper = false;
        }


        /** @var Product $product */
        foreach ($collection as $product) {
            if (is_null($product->getData('tier_price'))) {
                $rule = $this->catalogPriceRule->create();
                $finalPrice = $rule->calcProductPriceRule($product, $product->getPrice());
                if (!$finalPrice) {
                    $finalPrice = $product->getFinalPrice();
                }
            } else {
                $finalPrice = $product->getData('tier_price');
            }

            if ($product->getTypeId() === 'configurable') {
                $_children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($_children as $child) {
                    $finalPrice = $child->getFinalPrice();
                    break;
                }
            }
                $cfpAction = '';
            if ($cfpHelper && $cfpRule = $cfpHelper->validateProductInRuleAvailable($product->getId(), $group)) {
                $cfpAction = $cfpRule->getAction();
            }

            if ($product->getTypeId() === 'bundle') {
                $objectManager = ObjectManager::getInstance();
                $bundleProduct = $objectManager->get(Product::class)
                    ->setStoreId($store->getId())
                    ->load($product->getId());
                $bundleObj = $bundleProduct->getPriceInfo()->getPrice('final_price');
                $productList[] = [
                    'value' => $product->getName(),
                    's' => $product->getSku(), //sku
                    'minP' => $this->_priceHelper->currencyByStore(
                        (float)$bundleObj->getMinimalPrice()->getValue(),
                        $store,
                        false,
                        false
                    ),
                    'maxP' => $this->_priceHelper->currencyByStore(
                        (float)$bundleObj->getMaximalPrice()->getValue(),
                        $store,
                        false,
                        false
                    ),//price
                    'i' => $this->getMediaHelper()->getProductImage($product, 'mpsearch_image'),//image
                    'u' => $this->getProductUrl($product), //product url
                    't' => $product->getTypeId(),
                    'cfp'=> $cfpAction
                ];
            } else {
                $productList[] = [
                    'value' => $product->getName(),
                    's' => $product->getSku(), //sku
                    'p' => $this->_priceHelper->currencyByStore($finalPrice, $store, false, false), //price
                    'i' => $this->getMediaHelper()->getProductImage($product, 'mpsearch_image'),//image
                    'u' => $this->getProductUrl($product),//product url
                    't' => $product->getTypeId(),
                    'cfp'=> $cfpAction
                ];
            }


        }
        $this->getMediaHelper()->createJsFile(
            $this->getJsFilePath($group, $store),
            'var mageplazaSearchProducts = ' . self::jsonEncode($productList)
        );

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return bool|string
     */
    protected function getProductUrl($product)
    {
        $productUrl = $product->getProductUrl();
        $requestPath = $product->getRequestPath();
        if (!$requestPath) {
            $pos = strpos($productUrl, 'catalog/product/view');
            if ($pos !== false) {
                $productUrl = substr($productUrl, $pos + 20);
            }
        } else {
            $productUrl = $requestPath;
        }

        return $productUrl;
    }

    /**
     * @param int $customerGroupId
     * @param Store $store
     *
     * @return string
     */
    public function getJsFilePath($customerGroupId, $store)
    {
        return Media::TEMPLATE_MEDIA_PATH . '/' . $store->getCode() . '_' . $customerGroupId . '.js';
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getJsFileUrl()
    {
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $mediaDirectory = $this->getMediaHelper()->getMediaDirectory();
        $filePath = $this->getJsFilePath($customerGroupId, $store);
        if (!$mediaDirectory->isFile($filePath)) {
            $this->createJsonFileForStore($store, $customerGroupId);
        }

        return $this->getMediaHelper()->getMediaUrl($filePath);
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return self::jsonEncode($this->localeFormat->getPriceFormat());
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMaxResultAllowShow($storeId = null)
    {
        return $this->getModuleConfig('search/limit_search_results', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMinCharacterToQuery($storeId = null)
    {
        return $this->getModuleConfig('search/minimum_character', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAllowDisplayImageConfig($storeId = null)
    {
        return $this->getModuleConfig('search/display_product_image', $storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrentCurrencyRate()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        return $this->storeManager->getStore()->getBaseCurrency()->getRate($currencyCode);
    }
}
