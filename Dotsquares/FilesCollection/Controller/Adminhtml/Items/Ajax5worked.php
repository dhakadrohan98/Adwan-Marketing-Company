<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Controller\Adminhtml\Items;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Vendor\Module\Model\ResourceModel\Example\CollectionFactory;

class Ajax extends \Magento\Framework\App\Action\Action
{

   public function __construct( Context  $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory  ) {

        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {  
        $result    = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) 
        {
			
		$catid = $this->getRequest()->getParam('catid');
			
		$categoryId = 5;
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$category = $_objectManager->create('Magento\Catalog\Model\Category')
		->load($categoryId);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create()
                        ->addAttributeToSelect('*')
						->addCategoryFilter($category)->load();
						//->addCategoriesFilter(5);
						
                            // 
				$collectionAd = array();			 
			 foreach ($collection as $product){
			 //echo 'Name  = '.$product->getId().' '.$product->getName().'<br>';
			   return $result->setData($product->getId());
		    }			

	      // return $result->setData($collectionAd);
        }
    }
}

