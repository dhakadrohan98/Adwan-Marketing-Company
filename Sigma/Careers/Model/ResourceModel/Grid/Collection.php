<?php

/**
 * @category Sigma
 * @author SigmaInfo Team
 */

namespace Sigma\Careers\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'sigma_careers_grid_collection';
    protected $_eventObject = 'grid_collection';

    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        //$this->_init('Sigma\Careers\Model\Grid', 'Sigma\Careers\Model\ResourceModel\Grid');
    }
}
