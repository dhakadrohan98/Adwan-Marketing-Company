<?php
/**
 * @category  Sigma
 * @package   Sigma_ServiceModelGraphql
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);
namespace Sigma\ServiceModelGraphql\Model\Resolver\DataProvider;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Workaround\Override\Fixture\ResolverInterface;
use Sigma\ServiceModel\Block\ServiceModel\Create;

class AddServiceModel
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
     * @var \Sigma\ServiceModel\Block\ServiceModel\Create $createBlock
     */
    protected $createBlock;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customers;
   /**
    * @param DataPersistorInterface $dataPersistor
    * @param ResourceConnection $resource
    * @param StoreManagerInterface $storeManager
    */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        \Sigma\ServiceModel\Block\ServiceModel\Create $createBlock,
        \Magento\Customer\Model\Customer $customers
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->createBlock = $createBlock;
        $this->_customers = $customers;
    }

    /**
     * Add or insert data in DB
     *
     * @param args $customerId
     * @param args $productId
     */
    public function addData($customerId, $createdAt, $customer_file, $uploadedFileName)
    {
        $thanks_message = [];
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('sigma_service');
        $sql = $sql = "INSERT INTO " . $tableName . " (customer_id, customer_file, insert_date, customer_file_name) VALUES ('".$customerId."', '".$customer_file."', '".$createdAt."','".$uploadedFileName."')";
        $connection->query($sql);
        $thanks_message['success_message'] = "Request has been sent successfully inserted.";
        //Send Mail
        if ($thanks_message['success_message'] != null) {
            $customer = $this->_customers->load($customerId);
            $name = $customer->getFirstname()." ".$customer->getLastname();
            $email = $customer->getEmail();
            $this->createBlock->sendMailToAdmin($name, $email, $uploadedFileName, $customer_file);
            $this->createBlock->acknowledgeMailFromAdmin($name, $email);
        }
        return $thanks_message;
    }
}
