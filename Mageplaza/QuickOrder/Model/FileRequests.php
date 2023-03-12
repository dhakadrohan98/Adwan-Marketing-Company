<?php

namespace Mageplaza\QuickOrder\Model;

use Magento\Rule\Model\AbstractModel;
use Mageplaza\QuickOrder\Api\Data\FileRequestsInterface;

/**
 * Class FileRequests
 * @package Mageplaza\QuickOrder\Model
 */
class FileRequests extends AbstractModel implements FileRequestsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
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
