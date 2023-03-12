<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Model;

class Items extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Dotsquares\FilesCollection\Model\Resourcenew\Items');
    }
}
