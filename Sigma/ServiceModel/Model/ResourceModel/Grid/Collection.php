<?php
namespace Sigma\ServiceModel\Model\ResourceModel\Grid;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'sigma_servicemodel_grid_collection';
    protected $_eventObject = 'grid_collection';


    protected function _construct()
    {
        // $this->_init(
        //     \Sigma\ServiceModel\Model\Grid::class,
        //     \Sigma\ServiceModel\Model\ResourceModel\Grid::class
        // );
    }

    public function _beforeLoad()
    {

        $this->getSelect()->joinLeft(
            ['customer_entity' => $this->getTable('customer_entity')],
            'customer_entity.entity_id = main_table.customer_id',
            ['customername' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"]
        );
        $this->getSelect()->joinLeft(
            ['admin_user' => $this->getTable('admin_user')],
            'admin_user.user_id = main_table.admin_user_id',
            ['adminname' => "admin_user.firstname"]
        );

        return parent::_beforeLoad();
    }

    public function loadWithFilter(
        $printQuery = false,
        $logQuery = false
    ) {
        $this->getSelect()->columns(['customername' =>  "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"]);
        $this->getSelect()->columns(['adminname' =>  "admin_user.firstname"]);
        return parent::loadWithFilter($printQuery, $logQuery);
    }

    public function addFieldToFilter(
        $field,
        $condition = null
    ) {
        $fieldMap = $this->getFilterFieldsMap();
        if (is_array($field)) {
            foreach ($field as $singleField) {
                if (!isset($fieldMap['fields'][$singleField])) {
                    return parent::addFieldToFilter($field, $condition);
                }
            }
        } else {
            if (!isset($fieldMap['fields'][$field])) {
                return parent::addFieldToFilter($field, $condition);
            }
        }

        $fieldName = $fieldMap['fields'][$field];

        if (!in_array($field, ['customername'])) {
            $fieldName = $this->getConnection()->quoteIdentifier($fieldName);
        }
        // if (!in_array($field, ['adminname'])) {
        //     $fieldName = $this->getConnection()->quoteIdentifier($fieldName);
        // }

        $condition = $this->getConnection()->prepareSqlCondition($fieldName, $condition);
        $this->getSelect()->where($condition, null, \Magento\Framework\DB\Select::TYPE_CONDITION);

        return $this;
    }

    private function getFilterFieldsMap()
    {

        return [
            'fields' => [
                'customername' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)",
                'adminname' => "admin_user.firstname",
            ]
        ];
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('customername', 'customer_entity.customername');
        $this->addFilterToMap('adminname', 'admin_user.firstname');
        parent::_initSelect();
    }

}
