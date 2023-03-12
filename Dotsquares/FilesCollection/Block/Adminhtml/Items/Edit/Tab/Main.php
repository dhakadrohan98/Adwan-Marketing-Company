<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Dotsquares\FilesCollection\Block\Adminhtml\Items\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;



class Main extends Generic implements TabInterface
{

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Add File');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('File Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_dotsquares_filescollection_items');
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$systemStore = $objectManager->create('Magento\Store\Model\System\Store');
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create()
                        ->addAttributeToSelect('*')
                             ->load();
							 
				 $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
				$storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
				$currentStore = $storeManager->getStore();
				$mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				$fileaccessURL = $mediaUrl.'dotsquares'.$model->getFileName();
				$fileaccessURLDIV ='';
				
				if($model->getFileName()){
					
					$fileaccessURLDIV = '<div class="admin__field field fileaccess"><span><a href="'.$fileaccessURL.'"><span style="width:10px;height:10px;color:#000;text-indent:3001px;display:inline;padding:1px;"><strong>File Uploaded</strong></span></a></span><input type="hidden" name="checkedimagevalue" value="'.$model->getFileName().'" /></div><div class="admin__field field"> Please upload only pdf</div><script>function checkAddress(){ 
					  var checkboxck = document.getElementById("item_checkimage").value;
					  if(checkboxck == "" || checkboxck == 0){
						  document.getElementById("item_checkimage").checked = true;
						  document.getElementById("item_checkimage").value = 1;
					  }else{
					      document.getElementById("item_checkimage").checked = false;
						  document.getElementById("item_checkimage").value = 0;
					}	
					}</script>';
					
				}else{
					$fileaccessURLDIV = '<div class="admin__field field fileaccess"></div><div class="admin__field field"> Please upload only pdf</div>';
					//$fileaccessURLDIV = '<div class="admin__field field fileaccess"></div><div class="admin__field field"> Please upload only xlsx,pdf,doc,flv, avi, wmv, mov, wav, 3gp, mp4, jpg, jpeg, gif, png, bmp, txt, csv, docx, xls, ppt, pdf, mp3, zip</div>';
				}
				
           $ffgtyu = $model->getFileName();
		//print_r($model->getFileName());
		
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add File')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('File Name'), 'title' => __('File Name'), 'required' => true]
        );
		

		$fieldset->addField(
            'file_name',
            'file',
            ['name' => 'file_name', 'label' => __('Add File'), 'title' => __('Add File')]
        )->setAfterElementHtml($fileaccessURLDIV);
		
		 if ($model->getId()) {
		
		/*  $fieldset->addField(
            'checkimage',
            'checkbox',
            ['name' => 'checkimage', 'label' => __('Delete File'), 'title' => __('Delete File') ],
			'onchange'=>'checkAddress()'
        ); */
		
		$fieldset->addField('checkimage', 'checkbox', array(
          'label'     => 'Delete File',
          'values'    => '',
          'name'      => 'checkimage',
		  'onchange' => 'checkAddress()',
      ));
		
		
		 }


		 $fieldset->addField(
	       'store_ids',
	       'multiselect',
	       [
	         'name'     => 'store_ids[]',
	         'label'    => __('Store Views'),
	         'title'    => __('Store Views'),
	         'required' => true,
	         'values'   => $systemStore->getStoreValuesForForm(false, true),
	       ]
	    );
		
       /* category list*/

	   $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
$categories = $categoryFactory->create()                              
    ->addAttributeToSelect('*')
    ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched


	$collectionCat = array();
	
foreach ($categories as $category){
    if($category->getIsActive()){
		
		 $collectionCat[] = array(
                'value' => $category->getId(),
                'label' => $category->getName()
            );
		
	}
}

//print_r($collectionCat);

	/* 	 $fieldset->addField(
            'categories',
            'multiselect',
            [
               
                'required' => true,
                'name' => 'categories',
                
                'value' => '',
				
            ]
			
			
        ) */
		
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $URLBASE =  $storeManager->getStore()->getBaseUrl();
        $adminUrl = $objectManager->get('Magento\Backend\Helper\Data')->getAreaFrontName();
		$URLBASEURLajax = $URLBASE.$adminUrl.'/dotsquares_filescollection/items/ajax';
		
		
		$fieldset->addField('categories', 'multiselect', array(
           'label' => __('Category List'),
          'class'     => 'required-entry addproductlistalso',
         'values' => $collectionCat,
          'name'      => 'categories',
		  'onclick' => 'checkSelectedItem()'
        ))->setAfterElementHtml("
                         <script type=\"text/javascript\">
						 
						 require(['jquery', 'jquery/ui'], function($){ 
								  $(document).ready(function(){
														 //alert('functicon');
														 
													/* 	 setTimeout(
																  function() 
																  {
																	  checkSelectedItem();
																  }, 5000); */
														 
														
														 
													 });  
							 });
						 
						
						 
                            function checkSelectedItem(){ 
							
							 var select1 = document.getElementById('item_categories');
                var selected1 = [];
					for (var i = 0; i < select1.length; i++) {
						if (select1.options[i].selected) selected1.push(select1.options[i].value);
					}
					
                               
									   jQuery.ajax( {
													url: '".$URLBASEURLajax."/catid/' + selected1,
													data: {form_key: window.FORM_KEY},
													type: 'POST'
												}).done(function(a) { 
													//console.log(a); 
													//jsonObject = a.responseText.evalJSON();
													//alert(a);
													document.getElementById('item_product_ids').innerHTML= a;
												});			   
							   
							   
                            }
                         </script>");
	   
       /* category list end*/	   
		
		 
		$optionArray = '';
		
		$collectionAd = array();
		
		
		
		 foreach ($collection as $product){
			 //echo 'Name  = '.$product->getId().' '.$product->getName().'<br>';
			 $collectionAd[] = array(
                'value' => $product->getId(),
                'label' => $product->getName()
            );
		}  
		
		//print_r($collectionAd);
		
		 $fieldset->addField(
            'product_ids',
            'multiselect',
            [
                'label' => __('Product List'),
                'required' => true,
                'name' => 'product_ids',
                'values' => $collectionAd,
                'value' => ''
            ]
        );
		

   
 $fieldset->addField('status', 'select', array(
          'label'     => 'Status',
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => 'Enabled',
              ),

              array(
                  'value'     => 2,
                  'label'     => 'Disabled',
              ),
          ),
      ));
	
	
	/*   $this->setChild('form_after',
    $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
        ->addFieldMap('answers', 'answer')
        ->addFieldMap('is_status', 'status')
        ->addFieldDependence('status', 'answer', 'Customer')

);
     $form->setValues($model->getData());

    $this->setForm($form); */
		
		
		
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
