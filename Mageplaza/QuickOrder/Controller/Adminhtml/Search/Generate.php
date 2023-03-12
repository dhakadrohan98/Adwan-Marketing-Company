<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\QuickOrder\Controller\Adminhtml\Search;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\QuickOrder\Helper\Search;

/**
 * Class Generate
 * @package Mageplaza\QuickOrder\Controller\Adminhtml\Search
 */
class Generate extends Action
{
    /**
     * @var Search
     */
    protected $_helperSearch;

    /**
     * Generate constructor.
     *
     * @param Context $context
     * @param Search $helperSearch
     */
    public function __construct(
        Context $context,
        Search $helperSearch
    ) {
        $this->_helperSearch = $helperSearch;

        parent::__construct($context);
    }

    /**
     * execute js file data for all store & customer group
     * then redirect back to the system page
     */
    public function execute()
    {
        $errors = $this->_helperSearch->createJsonFile();
        if (empty($errors)) {
            $this->messageManager->addSuccessMessage(__('Generate search data successfully.'));
        } else {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        $this->_redirect('adminhtml/system_config/edit/section/quickorder');
    }
}
