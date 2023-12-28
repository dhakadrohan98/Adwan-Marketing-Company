<?php
/**
 * @category  Sigma
 * @package   Sigma_TopCategoryGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\TopCategoryGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\ViewModel\Category\Image;

/**
 * Resolver for Top Category
 */
class TopCategory implements ResolverInterface
{

    /**
     * Category Collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionfactory;

    /**
     * ScopeConfig Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_storeManager;

    /**
     * Image View Model
     *
     * @var \Magento\Catalog\ViewModel\Category\Image
     */
    public $image;

    /**
     * @param context $context
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $categoryCollectionfactory
     * @param Image $Image
     * @param data $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollectionfactory,
        Image $Image,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->image = $Image;
        $this->_categoryCollectionfactory = $categoryCollectionfactory;
    }

    /**
     *  Get Top Category for home page
     *
     * @return Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public function getTopCategory()
    {
        return $this->_categoryCollectionfactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('top_category', 1)
                    ->setStore($this->_storeManager->getStore())
                    ->setPageSize(10);
    }

    /**
     * Retrieve category image src
     *
     * @param object $category Magento\Catalog\Model\Category
     *
     * @return string|null
     */
    public function getCategoryImageSrc(\Magento\Catalog\Model\Category $category)
    {
        return $this->image->getUrl($category, 'thumbnail_image');
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

        $categoryCollection = $this->getTopCategory();

        foreach ($categoryCollection as $_category) {

            // get Image source of thumbnail_image
            $Imageurl = $this->getCategoryImageSrc($_category);
            $output[] = [
                'id' => $_category->getId(),
                'name' => $_category->getName(),
                'url_key' => $_category->getUrlPath(),
                'thumbnail_image' => $Imageurl,
                'description' => $_category->getDescription()
                ];
        }
            return ['items' => $output];
    }
}
