<?php
namespace Sigma\ServiceModel\Controller\Adminhtml\Servicemodel;


use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Sigma\ServiceModel\Model\ResourceModel\Grid\CollectionFactory;
use Sigma\ServiceModel\Model\GridFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.​_
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        GridFactory $gridFactory
    ) {

        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->gridFactory = $gridFactory;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        try {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        $recordDeleted = 0;
        foreach ($collection as $record) {
            $record = $this->gridFactory->create()->load($record->getId());
            $record->delete();
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));
        } catch (\Exception $e) {
        $this->messageManager->addError(__($e->getMessage()));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('servicemodel/index/index');
    }
    /**
     * Check Category Map recode delete Permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sigma_ServiceModel::row_data_delete');
    }
}
