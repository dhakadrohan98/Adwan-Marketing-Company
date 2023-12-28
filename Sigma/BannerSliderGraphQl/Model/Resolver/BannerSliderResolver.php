<?php
/**
 * @category  Sigma
 * @package   Sigma_BannerSliderGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\BannerSliderGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Mageplaza\BannerSlider\Model\BannerFactory;

/**
 * Retrieves Top Manufacturers Data
 */
class BannerSliderResolver implements ResolverInterface
{
    /**
     * @var \Mageplaza\BannerSlider\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $_bannerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var BannerFactory
     */
    public $bannerFactory;

    /**
     * @param CollectionFactory $bannerCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param BannerFactory $bannerFactory
     */
    public function __construct(
        \Mageplaza\BannerSlider\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BannerFactory $bannerFactory
    ) {
        $this->_bannerCollectionFactory = $bannerCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->bannerFactory = $bannerFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /*if (!isset($args['banner_id'])) {
            throw new GraphQlInputException(__('No banners found.'));
        }*/

        /**
         *  get storeName for store
         */
        $storeName = $this->getStoreName($args);
        if ($storeName == "EN") {
            $id = 1;
        } else {
            $id = 2;
        }
        
        $output = [];
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $collection = $this->bannerFactory->create()->getCollection();

        $collection->join(
            ['banner_slider' => $collection->getTable('mageplaza_bannerslider_banner_slider')],
            'main_table.banner_id=banner_slider.banner_id AND banner_slider.slider_id=' . $id,
            ['position']
        );

        $collection->addOrder('position', 'ASC');

        foreach ($collection as $banner) {
            $output[] = [
                'banner_id' => $banner->getBannerId(),
                'status' => $banner->getStatus(),
                'name' => $banner->getName(),
                'content' => $banner->getContent(),
                'image' => $banner->getImageUrl(),
                'title' => $banner->getTitle(),
                'url_banner' => $banner->getUrlBanner(),
                'newtab' => $banner->getNewtab()
            ];
        }

        return ['items' => $output];
    }

    /**
     * Passed store_name from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getStoreName(array $args)
    {
        if (!isset($args['storeName'])) {
            throw new GraphQlInputException(__('"storeName should be specified'));
        }
        return $args['storeName'];
    }
}
