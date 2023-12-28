<?php
namespace Sigma\ServiceModel\Controller\Adminhtml\Servicemodel;
use Sigma\ServiceModel\Block\ServiceModel\Create;
class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Sigma_ServiceModel::save';

    /**
     * @var \Webkul\Grid\Model\GridFactory
     */
    protected $gridFactory;

    protected $_mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory $adapterFactory
     */
    protected $adapterFactory;
    /**
     * @var \Sigma\ServiceModel\Block\ServiceModel\Create $createBlock
     */
    protected $createBlock;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customers;
    /**
     * @var \Sigma\ServiceModel\Model\ResourceModel\Grid\CollectionFactory
     */
    protected $gridCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\Grid\Model\GridFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Sigma\ServiceModel\Model\GridFactory $gridFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Sigma\ServiceModel\Block\ServiceModel\Create $createBlock,
        \Magento\Customer\Model\Customer $customers,
        \Sigma\ServiceModel\Model\ResourceModel\Grid\CollectionFactory $gridCollectionFactory
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->createBlock = $createBlock;
        $this->_customers = $customers;
        $this->gridCollectionFactory = $gridCollectionFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $file = $this->getRequest()->getFiles('admin_file');
        if (!$data) {
            $this->_redirect('servicemodel/servicemodel/addrow');
            return;
        }
        try{
            $rowData = $this->gridFactory->create();
            $uploaderFactory = $this->_fileUploaderFactory->create(['fileId' => 'admin_file']);
            $uploaderFactory->setAllowedExtensions(['pdf','csv']);
            $fileAdapter = $this->adapterFactory->create();
            $uploaderFactory->setAllowRenameFiles(true);
            $destinationPath = $this->_mediaDirectory->getAbsolutePath('adminrequests/');
            $result = $uploaderFactory->save($destinationPath);
            $data['admin_file'] = $result['file'];
            $data['admin_file_name'] = $result['name'];
            $data['is_reply'] = 1;
            $rowData->setData($data);

            if (isset($data['id'])) {
                $rowData->setId($data['id']);
            }
            $rowData->save();
            $collection = $this->gridCollectionFactory->create()->addFieldToFilter('id', $data['id']);
            $customerId = $collection->getFirstItem()->getCustomerId();
            $customer = $this->_customers->load($customerId);
            $name = $customer->getFirstname()." ".$customer->getLastname();
            $email = $customer->getEmail();
            $adminFile = $collection->getFirstItem()->getAdminFile();
            $adminFileName = $collection->getFirstItem()->getAdminFileName();
            $this->createBlock->sendMailFromAdmin($name, $email, $adminFile, $adminFileName);

            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('servicemodel/index/index');
    }

    /**
     * Is the user allowed to view the page.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}
