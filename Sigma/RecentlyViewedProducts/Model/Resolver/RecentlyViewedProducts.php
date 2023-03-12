<?php
/**
 * @category  Sigma
 * @package   Sigma_RecentlyViewedProducts
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\RecentlyViewedProducts\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Block\Product\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Resolver for Recently Viewed Products
 */
class RecentlyViewedProducts implements ResolverInterface
{

    /**
     * ScopeConfig Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_storeManager;

    /**
     * ProductFactory Interface
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * PriceFormating
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @param context $context
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param ProductFactory $productFactory
     * @param Data $priceHelper
     * @param data $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        Data $priceHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_productFactory = $productFactory;
        $this->priceHelper       = $priceHelper;
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

        /**
         *  get customerId of customer
         */
        $customerId = $this->getId($args);

        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('report_viewed_product_index');

        // SELECT DATA
        $sql = $connection->select('*')
                          ->from($tableName)
                          ->where('customer_id = ?', $customerId)
                          ->limit('5');
                             
        $collection = $connection->fetchAll($sql);
        foreach ($collection as $data) {
            $productId = $data['product_id'];
            $store = $this->_storeManager->getStore();
            $product = $this->_productFactory->create()->load($productId);
            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
 
            $output[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'url_key' => $product->getProductUrl(),
                'thumbnail_image' => $productImageUrl,
                'price' => $this->priceHelper->currency($product->getPrice(), true, false)
                ];
        }
            return ['items' => $output];
    }

    /**
     * Passed customerId from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getId(array $args)
    {
        if (!isset($args['customerId'])) {
            throw new GraphQlInputException(__('"customerId should be specified'));
        }
        return $args['customerId'];
    }
}
