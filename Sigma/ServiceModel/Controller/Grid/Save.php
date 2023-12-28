<?php
namespace Sigma\ServiceModel\Controller\Grid;

use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Action\Context;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Sigma\ServiceModel\Model\GridFactory
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
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Sigma\ServiceModel\Model\GridFactory $gridFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory
    ) {
        $this->gridFactory = $gridFactory;
        $this->_pageFactory = $pageFactory;
        $this->filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        return parent::__construct($context);
    }
    /**
     * View page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $file = $this->getRequest()->getFiles('customer_file');
        try{
            $rowData = $this->gridFactory->create();
            $uploaderFactory = $this->_fileUploaderFactory->create(['fileId' => 'customer_file']);
            $uploaderFactory->setAllowedExtensions(['pdf','png']);
            $fileAdapter = $this->adapterFactory->create();
            $uploaderFactory->setAllowRenameFiles(true);
            $destinationPath = $this->_mediaDirectory->getAbsolutePath('allrequests/');
            $result = $uploaderFactory->save($destinationPath);
            $data['customer_file'] = $result['file'];
            $data['customer_file_name'] = $result['name'];
            $rowData->setData($data);

            if (isset($data['id'])) {
                $rowData->setId($data['id']);
            }
                $rowData->save();
                $this->messageManager->addSuccess(__('Request sent successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('servicemodel/servicemodel/index');
    }
}

