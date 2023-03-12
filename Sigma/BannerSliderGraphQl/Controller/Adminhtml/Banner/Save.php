<?php
/**
 * @category  Sigma
 * @package   Sigma_BannerSliderGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */
declare(strict_types=1);

namespace Sigma\BannerSliderGraphQl\Controller\Adminhtml\Banner;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageplaza\BannerSlider\Controller\Adminhtml\Banner;
use Mageplaza\BannerSlider\Helper\Image;
use Mageplaza\BannerSlider\Model\BannerFactory;
use Mageplaza\BannerSlider\Model\Config\Source\Type;
use RuntimeException;

/**
 * Class Save the data
 */
class Save extends Banner
{
    /**
     * @var Js
     */
    public $jsHelper;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * Save constructor.
     *
     * @param Image $imageHelper
     * @param BannerFactory $bannerFactory
     * @param Registry $registry
     * @param Js $jsHelper
     * @param Context $context
     */
    public function __construct(
        Image $imageHelper,
        BannerFactory $bannerFactory,
        Registry $registry,
        Js $jsHelper,
        Context $context
    ) {
        $this->imageHelper = $imageHelper;
        $this->jsHelper = $jsHelper;

        parent::__construct($bannerFactory, $registry, $context);
    }

    /**
     * Execute function to save the data
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
// @codingStandardsIgnoreFile   
    public function execute()
    {
        $resultRedirect = $this
            ->resultRedirectFactory
            ->create();

        if ($this->getRequest()->getPost('banner')) {
            $data = $this->getRequest()
                ->getPost('banner');
            $files = $this->getRequest()
                ->getFiles();
            $banner = $this->initBanner();
            if ($files['image']['name']) {
                if ($data['type'] === Type::IMAGE) {
                    $this
                        ->imageHelper
                        ->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_BANNER, $banner->getImage());
                } else {
                    $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
                }
            }
            $data['sliders_ids'] = (isset($data['sliders_ids']) && $data['sliders_ids'])
                ? explode(',', $data['sliders_ids']) : [];
            if ($this->getRequest()->getPost('sliders', false)) {
                $banner->setTagsData(
                    $this->jsHelper->decodeGridSerializedInput($this->getRequest()->getPost('sliders', false))
                );
            }

            $banner->addData($data);

            $this->_eventManager->dispatch(
                'mpbannerslider_banner_prepare_save',
                [
                    'banner' => $banner,
                    'request' => $this->getRequest()
                ]
            );

            try {
                if (isset($data['banner_id'])) {
                    if (!$files['image']['name'] || $data['image']) {
                        $banner->save();
                        $this
                            ->messageManager
                            ->addSuccess(__('The Banner has been saved.'));
                        $this->_session->setMageplazaBannerSliderBannerData(false);
                        if ($this->getRequest()->getParam('back')) {
                            $resultRedirect->setPath(
                                'mpbannerslider/*/edit',
                                [
                                    'banner_id' => $banner->getId(),
                                    '_current' => true
                                ]
                            );

                            return $resultRedirect;
                        }
                    } else {
                        $this
                            ->messageManager
                            ->addError(__("Please select valid image file"));
                        $data = $this
                            ->_session
                            ->getData('mpbannerslider_banner_data', true);
                        if (!empty($data)) {
                            $banner->setData($data);
                        }
                        $this->_getSession()
                            ->setData('mageplaza_bannerSlider_banner_data', $data);
                        
                        $this
                            ->_session
                            ->setMageplazaBannerSliderBannerData(true);
                        $resultRedirect->setPath(
                            'mpbannerslider/*/edit',
                            [
                                    'banner_id' => $banner->getId(),
                                    '_current' => true
                                ]
                        );
                        
                        return $resultRedirect;
                    }
                } else {
                    if ($data['image']) {
                        $banner->save();
                        $this
                            ->messageManager
                            ->addSuccess(__('The Banner has been saved.'));
                        $this
                            ->_session
                            ->setMageplazaBannerSliderBannerData(false);
                        if ($this->getRequest()
                            ->getParam('back')) {
                            $resultRedirect->setPath(
                                'mpbannerslider/*/edit',
                                [
                                    'banner_id' => $banner->getId(),
                                    '_current' => true
                                ]
                            );

                            return $resultRedirect;
                        }
                    } else {
                        $this
                            ->messageManager
                            ->addError(__("Please select valid image file"));
                        $this
                            ->_session
                            ->getData('mpbannerslider_banner_data', true);
                        $this
                            ->_session
                            ->setMageplazaBannerSliderBannerData(true);
                        $resultRedirect->setPath('mpbannerslider/*/new', ['_current' => true]);

                        return $resultRedirect;
                    }
                }
                $resultRedirect->setPath('mpbannerslider/*/');

                return $resultRedirect;
            } catch (RuntimeException $e) {
                $this
                    ->messageManager
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this
                    ->messageManager
                    ->addException($e, __('Something went wrong while saving the Banner.'));
            }

            $this->_getSession()
                ->setData('mageplaza_bannerSlider_banner_data', $data);
            $resultRedirect->setPath('mpbannerslider/*/edit', ['banner_id' => $banner->getId() , '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mpbannerslider/*/');

        return $resultRedirect;
    }
}
