<?php

namespace Mageplaza\QuickOrder\Model;

use Magento\Rule\Model\AbstractModel;
use Mageplaza\QuickOrder\Api\Data\ItemUpdateRequestInterface;

/**
 * Class ItemUpdateRequest
 * @package Mageplaza\QuickOrder\Model
 */
class ItemUpdateRequest extends AbstractModel implements ItemUpdateRequestInterface
{


    public function getConditionsInstance()
    {
        // TODO: Implement getConditionsInstance() method.
    }

    public function getActionsInstance()
    {
        // TODO: Implement getActionsInstance() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setItemId($value)
    {
        return $this->setData(self::ITEM_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
