<?php
namespace Sigma\Careers\Api\Data;

interface GridInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const NAME = 'name';
    const EMAIL = 'email';
    const MOBILE = 'mobile';
    const SPECIALIZATION = 'specialization';
    const CV = 'cv';
    const CV_FILE_NAME = 'cv_file_name';
    const CREATED_AT = 'created_at';
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
     * Get Name.
     *
     * @return varchar
     */
    public function getName();

    /**
     * Set Name
     */
    public function setName($name);
    /**
     * Get Email.
     *
     * @return varchar
     */
    public function getEmail();
    /**
     * Set Email
     */
    public function setEmail($email);
    /**
     * Get Specialization.
     *
     * @return varchar
     */
    public function getSpecialization();
     /**
     * Set Specialization.
     */
    public function setSpecialization($specialization);
    /**
     * Get Mobile.
     *
     * @return varchar
     */
    public function getMobile();
    /**
     * Set Mobile.
     */
    public function setMobile($mobile);
    /**
     * Get CV.
     *
     * @return varchar
     */
    public function getCV();
    /**
     * Set CV.
     */
    public function setCV($cv);
    /**
     * Get CV file name.
     */
    public function getCVFileName();
    /**
     * Set CV file name.
     */
    public function setCVFileName($cvFileName);
    /**
     * Get Created At.
     *
     * @return varchar
     */
    public function getCreatedAt();
    /**
     * Set Created At.
     */
    public function setCreatedAt($createdAt);
}
