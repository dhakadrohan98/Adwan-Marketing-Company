<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Controller\Adminhtml\Items;

class Index extends \Dotsquares\FilesCollection\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dotsquares_FilesCollection::filescollection');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Attachments'));
        $resultPage->addBreadcrumb(__('Dotsquares'), __('Dotsquares'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}
