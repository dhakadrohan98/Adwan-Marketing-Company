<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductMediaGalleryGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductMediaGalleryGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Emizentech\ShopByBrand\Model\BrandFactory;

class GetProductMediaGallery implements ResolverInterface
{

    /**
     * Brand Collection
     *
     * @var \Emizentech\ShopByBrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param BrandFactory $brandFactory
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     */
    public function __construct(
        BrandFactory $brandFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_brandFactory     = $brandFactory;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
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
         *  id of product data
         */
        $productId = $this->getId($args);

        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // get product by ID
        $product = $this->getProductById($productId);
        $manufacturerId = $product->getManufacturer();
        
        $brandItem = $this->_brandFactory->create()->load($manufacturerId, 'attribute_id');
       
        $brandLogo = ($brandItem->getLogo()) ? $mediaUrl.$brandItem->getLogo() : '';
        
        // Get Product Images
        $productImages = $this->getProductImages($productId);
       
        foreach ($productImages as $image) {
            $mediaGallery[] = [
                'image' => $image->getUrl(),
                'label' => $image->getLabel()
            ];
        }
            $output[] = [
                'name' => $product->getName(),
                'short_description' => $product->getShortDescription(),
                'manufacturer_logo' => $brandLogo,
                'media_gallery'=> $mediaGallery,

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
     * Get Product Media Gallery Images by Id
     *
     * @param int $productId
     * @return ProductFactory[]
     */
    public function getProductImages($productId)
    {
        $_product = $this->_productFactory->create()->load($productId);
        $productImages = $_product->getMediaGalleryImages();
        return $productImages;
    }

    /**
     * Get Product Repository by ID
     *
     * @param int $id
     * @return ProductRepository[]
     */
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }
}
