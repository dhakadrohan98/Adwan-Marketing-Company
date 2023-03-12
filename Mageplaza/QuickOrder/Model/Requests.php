<?php

namespace Mageplaza\QuickOrder\Model;

use Magento\Rule\Model\AbstractModel;
use Mageplaza\QuickOrder\Api\Data\RequestsInterface;

/**
 * Class Requests
 * @package Mageplaza\QuickOrder\Model
 */
class Requests extends AbstractModel implements RequestsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string[] $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getConditionsInstance()
    {
        // TODO: Implement getConditionsInstance() method.
    }

    public function getActionsInstance()
    {
        // TODO: Implement getActionsInstance() method.
    }
}
