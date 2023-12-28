<?php
namespace Sigma\ServiceModel\Model;

use Sigma\ServiceModel\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'sigma_service';
    /**
     * @var string
     */
    protected $_cacheTag = 'sigma_service';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'sigma_service';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Sigma\ServiceModel\Model\ResourceModel\Grid');

    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
     /**
     * Get Customer Id.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set Customer Id.
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Customer File.
     *
     * @return varchar
     */
    public function getCustomerFile()
    {
        return $this->getData(self::CUSTOMER_FILE);
    }

    /**
     * Set  Customer File.
     */
    public function setCustomerFile($customerFile)
    {
        return $this->setData(self::CUSTOMER_FILE, $customerFile);
    }

    /**
     * Get InsertDate.
     *
     * @return varchar
     */
    public function getInsertDate()
    {
        return $this->getData(self::INSERT_DATE);
    }

    /**
     * Set InsertDate.
     */
    public function setInsertDate($insertDate)
    {
        return $this->setData(self::INSERT_DATE, $insertDate);
    }
     /**
     * Get Reply.
     *
     * @return int
     */
    public function getIsReply()
    {
        return $this->getData(self::IS_REPLY);
    }

    /**
     * Set Reply.
     */
    public function setIsReply($isReply)
    {
        return $this->setData(self::IS_REPLY, $isReply);
    }

     /**
     * Get ReplyDate.
     *
     * @return varchar
     */
    public function getReplyDate()
    {
        return $this->getData(self::REPLY_DATE);
    }

    /**
     * Set ReplyDate.
     */
    public function setReplyDate($replyDate)
    {
        return $this->setData(self::REPLY_DATE, $replyDate);
    }
     /**
     * Get Admin User Id.
     *
     * @return int
     */
    public function getAdminUserId()
    {
        return $this->getData(self::ADMIN_USER_ID);
    }

    /**
     * Set Admin User Id.
     */
    public function setgetAdminUserId($adminUserId)
    {
        return $this->setData(self::ADMIN_USER_ID, $adminUserId);
    }
    /**
     * Get Admin File.
     *
     * @return varchar
     */
    public function getAdminFile()
    {
        return $this->getData(self::ADMIN_FILE);
    }

    /**
     * Set Admin File.
     */
    public function setAdminFile($adminFile)
    {
        return $this->setData(self::ADMIN_FILE, $adminFile);
    }
    /**
     * Get Customer File.
     *
     * @return varchar
     */
    public function getCustomerFileName()
    {
        return $this->getData(self::CUSTOMER_FILE_NAME);
    }

    /**
     * Set  Customer File.
     */
    public function setCustomerFileName($customerFileName)
    {
        return $this->setData(self::CUSTOMER_FILE_NAME, $customerFileName);
    }
    /**
     * Get Admin File Name.
     *
     * @return varchar
     */
    public function getAdminFileName()
    {
        return $this->getData(self::ADMIN_FILE);
    }

    /**
     * Set Admin File Name.
     */
    public function setAdminFileName($adminFileName)
    {
        return $this->setData(self::ADMIN_FILE_NAME, $adminFileName);
    }
}
