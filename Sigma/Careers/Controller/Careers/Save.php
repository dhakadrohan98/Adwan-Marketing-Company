<?php

namespace Sigma\Careers\Controller\Careers;

use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Action\Context;
use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
use Sigma\Careers\Block\Careers\Create;

class Save extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Sigma\Careers\Model\GridFactory
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
     * @var \Sigma\Careers\Block\Careers\Create $createBlock
     */
    protected $createBlock;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Sigma\Careers\Model\GridFactory $gridFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Sigma\Careers\Block\Careers\Create $createBlock,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager
    ) {
        $this->gridFactory = $gridFactory;
        $this->_pageFactory = $pageFactory;
        $this->filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->createBlock = $createBlock;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
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
        $file = $this->getRequest()->getFiles('cv');
        try {
            $rowData = $this->gridFactory->create();
            $uploaderFactory = $this->_fileUploaderFactory->create(['fileId' => 'cv']);
            $uploaderFactory->setAllowedExtensions(['pdf']);
            $fileAdapter = $this->adapterFactory->create();
            $uploaderFactory->setAllowRenameFiles(true);
            $destinationPath = $this->_mediaDirectory->getAbsolutePath('cv/');
            $result = $uploaderFactory->save($destinationPath);
            $data['cv'] = $result['file'];
            // $data['customer_file_name'] = $result['name'];
            $rowData->setData($data);

            if (isset($data['id'])) {
                $rowData->setId($data['id']);
            }
            $rowData->save();

            //Send Mail
            $this->createBlock->sendMail($data['name'], $data['email'], $data['mobile'], $data['specialization'], $data['cv']);
            $this->createBlock->acknowledgeMail($data['name'], $data['email']);

            $this->messageManager->addSuccess(__('CV Uploaded Successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('careers/careers/index');
    }
}
