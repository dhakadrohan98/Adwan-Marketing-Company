<?php
/**
 * @category  Sigma
 * @package   Sigma_TopCategoryGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

namespace Sigma\TopCategoryGraphQl\Controller\Adminhtml\Category\Image;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;

/**
 * Sigma Adminhtml Category Image Upload Controller
 */
class Upload extends \Magento\Backend\App\Action
{
    /**
     * Image uploader Model
     *
     * @var \Magento\Catalog\Model\ImageUploader
     */
    protected $imageUploader;

    /**
     * @param context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'thumbnail_image');
        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
