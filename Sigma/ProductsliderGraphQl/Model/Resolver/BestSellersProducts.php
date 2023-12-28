<?php
namespace Sigma\ProductsliderGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Mageplaza\Productslider\Helper\Data;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceFormating;

/**
 * Class BestSellerProducts
 * Mageplaza\Productslider\Block
 */
class BestSellersProducts extends AbstractProduct implements ResolverInterface
{
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var PriceFormating
     */
    protected $priceHelper;
    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;
    /**
     * @var Grouped
     */
    protected $grouped;
    /**
     * @var Configurable
     */
    protected $configurable;
    /**
     * @var
     */
    protected $_productCollectionFactory;
    /**
     * @var Visibility
     */
    protected $_catalogProductVisibility;
    /**
     * @var HttpContext
     */

    /**
     * BestSellerProducts constructor.
     *
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param HttpContext $httpContext
     * @param EncoderInterface $urlEncoder
     * @param BestSellersCollectionFactory $bestSellersCollectionFactory
     * @param Grouped $grouped
     * @param Configurable $configurable
     * @param LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        DateTime $dateTime,
        Data $helperData,
        HttpContext $httpContext,
        EncoderInterface $urlEncoder,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        Grouped $grouped,
        Configurable $configurable,
        LayoutFactory $layoutFactory,
        StoreManagerInterface $storeManager,
        PriceFormating $priceHelper,
        array $data = []
    ) {
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->grouped                   = $grouped;
        $this->configurable              = $configurable;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->storeManager              = $storeManager;
        $this->priceHelper               = $priceHelper;
    }

    /**
     * get collection of best-seller products
     * @return mixed
     */
    public function getProductCollection()
    {
        $bestSellers = $this->_bestSellersCollectionFactory->create()
        ->setModel(\Magento\Catalog\Model\Product::class)
        ->addStoreFilter($this->getStoreId())
        ->setPeriod('month');

        $productIds = $this->getProductParentIds($bestSellers);
        if (empty($productIds)) {
            return null;
        }

        $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(['name','image'])
            ->addStoreFilter($this->getStoreId())
            ->addUrlRewrite()
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setPageSize($this->getProductsCount());

        return $collection;
    }
        
        /**
         * Get Store Id
         *
         * @return int
         */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getProductParentIds($collection)
    {
        $productIds = [];

        foreach ($collection as $product) {
            if (isset($product->getData()['entity_id'])) {
                $productId = $product->getData()['entity_id'];
            } else {
                $productId = $product->getProductId();
            }

            $parentIdsGroup  = $this->grouped->getParentIdsByChild($productId);
            $parentIdsConfig = $this->configurable->getParentIdsByChild($productId);

            if (!empty($parentIdsGroup)) {
                $productIds[] = $parentIdsGroup;
            } elseif (!empty($parentIdsConfig)) {
                $productIds[] = $parentIdsConfig[0];
            } else {
                $productIds[] = $productId;
            }
        }

        return $productIds;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $productData = $this->getProductCollection();
        $priceHelper = $this->priceHelper;
            
        foreach ($productData as $_product) {
            $output[] = [
                'id' => $_product->getId(),
                'name' => $_product->getName(),
                'image' => $_product->getImage(),
                'sku' => $_product->getSku(),
                'price' => $priceHelper->currency($_product->getPrice(), true, false),
                'special_price' => $priceHelper->currency($_product->getSpecialPrice(), true, false)
            ];
        }
        return ['items' => $output];
    }
}
