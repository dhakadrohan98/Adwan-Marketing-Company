<?php

/**
 * @category  Sigma
 * @package   Sigma_ServiceModelGraphql
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\CareersGraphQl\Model\Resolver\DataProvider;

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
use Sigma\Careers\Block\Careers\Create;

class AddCareerRequest
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
     * @var \Sigma\Careers\Block\Careers\Create $createBlock
     */
    protected $createBlock;


    /**
     * @param DataPersistorInterface $dataPersistor
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        \Sigma\Careers\Block\Careers\Create $createBlock
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->createBlock = $createBlock;
    }

    /**
     * Add or insert data in DB
     *
     * @param args $customerId
     * @param args $productId
     */
    public function addData($name, $email, $mobile, $specialization, $createdAt, $cv, $cvFileName)
    {
        $thanks_message = [];
        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('sigma_careers');
        $sql = $sql = "INSERT INTO " . $tableName . " (name, email, mobile, specialization, cv, created_at, cv_file_name) VALUES ('" . $name . "', '" . $email . "', '" . $mobile . "', '" . $specialization . "',  '" . $cv . "','" . $createdAt . "', '". $cvFileName ."')";
        $connection->query($sql);
        $thanks_message['success_message'] = "CV has been uploaded successfully";
        //Send Mail
        if ($thanks_message['success_message'] != null) {
            $this->createBlock->sendMail($name, $email, $mobile, $specialization, $cv);
            $this->createBlock->acknowledgeMail($name, $email);
        }
        return $thanks_message;
    }
}
