<?php

/**
 * @category  Sigma
 * @package   Sigma_ServiceModelGraphql
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ServiceModelGraphql\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
//use Magento\Catalog\Block\Product\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Resolver for Recently Viewed Products
 */
class GetRequestCollection implements ResolverInterface
{

    /**
     * ScopeConfig Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_storeManager;

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
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ProductFactory $productFactory,
        Data $priceHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
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

        $customerId = $context->getUserId();


        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('sigma_service');

        //SELECT DATA
        $sql = $connection->select('*')
            ->from($tableName)
            ->where('customer_id = ?', $customerId);
        $collection = $connection->fetchAll($sql);
        foreach ($collection as $data) {
            $output[] = [
                'id' => $context->getUserId(),
                'customer_file' => $data['customer_file'],
                'created_at' => $data['insert_date'],
                'is_reply' => $data['is_reply'],
                'reply_date' => $data['reply_date'],
                'admin_file' => $data['admin_file']
            ];
        }

        return ['items' => $output];
    }
}
