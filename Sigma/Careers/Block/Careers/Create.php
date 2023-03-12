<?php
namespace Sigma\Careers\Block\Careers;

use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;

class Create extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $_scopeConfig;
    /**
     * @var \Psr\Log\LoggerInterface $loggerInterface
     */
    protected $_logLoggerInterface;
    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Url
     */
    private $urlManager;
    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Url $urlManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url $urlManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlManager = $urlManager;
        $this->customerSession = $customerSession;
        $this->_date =  $date;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->storeManager = $storeManager;
    }


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Careers'));
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Careers'));
        }
    }
    /**
     * Get Current Date
     *
     * @return date
     */
    public function getCurrentDate()
    {
        return $this->_date->date()->format('Y-m-d H:i:s');
    }
    /**
     * Send Mail
     *
     */
    public function sendMail($name, $email, $mobile, $specialization, $cv)
    {
        $sender = [
            'name' => $name,
            'email' => $email
        ];
        $sentToEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sentToName = $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $cvUrl = $mediaUrl . "cv/" . $cv;

        $transport = $this->_transportBuilder->setTemplateIdentifier('careers_email_template')->setTemplateOptions(
            [
                'area' => 'frontend',
                'store' => $this->storeManager->getStore()->getId()
            ]
        )
            ->setTemplateVars([
                'name'  => $name,
                'email'  => $email,
                'mobile' => $mobile,
                'specialization' => $specialization,
                'cvUrl' => $cvUrl,
                'cvName' => $cv
            ])
            ->setFromByScope($sender)
            ->addTo($sentToEmail, $sentToName)
            ->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();

    }
    /**
     * Send Acknowledge Mail
     *
     */
    public function acknowledgeMail($name, $email)
    {
        $sender = [
            'name' => $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'email' => $this->_scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ];
        $sentToEmail = $email;
        $sentToName = $name;

        $transport = $this->_transportBuilder->setTemplateIdentifier('acknowledge_email_template')->setTemplateOptions(
            [
                'area' => 'frontend',
                'store' => $this->storeManager->getStore()->getId()
            ]
        )
            ->setTemplateVars([
                'name'  => $name,
                'email'  => $email
            ])
            ->setFromByScope($sender)
            ->addTo($sentToEmail, $sentToName)
            ->getTransport();
        $transport->sendMessage();
        $this->_inlineTranslation->resume();
    }

}
