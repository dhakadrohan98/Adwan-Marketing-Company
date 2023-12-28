<?php

declare(strict_types=1);

namespace Emizentech\ShopByBrand\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Emizentech\ShopByBrand\Model\BrandFactory;
use Magento\Framework\Pricing\Helper\Data;

class TopManufacturers implements ResolverInterface
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
     * Product Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        BrandFactory $brandFactory,
        CollectionFactory $collectionFactory,
        Data $priceHelper
    ) {
        $this->_brandFactory     = $brandFactory;
        $this->collectionFactory = $collectionFactory;
        $this->priceHelper       = $priceHelper;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null)
    {
        $output = [];
        /**
         *  Id for brand Collection
         */
        $id = $this->getId($args);
        $topBrands = $this->getFeaturedBrands($id);
        $priceHelper = $this->priceHelper;

        foreach ($topBrands as $_brands) {
          $attributeId = $_brands->getAttributeId();
          $productdeatils = $this->getProductdetails($attributeId);

            foreach ($productdeatils as $value) {
                $ProductDetails[] = [
                    'id' => $value->getId(),
                    'name' => $value->getName(),
                    'sku' => $value->getSku(),
                    'image' => $value->getImage(),
                    'price' => $priceHelper->currency($value->getPrice(), true, false),
                ];
            }
                $output[] = [
                    'id' => $_brands->getId(),
                    'name' => $_brands->getName(),
                    'logo' => $_brands->getLogo(),
                    'description' => $_brands->getDescription(),
                    'productdetail'=> $ProductDetails,

                ];
        }
        
        return ['items' => $output];
    }

    /**
     *  Get FeaturedBrand
     *
     * @return Emizentech\ShopByBrand\Model\BrandFactory
     */
    public function getFeaturedBrands($id){
        $collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('id',['in' => $id]);
        $collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('featured' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->setOrder('sort_order' , 'ASC');
        return $collection;
    }

     /**
     *  Passed Id from graphql query
     *
     */
    private function getId(array $args)
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id should be specified'));
        }
        return $args['id'];
    }

     /**
     *  Get Product Details based on attribute_id
     *
     * @return Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getProductdetails($attributeId)
    {
        $product = $this->collectionFactory->create()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('manufacturer', array('eq' => $attributeId ))
        ->load();

    return $product;
    }
}
