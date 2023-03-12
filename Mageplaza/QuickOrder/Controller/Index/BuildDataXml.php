<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\QuickOrder\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class BuildDataXml
 * @package Mageplaza\QuickOrder\Controller\Index
 */
class BuildDataXml extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * BuildDataXml constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $content = $this->getRequest()->getParam('items');
        $items = simplexml_load_string($content);
        $result = ['SKU,QTY,Option1:value1,Option2:value2'];

        foreach ($items as $item) {
            $node = implode(',', (array)$item);
            $result[] = $node;
        }

        return $this->jsonFactory->create()->setData($result);
    }
}
