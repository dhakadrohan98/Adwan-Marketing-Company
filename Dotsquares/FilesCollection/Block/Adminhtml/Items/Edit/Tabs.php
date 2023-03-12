<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */
namespace Dotsquares\FilesCollection\Block\Adminhtml\Items\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('dotsquares_filescollection_items_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('File Information'));
    }
}
