<?php
/**
 * @category  Sigma
 * @package   Sigma_RecentlyViewedProducts
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\RecentlyViewedProducts\Model\Resolver\DataProvider;

 use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
 use Magento\Framework\App\Action\Context;
 use Magento\Framework\App\Request\DataPersistorInterface;
 use Magento\Framework\Controller\Result\Redirect;
 use Magento\Framework\Exception\LocalizedException;
 use Magento\Framework\App\ObjectManager;
 use Magento\Framework\DataObject;
 use Magento\Framework\App\ResourceConnection;
 use Magento\Store\Model\StoreManagerInterface;

class AddRecentlyViewed
{
   /**
    * @var DataPersistorInterface
    */
    private $dataPersistor;

   /**
    * @var ResourceConnection
    */
    private $resource;

   /**
    * Store Manager Model
    *
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;
   
   /**
    * @param DataPersistorInterface $dataPersistor
    * @param ResourceConnection $resource
    * @param StoreManagerInterface $storeManager
    */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
    }

    /**
     * Add or insert data in DB
     *
     * @param args $customerId
     * @param args $productId
     */
    public function addData($customerId, $productId)
    {
        $thanks_message = [];
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('report_viewed_product_index');
        $sql = $sql = "INSERT INTO " . $tableName . " (customer_id, product_id, store_id, added_at) VALUES ('".$customerId."', '".$productId."', '".$this->_storeManager->getStore()->getId()."', now())";
        $connection->query($sql);
        
        $thanks_message['success_message'] = "Data has been successfully inserted.";
        return $thanks_message;
    }
}
