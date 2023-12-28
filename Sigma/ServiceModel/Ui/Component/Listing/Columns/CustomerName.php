<?php
namespace Sigma\ServiceModel\Ui\Component\Listing\Columns;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerName extends Column
{
    /**
     * @var \Magento\Customer\Model\Customer $customer
     */
    protected $customers;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\Customer $customers,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->_customers = $customers;
    }
    /**
     * Return Customer Name from Customer Id
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $customerId = $item['customer_id'];
                    $customer = $this->_customers->load($customerId);
                    $item[$fieldName] = $customer->getFirstname()." ".$customer->getLastname();
                }
            }
        }

        return $dataSource;
    }
}
