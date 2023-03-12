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

	 
		$sdf = explode(',',$catid);
		//return $result->setData($sdf[0]);
		$cou = count($sdf);
		$htm ='';
        for($c=0; $c < $cou; $c++){
		
                $categoryId = $sdf[$c];
				$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$category = $_objectManager->create('Magento\Catalog\Model\Category')
				->load($categoryId);

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
				$collection = $productCollection->create()
								->addAttributeToSelect('*')
								->addCategoryFilter($category)->load();
								
				$collectionAd = array();			 
					 foreach ($collection as $product){
					 //echo 'Name  = '.$product->getId().' '.$product->getName().'<br>';
					  // return $result->setData($product->getId());
					  
					   if($htm ==''){
						   $htm = '<option value="'.$product->getId().'">'.$product->getName().'</option>';
					   }else{
						   $htm = $htm.'<option value="'.$product->getId().'">'.$product->getName().'</option>';
					   }
					  
					}		
		
        //end for		
		}
		
		if($htm ==''){
			 $htm = '<option value="" disabled>no product found with selected category</option>';
		}

        $htmm = '<select id="item_product_ids" name="product_ids[]" size="10" class=" required-entry _required select multiselect admin__control-multiselect" data-ui-id="filescollection-items-edit-form-fieldset-element-select-product-ids" multiple="multiple">
        '.$htm.'</select>';	
 
        return $result->setData($htmm); 
		
	      // return $result->setData($collectionAd);
        }
    }
}

