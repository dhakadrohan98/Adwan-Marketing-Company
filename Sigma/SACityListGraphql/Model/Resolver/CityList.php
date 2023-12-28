<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Sigma\SACityListGraphql\Model\Resolver;

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


class CityList implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFilter
     */
    private $orderFilter;

    /**
     * @var OrderFormatter
     */
    private $orderFormatter;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderFilter $orderFilter
     * @param OrderFormatter $orderFormatter
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
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $region_id = $args['code'];

        /** @var StoreInterface $store */
        //  $store = $context->getExtensionAttributes()->getStore();

       // $citiesArray = [];
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('directory_region_city');

        //SELECT DATA
        $sql = $connection->select('*')
            ->from($tableName)
            ->where('region_id = ?', $region_id);
        $collection = $connection->fetchAll($sql);
        foreach ($collection as $data) {
            $output[] = [
                'id' => $data['city_id'],
                'city_name' => $data['default_name']
            ];
        }
        return ['items' => $output];
    }
}
