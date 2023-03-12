<?php
namespace Sigma\ServiceModel\Block\ServiceModel;

use Sigma\ServiceModel\Api\Data\GridInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Url
     */
    private $urlManager;

    /**
     * @var \Sigma\ServiceModel\Model\ResourceModel\Grid\CollectionFactory
     */
    protected $gridCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Url $urlManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url $urlManager,
        \Sigma\ServiceModel\Model\ResourceModel\Grid\CollectionFactory $gridCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlManager = $urlManager;
        $this->gridCollectionFactory = $gridCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Service Model Requests'));
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Service Model Requests'));
        }
    }
    /**
     * @return string
     */
    public function getCreateUrl()
    {
        $urlManager = clone $this->urlManager;

        return $urlManager->getUrl('servicemodel/servicemodel/create');
    }
    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }

    /**
     * @return Listing\Column\DefaultColumn[]
     */
    public function getColumns()
    {
        $columns = [];

        $names = array_intersect($this->getChildNames(), $this->getGroupChildNames('column'));

        foreach ($names as $name) {
            $columns[$name] = $this->getChildBlock($name);
        }

        return $columns;
    }

    /**
     * @param Listing\Column\DefaultColumn $column
     * @param TicketInterface $item
     * @return string
     */
    public function getColumnHtml(Listing\Column\DefaultColumn $column, GridInterface $item)
    {
        $column->setItem($item);

        return $column->toHtml();
    }
    /**
     * @return object
     */
    public function getRequestCollection()
    {

        $collection = $this->gridCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomer()->getId());

        return $collection;
    }

}
