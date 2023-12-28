<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Controller\Adminhtml\Items;

class NewAction extends \Dotsquares\FilesCollection\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
