<?php
/**
 * @category  Sigma
 * @package   Sigma_SubCategoryGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\SubCategoryGraphQl\Model\Resolver;

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
 * Resolver for Sub Category Class
 */
class SubCategory implements ResolverInterface
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
     * Get Sub Category for home page
     *
     * @param categoryId $categoryId
     *
     * @return Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public function getSubCategory($categoryId)
    {
        return $this->_categoryCollectionfactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToFilter('parent_id', $categoryId)
                    ->setStore($this->_storeManager->getStore());
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

        /**
         *  get categoryId for category
         */
        $categoryId = $this->getId($args);

        $categoryCollection = $this->getSubCategory($categoryId);

        $output = [];
        foreach ($categoryCollection as $_category) {

            // get Image source of thumbnail_image
            $Imageurl = $this->getCategoryImageSrc($_category);
            $output[] = [
                'id' => $_category->getEntityId(),
                'name' => $_category->getName(),
                'url_key' => $_category->getUrlPath(),
                'thumbnail_image' => $Imageurl,
                ];
        }
            return ['items' => $output];
    }

    /**
     * Passed Id from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getId(array $args)
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"id should be specified'));
        }
        return $args['id'];
    }
}
