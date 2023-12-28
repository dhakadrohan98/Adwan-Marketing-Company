<?php
/**
 * @category  Sigma
 * @package   Sigma_ProductPDFGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\ProductPDFGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Dotsquares\FilesCollection\Model\ItemsFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolver for PDF Files
 */
class Files implements ResolverInterface
{
    /**
     * Store Manager Model
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Files Collection
     *
     * @var \Sigma\ProductPDFGraphQl\Model\FilesFactory
     */
    protected $_filesFactory;

    /**
     * @param ItemsFactory $filesFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ItemsFactory $filesFactory, StoreManagerInterface $storeManager)
    {
        $this->_filesFactory = $filesFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /**
         *  Id As Argument For Product Ids
         */
        $id = $this->getId($args);

        /**
         *  StoreId As Argument For store Ids
         */
        $storeid = $this->getStoreId($args);

        /**
         * Get Collection For Dotsquares Filescollection Items
         */
        $collection = $this
            ->_filesFactory
            ->create()
            ->getCollection();
        $collection = $collection->addFieldToFilter('product_ids', ['finset' => $id])
                                ->addFieldToFilter('store_ids', ['finset' => $storeid])
                                ->addFieldToFilter('status', 1);

        $output = [];

        /**
         * Get Media Url From Store Manager
         */
        $mediaUrl = $this
            ->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        /**
         *  Get Filescollection
         */
        foreach ($collection as $_value) {
            if ($_value->getFileName() != null) {
                $output[] = [
                    'id' => $_value->getId(),
                    'name' => $_value->getName() ,
                    'file_name' => $mediaUrl . 'dotsquares' . $_value->getFileName() ,
                    'status' => $_value->getStatus()
                ];
            }
        }

        return ['items' => $output];
    }

    /**
     * Passed Id from graphql query
     *
     * @param args $args
     */
    private function getId(array $args)
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id should be specified'));
        }
        return $args['id'];
    }

    /**
     * Passed storeid from graphql query
     *
     * @param args $args
     */
    private function getStoreId(array $args)
    {
        if (!isset($args['storeid'])) {
            throw new GraphQlInputException(__('"storeid should be specified'));
        }
        return $args['storeid'];
    }
}
