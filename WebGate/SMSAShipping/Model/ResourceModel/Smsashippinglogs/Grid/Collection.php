<?php
    
    namespace WebGate\SMSAShipping\Model\ResourceModel\Smsashippinglogs\Grid;
    
    use Magento\Framework\Api\Search\SearchResultInterface;
    use Magento\Framework\Api\SearchCriteriaInterface;
    use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
    use Magento\Framework\Data\Collection\EntityFactoryInterface;
    use Magento\Framework\Event\ManagerInterface;
    use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
    use Magento\Framework\Search\AggregationInterface;
    use Psr\Log\LoggerInterface;
    use WebGate\SMSAShipping\Model\ResourceModel\Smsashippinglogs\Collection as SmsashippinglogsCollection;
    
    /**
     * Class Collection
     * Collection for displaying grid
     */
    class Collection extends SmsashippinglogsCollection implements SearchResultInterface
    {
        /**
         * @var AggregationInterface
         */
        protected $aggregations;
    
        /**
         * @param EntityFactoryInterface $entityFactory
         * @param LoggerInterface $logger
         * @param FetchStrategyInterface $fetchStrategy
         * @param ManagerInterface $eventManager
         * @param mixed|null $mainTable
         * @param AbstractDb $eventPrefix
         * @param mixed $eventObject
         * @param mixed $resourceModel
         * @param string $model
         * @param null $connection
         * @param AbstractDb|null $resource
         *
         * @SuppressWarnings(PHPMD.ExcessiveParameterList)
         */
        public function __construct(
            EntityFactoryInterface $entityFactory,
            LoggerInterface $logger,
            FetchStrategyInterface $fetchStrategy,
            ManagerInterface $eventManager,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
            $connection = null,
            AbstractDb $resource = null
        ) {
            parent::__construct(
                $entityFactory,
                $logger,
                $fetchStrategy,
                $eventManager,
                $connection,
                $resource
            );
            $this->_eventPrefix = $eventPrefix;
            $this->_eventObject = $eventObject;
            $this->_init($model, $resourceModel);
            $this->setMainTable($mainTable);
        }
    
        /**
         * @return AggregationInterface
         */
        public function getAggregations()
        {
            return $this->aggregations;
        }
    
        /**
         * @param AggregationInterface $aggregations
         * @return $this
         */
        public function setAggregations($aggregations)
        {
            $this->aggregations = $aggregations;
        }
    
    
        /**
         * Retrieve all ids for collection
         * Backward compatibility with EAV collection
         *
         * @param int $limit
         * @param int $offset
         * @return array
         */
        public function getAllIds($limit = null, $offset = null)
        {
            return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
        }
    
        /**
         * Get search criteria.
         *
         * @return SearchCriteriaInterface|null
         */
        public function getSearchCriteria()
        {
            return null;
        }
    
        /**
         * Set search criteria.
         *
         * @param SearchCriteriaInterface $searchCriteria
         * @return $this
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
        {
            return $this;
        }
    
        /**
         * Get total count.
         *
         * @return int
         */
        public function getTotalCount()
        {
            return $this->getSize();
        }
    
        /**
         * Set total count.
         *
         * @param int $totalCount
         * @return $this
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function setTotalCount($totalCount)
        {
            return $this;
        }
    
        /**
         * Set items list.
         *
         * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
         * @return $this
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function setItems(array $items = null)
        {
            return $this;
        }
    
        /**
         * setFilterGroups search criteria.
         *
         * @param \Magento\Framework\Api\Search\FilterGroup[] $getFilterGroups
         */
        public function setFilterGroups(SearchCriteriaInterface $searchCriteria)
        {
            foreach($searchCriteria->getFilterGroups() as $filterGroup)
            {
                foreach($filterGroup->getFilters() as $filter)
                {
                    $condition = $filter->getConditionType() ? : 'eq';
                    $this->addFieldToFilter($filter->getField() , [ $condition => $filter->getValue() ]);
                }
            }
        
            return $this;
        }
    
        /**
         * setOrders search criteria.
         *
         * @param \Magento\Framework\Api\SortOrder[]|null $orders
         */
        public function setOrders(SearchCriteriaInterface $searchCriteria)
        {
            $sortOrdersData = $searchCriteria->getSortOrders();
            if($sortOrdersData)
            {
                foreach($sortOrdersData as $sortOrder)
                {
                    $this->addOrder(
                        $sortOrder->getField() ,
                        ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                    );
                }
            }
        
            return $this;
        }

    }
