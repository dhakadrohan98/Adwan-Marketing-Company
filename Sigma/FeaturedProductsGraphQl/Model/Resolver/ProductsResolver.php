<?php
/**
 * @category  Sigma
 * @package   Sigma_FeaturedProductsGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\FeaturedProductsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\Helper\Data as PriceFormating;

/**
 * Product collection resolver
 */
class ProductsResolver implements ResolverInterface
{
    /**
     * @var PriceFormating
     */
    protected $priceHelper;

    /**
     * @var PriceFormating
     */
    protected $_imageHelper;

    /**
     * @param context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param StockStateInterface $stockItem
     * @param Image $imageHelper
     * @param PriceFormating $priceHelper
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Magento\Catalog\Helper\Image $imageHelper,
        PriceFormating $priceHelper
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->stockItem                = $stockItem;
        $this->_imageHelper             = $imageHelper;
        $this->priceHelper               = $priceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $productsData = $this->getProductsData();
        return $productsData;
    }

    /**
     * Get Products Collection Data
     *
     * @return array
     *
     * @throws GraphQlNoSuchEntityException
     */
    
    private function getProductsData(): array
    {
        try {
            $collection = $this->_productCollectionFactory->create();
            $collection = $this->_productCollectionFactory->create()
                            ->addAttributeToSelect('*')
                            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                            ->addAttributeToFilter('status', '1')
                            ->addAttributeToFilter('featured_products', '1');

            $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

            $output = [];

            foreach ($collection as $_product) {
                $imageUrl = $this->_imageHelper->init($_product, 'product_page_image_small')
                    ->setImageFile($_product->getSmallImage())
                    ->resize(380)->getUrl();
                
                $stockQty = $this->stockItem->getStockQty($_product->getId(), $_product->getStore()->getWebsiteId());
                $output[] = [
                    'id' => $_product->getId(),
                    'name' => $_product->getName(),
                    'image' => $imageUrl,
                    'sku' => $_product->getSku(),
                    'qty' => $stockQty,
                    'price' => $this->priceHelper->currency($_product->getPrice(), true, false),
                    'special_price' => $this->priceHelper->currency($_product->getSpecialPrice(), true, false)
                ];
            }
            return ['items' => $output];

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }
}
