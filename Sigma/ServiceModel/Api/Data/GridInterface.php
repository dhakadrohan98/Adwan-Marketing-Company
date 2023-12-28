<?php
namespace Sigma\ServiceModel\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_FILE = 'customer_file';
    const INSERT_DATE = 'insert_date';
    const IS_REPLY = 'is_reply';
    const REPLY_DATE = 'reply_date';
    const ADMIN_USER_ID = 'admin_user_id';
    const ADMIN_FILE = 'admin_file';
    const CUSTOMER_FILE_NAME = 'customer_file_name';
    const ADMIN_FILE_NAME = 'admin_file_name';
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id.
     */
    public function setId($id);

     /**
     * Get Customer Id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set Customer Id.
     */
    public function setCustomerId($customerId);

    /**
     * Get Customer File.
     *
     * @return varchar
     */
    public function getCustomerFile();

    /**
     * Set  Customer File.
     */
    public function setCustomerFile($customerFile);

    /**
     * Get InsertDate.
     *
     * @return varchar
     */
    public function getInsertDate();

    /**
     * Set InsertDate.
     */
    public function setInsertDate($insertDate);

     /**
     * Get Reply.
     *
     * @return int
     */
    public function getIsReply();
    /**
     * Set Reply.
     */
    public function setIsReply($isReply);

     /**
     * Get ReplyDate.
     *
     * @return varchar
     */
    public function getReplyDate();

    /**
     * Set ReplyDate.
     */
    public function setReplyDate($replyDate);

     /**
     * Get Admin User Id.
     *
     * @return int
     */
    public function getAdminUserId();

    /**
     * Set Admin User Id.
     */
    public function setgetAdminUserId($adminUserId);

    /**
     * Get Admin File.
     *
     * @return varchar
     */
    public function getAdminFile();
    /**
     * Set Admin File.
     */
    public function setAdminFile($adimFile);
    /**
     * Get Admin File Name.
     *
     * @return varchar
     */
    public function getAdminFileName();
    /**
     * Set Admin File Name.
     */
    public function setAdminFileName($adimFile);
    /**
     * Get Customer File Name.
     *
     * @return varchar
     */
    public function getCustomerFileName();

    /**
     * Set  Customer File Name.
     */
    public function setCustomerFileName($customerFile);
}
