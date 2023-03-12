<?php
    /**
     * Hello Rewrite Product View Controller
     *
     * @category    Webkul
     * @package     Webkul_Hello
     * @author      Webkul Software Private Limited
     *
     */
    namespace Dotsquares\FilesCollection\Controller\Rewrite\Product;
 
    class View extends \Magento\Catalog\Controller\Product\View
    {
        /**
         * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
         */
        public function execute()
        {
			
			//echo 'test'; //die();
			
            // Do your stuff here
            return parent::execute();
        }
    }