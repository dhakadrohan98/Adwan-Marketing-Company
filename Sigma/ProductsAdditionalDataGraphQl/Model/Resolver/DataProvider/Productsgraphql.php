<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductsAdditionalDataGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductsAdditionalDataGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Emizentech\ShopByBrand\Model\Items;

/**
 * Resolver for Products Additional csutom attribute Data Provider
 */
class Productsgraphql extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * Brand Information
     *
     * @var \Emizentech\ShopByBrand\Model\Items
     */
    protected $_brandInfo;

    /**
     * @param Magento\Backend\Block\Template\Context $context
     * @param Magento\Catalog\Model\ProductRepository $productRepository
     * @param Items $brandInfo
     * @param data $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Items $brandInfo,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
        $this->_brandInfo     = $brandInfo;
        parent::__construct($context, $data);
    }

    /**
     * Get product repository by sku
     *
     * @param string $sku
     * this function return all the product data by product sku
     **/
    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }
    /**
     * Get product attribute data by sku
     *
     * @param string $sku
     * @param int $manufacturerId
     * this function return all the word of the day by id
     **/
    public function getAttributesBySku($sku, $manufacturerId)
    {
        $_product = $this->getProductBySku($sku);
        $attributes = $_product->getAttributes();// All Product Attributes

        // get Brand items data
        $brandItem = $this->_brandInfo->load($manufacturerId, 'attribute_id');
        
        $attributesData = [];
        $i=0;
        foreach ($attributes as $attribute) {
            //if($attribute->getIsUserDefined()){ // system product attribute by checking attribute is user created
                $attributeLabel = $attribute->getFrontend()->getLabel();
                $attributeValue = $attribute->getFrontend()->getValue($_product);

            if ($attribute->getAttributeCode()=="manufacturer") {
                $attributeLabelAndValue = $attributeLabel.": ".$attributeValue;
                $attributesData[$i]['value'] = $attributeValue;
                $attributesData[$i]['sku'] = $sku;
                $attributesData[$i]['url_key'] = $brandItem->getUrlKey();
            }
            //}
            $i++;
        }
        return $attributesData;
    }
}
