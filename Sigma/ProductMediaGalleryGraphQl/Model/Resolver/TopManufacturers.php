<?php
/**
 * @category  Sigma
 * @package   Sigma_ShopByBrandGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\ShopByBrandGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Retrieves Top Manufacturers Data
 */
class TopManufacturers implements ResolverInterface
{
    /**
     * @var \Emizentech\ShopByBrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param BrandFactory $brandFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Emizentech\ShopByBrand\Model\BrandFactory $brandFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $storeManager;
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

        $output = [];
        $topBrands = $this->getFeaturedBrands();
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        foreach ($topBrands as $_brands) {
           
            $output[] = [
                'id' => $_brands->getId(),
                'name' => $_brands->getName(),
                'url_key' => $_brands->getUrlKey(),
                'logo' => $mediaUrl.$_brands->getLogo(),
            ];
        }

        return ['items' => $output];
    }

    /**
     *  Get Featured Brands Collection
     *
     * @return array
     */
    public function getFeaturedBrands()
    {
        $collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('featured', \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->setOrder('sort_order', 'ASC');
        return $collection;
    }
}
