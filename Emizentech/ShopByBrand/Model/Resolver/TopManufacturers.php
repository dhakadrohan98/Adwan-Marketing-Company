<?php

declare(strict_types=1);

namespace Emizentech\ShopByBrand\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class TopManufacturers implements ResolverInterface
{
    protected $_brandFactory;

    public function __construct(
        \Emizentech\ShopByBrand\Model\BrandFactory $brandFactory
    ) {
        $this->_brandFactory = $brandFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null)
    {

        $output = [];
        $topBrands = $this->getFeaturedBrands();

     
        foreach ($topBrands as $_brands) {

            $output[] = [
                'id' => $_brands->getId(),
                'name' => $_brands->getName(),
                'url_key' => $_brands->getUrlKey(),
                'logo' => $_brands->getLogo(),
            ];
        }

        return ['items' => $output];
    }

    public function getFeaturedBrands(){
        $collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('featured' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->setOrder('sort_order' , 'ASC');
        return $collection;
    }
}