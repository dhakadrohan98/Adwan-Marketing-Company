<?php
/**
 * @category  Sigma
 * @package   Sigma_ShopByBrandGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ShopByBrandGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Emizentech\ShopByBrand\Model\BrandFactory;
use Magento\Framework\Pricing\Helper\Data;
use Emizentech\ShopByBrand\Model\Items;

class BrandInfo implements ResolverInterface
{

    /**
     * PriceFormating
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * Brand Collection
     *
     * @var \Emizentech\ShopByBrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * Brand Information
     *
     * @var \Emizentech\ShopByBrand\Model\Items
     */
    protected $_brandInfo;

    /**
     * Product Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collectionFactory;

    /**
     * @param BrandFactory $brandFactory
     * @param CollectionFactory $collectionFactory
     * @param Data $priceHelper
     * @param Items $brandInfo
     */
    public function __construct(
        BrandFactory $brandFactory,
        CollectionFactory $collectionFactory,
        Data $priceHelper,
        Items $brandInfo
    ) {
        $this->_brandFactory     = $brandFactory;
        $this->collectionFactory = $collectionFactory;
        $this->priceHelper       = $priceHelper;
        $this->_brandInfo     = $brandInfo;
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
        $output = [];

        /**
         *  Id for brand item
         */
        $id = $this->getId($args);

        // get Brand items data
        $brandItem = $this->_brandInfo->load($id);
        
        $attributeId = $brandItem->getAttributeId();
        $productData = $this->getProductDetails($attributeId);
        $ProductDetails = [];
        foreach ($productData as $_product) {
            $ProductDetails[] = [
                'id' => $_product->getId(),
                'name' => $_product->getName(),
                'sku' => $_product->getSku(),
                'image' => $_product->getImage(),
                'price' => $this->priceHelper->currency($_product->getPrice(), true, false),
            ];
        }
            $output[] = [
                'id' => $brandItem->getId(),
                'name' => $brandItem->getName(),
                'logo' => $brandItem->getLogo(),
                'description' => $brandItem->getDescription(),
                'productdetail'=> $ProductDetails,

            ];
        
            return ['items' => $output];
    }

     /**
      * Passed Id from graphql query
      *
      * @param array $args
      */
    private function getId(array $args)
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id should be specified'));
        }
        return $args['id'];
    }

     /**
      * Get Product Details based on attribute_id
      *
      * @param int $attributeId
      * @return Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
      */
    public function getProductDetails($attributeId)
    {
        $product = $this->collectionFactory->create()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('manufacturer', ['eq' => $attributeId ])
        ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->load();
        return $product;
    }
}
