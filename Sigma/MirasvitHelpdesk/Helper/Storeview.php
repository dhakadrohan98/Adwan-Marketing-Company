<?php

namespace Sigma\MirasvitHelpdesk\Helper;

class Storeview extends \Mirasvit\Helpdesk\Helper\Storeview
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Storeview constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->storeManager     = $storeManager;

        parent::__construct($storeManager, $context);
    }
    /**
     * @param \Magento\Framework\DataObject $object
     * @param string                        $field
     * @param string                        $value
     *
     * @return void
     */
    public function setStoreViewValue($object, $field, $value)
    {
        $storeId = (int) $object->getStoreId();
        $serializedValue = $object->getData($field);
        $arr = $this->unserialize($serializedValue);

        if ($storeId === 0) {
            $arr[0] = $value;
        } else {
            $arr[$storeId] = $value;
            if (!isset($arr[0])) {
                $arr[0] = $value;
            }
        }
        $object->setData($field, $arr[0]);
    }

}
