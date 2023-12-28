<?php
namespace Sigma\Careers\Model;

use Sigma\Careers\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'sigma_careers';
    /**
     * @var string
     */
    protected $_cacheTag = 'sigma_careers';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'sigma_careers';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Sigma\Careers\Model\ResourceModel\Grid');

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
     * Get Name.
     *
     * @return varchar
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set Customer Id.
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get Email.
     *
     * @return varchar
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set Email
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get Specialization.
     *
     * @return varchar
     */
    public function getSpecialization()
    {
        return $this->getData(self::MOBILE);
    }

    /**
     * Set Specialization.
     */
    public function setSpecialization($specialization)
    {
        return $this->setData(self::SPECIALIZATION, $specialization);
    }
    /**
     * Get Mobile.
     *
     * @return varchar
     */
    public function getMobile()
    {
        return $this->getData(self::MOBILE);
    }

    /**
     * Set Mobile.
     */
    public function setMobile($mobile)
    {
        return $this->setData(self::MOBILE, $mobile);
    }
    /**
     * Get CV.
     *
     * @return varchar
     */
    public function getCV()
    {
        return $this->getData(self::CV);
    }

    /**
     * Set CV.
     */
    public function setCV($cv)
    {
        return $this->setData(self::CV, $cv);
    }
    /**
     * Get CV file name.
     *
     * @return varchar
     */
    public function getCVFileName()
    {
        return $this->getData(self::CV_FILE_NAME);
    }

    /**
     * Set CV file name.
     */
    public function setCVFileName($cvFileName)
    {
        return $this->setData(self::CV_FILE_NAME, $cvFileName);
    }
    /**
     * Get Created At.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
