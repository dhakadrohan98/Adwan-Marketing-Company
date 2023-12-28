<?php

namespace Mageplaza\QuickOrder\Model;

use Magento\Rule\Model\AbstractModel;
use Mageplaza\QuickOrder\Api\Data\ItemUpdateRequestInterface;
use Mageplaza\QuickOrder\Api\Data\UpdateRequestInterface;

/**
 * Class UpdateRequests
 * @package Mageplaza\QuickOrder\Model
 */
class UpdateRequests extends AbstractModel implements UpdateRequestInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * @param ItemUpdateRequestInterface[] $value
     *
     * @return $this
     */
    public function setItems($value)
    {
        return $this->setData(self::ITEMS, $value);
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
