<?php
namespace Sigma\ServiceModel\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Sigma\ServiceModel\Model\ResourceModel\Grid\Collection;

class AdminId extends Column
{
   /**
     * @var \Magento\Customer\Model\Customer $customer
     */
    protected $customers;

    /**
     * @var \Magento\User\Model\UserFactory
    */
    protected $_userFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\Customer $customers,
        \Sigma\ServiceModel\Model\GridFactory $gridCollectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\User\Model\UserFactory $userFactory,
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
        $this->gridCollectionFactory = $gridCollectionFactory;
        $this->_downloader = $fileFactory;
        $this->directory = $directory;
        $this->storeManager = $storeManager;
        $this->_userFactory = $userFactory;
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
                    $admin_id = $item['admin_user_id'];
                    $user = $this->_userFactory->create()->load($admin_id)->getUserName();
                        $item[$fieldName] = $user;

                }
            }
        }
        return $dataSource;
    }
}
