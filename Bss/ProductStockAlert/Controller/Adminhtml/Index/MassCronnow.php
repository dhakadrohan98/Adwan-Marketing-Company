<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory;
use Bss\ProductStockAlert\Model\Stock as Stock;

class MassCronnow extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    protected $model;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param \Bss\ProductStockAlert\Model\StockEmailProcessor $stockEmailProcessor
     * @param CollectionFactory $collectionFactory
     * @param Stock $model
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Bss\ProductStockAlert\Model\StockEmailProcessor $stockEmailProcessor,
        CollectionFactory $collectionFactory,
        Stock $model
    ) {
        $this->filter = $filter;
        $this->stockEmailProcessor = $stockEmailProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $this->stockEmailProcessor->process($collection);
        } catch (\Exception $e) {
            throw new \LogicException(__($e->getMessage()));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ProductStockAlert::manage');
    }
}
