<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductSpecificationsGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductSpecificationsGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
     * @param Magento\Backend\Block\Template\Context $context
     * @param Magento\Catalog\Model\ProductRepository $productRepository
     * @param data $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_productRepository = $productRepository;
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
     * this function return all the word of the day by id
     **/
    public function getAttributesBySku($sku)
    {
        $_product = $this->getProductBySku($sku);
        $attributes = $_product->getAttributes();// All Product Attributes

        $attributesData = [];
        $i=0;
        foreach ($attributes as $attribute) {
            if ($attribute->getIsUserDefined()) { // system product attribute by checking attribute is user created
                if ($attribute->getAttributeCode()=="cost") {
                    continue;
                }
                $attributeLabel = $attribute->getStoreLabel();
                $attributeValue = $attribute->getFrontend()->getValue($_product);

                $attributeLabelAndValue = $attributeLabel.": ".$attributeValue;
                $attributesData[$i]['label'] = $attributeLabel;
                $attributesData[$i]['value'] = $attributeValue;
            }
            $i++;
        }
        return $attributesData;
    }
}
