<?php
namespace Sigma\ServiceModel\Block\Adminhtml\Servicemodel\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $authSession;

    protected $date;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->authSession = $authSession;
        $this->_date = $date;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {


        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('row_data');
        $model->setAdminUserId($this->authSession->getUser()->getId());
        $model->setReplyDate($this->_date->date()->format('Y-m-d H:i:s'));
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );

        $form->setHtmlIdPrefix('wkticket_');
        if ($model->getId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Row Data'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
            $fieldset->addField('admin_user_id', 'hidden', ['name' => 'admin_user_id', 'value' => $this->authSession->getUser()->getId()]);
            $fieldset->addField('reply_date', 'hidden', ['name' => 'reply_date', 'value' => $this->_date->date()->format('Y-m-d H:i:s')]);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Row Data'), 'class' => 'fieldset-wide']
            );
        }
        $fieldset->addField(
            'title',
            'file',
            [
                'name' => 'admin_file',
                'label' => __('Admin File'),
                'id' => 'afmin_file',
                'title' => __('Admin File'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
