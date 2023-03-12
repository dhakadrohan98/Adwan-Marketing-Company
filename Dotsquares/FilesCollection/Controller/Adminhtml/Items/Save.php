<?php
/**
 * Copyright © 2015 Dotsquares. All rights reserved.
 */

namespace Dotsquares\FilesCollection\Controller\Adminhtml\Items;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
//class Save extends \Dotsquares\FilesCollection\Controller\Adminhtml\Items
{
	
  protected $_jsHelper;
	
	protected $_contactCollectionFactory;
	
	public function __construct(
        Context $context,
        \Magento\Backend\Helper\Js $jsHelper
    ) {
        $this->_jsHelper = $jsHelper;
        //$this->_contactCollectionFactory = $contactCollectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
	
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Dotsquares\FilesCollection\Model\Items');
				
                $data = $this->getRequest()->getPostValue();
				
			
				
					foreach ($data as $key => $value)
						{
							if (is_array($value))
							{
								$data[$key] = implode(',',$this->getRequest()->getParam($key)); 
							}
						}
				
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
				
			$data = $inputFilter->getUnescaped();
			
            try{  
			/*media*/
			$uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader',['fileId' => 'file_name']);
			$fInam = $uploader->validateFile();
			$ffgko = $fInam['name'];
		  
		    $ext = substr($ffgko, strrpos($ffgko, '.') + 1);
			$filearr =  array("pdf");
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection('core_write');
			$table =  $connection->getTableName('dotsquares_filescollection_items');
			$QURY1 = 'SELECT * FROM ' . $table .' WHERE name="'.$data['name'].'"';
			$productfiles = $connection->fetchAll($QURY1);
			$namecount = count($productfiles);
			$ext = substr($ffgko, strrpos($ffgko, '.') + 1);
			if($ext == 'docx'){
				$ext = 'doc';
			} 
			$QURY2 = 'SELECT * FROM ' . $table .' WHERE file_name LIKE "%'.$ext.'%"';
			$productfiles2 = $connection->fetchAll($QURY2); 
			$countpr2 = count($productfiles2);
            /*check already uploaded files count end*/
			$makefalse = 0;
			$idS = $this->getRequest()->getParam('id');
			
			
					if (in_array($ext, $filearr) || $idS !='')
					  {
						 /*ext match*/
						 if (isset($ffgko) && isset($ffgko) && strlen($ffgko))
							{
								#echo '<pre>';print_r($_FILES);die("test");
								try
								{
									$uploader = $this->_objectManager->create(
										'Magento\MediaStorage\Model\File\Uploader',
										['fileId' => 'file_name']
									);
									//$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
									
									$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
									$uploader->setAllowRenameFiles(true);
									$uploader->setFilesDispersion(true);
									$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
										->getDirectoryRead(DirectoryList::MEDIA);
									$result = $uploader->save(
										$mediaDirectory->getAbsolutePath('dotsquares')
									);
									$data['file_name'] = $result['file'];
								} catch (\Exception $e) {
									if ($e->getCode() == 0) {
										$this->messageManager->addError($e->getMessage());
									}
								}
							}
			
						/*media*/
							
							
							$id = $this->getRequest()->getParam('id');
							if ($id) {
								$model->load($id);
								if ($id != $model->getId()) {
									throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
								}
							}
							
							if ($id) {
								echo "test";
								$checkimage = $this->getRequest()->getParam('checkimage');
								if($ext == "pdf"){
								if($checkimage ==1){
									$oldfilepath = $this->getRequest()->getParam('checkedimagevalue');
									
									$mediaDirectoryrd = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
									$mediaRootDir = $mediaDirectoryrd->getAbsolutePath();
									$actualfile = $mediaRootDir .'dotsquares'. $oldfilepath;
									//print_r($actualfile);
										unlink($actualfile);
									/*   if ($this->_file->isExists($actualfile))  {

										$this->_file->deleteFile($actualfile);
									}  */ 
									
									
								}
							}else{
								echo "else"; 
								$this->messageManager->addError(
								__('Something went wrong while saving the item data. Please check your uploaded file formate, only pdf file accept')
							);	
								$this->_redirect('dotsquares_filescollection/*/edit', ['id' => $model->getId()]);
								return false;
							}
								
							}
							
					        $model->setData($data);
							$session = $this->_objectManager->get('Magento\Backend\Model\Session');
							$session->setPageData($model->getData());
							$model->save();
							$this->messageManager->addSuccess(__('You saved the item.'));
							$session->setPageData(false);
							if ($this->getRequest()->getParam('back')) {
								$this->_redirect('dotsquares_filescollection/*/edit', ['id' => $model->getId()]);
								return;
							}
						 /*ext match end*/
					  }else{
						  $this->messageManager->addError(
								__('Something went wrong while saving the item data. Please check your uploaded file formate, only pdf file accept')
							);
					  }
			
			
			
			}catch (\Exception $e) {
				//if no file have
				
				  	    $id = $this->getRequest()->getParam('id');
							if ($id) {
								$model->load($id);
								if ($id != $model->getId()) {
									throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
								}
							}
							
							if ($id) {
								$checkimage = $this->getRequest()->getParam('checkimage');
								
								if($checkimage ==1){
									$oldfilepath = $this->getRequest()->getParam('checkedimagevalue');
									
									$mediaDirectoryrd = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
									$mediaRootDir = $mediaDirectoryrd->getAbsolutePath();
									$actualfile = $mediaRootDir .'dotsquares'. $oldfilepath;
									$data['file_name'] = '';
										unlink($actualfile);
								}
							}
							
					        $model->setData($data);
							$session = $this->_objectManager->get('Magento\Backend\Model\Session');
							$session->setPageData($model->getData());
							$model->save();
							$this->messageManager->addSuccess(__('You saved the item.'));
							$session->setPageData(false);
							if ($this->getRequest()->getParam('back')) {
								$this->_redirect('dotsquares_filescollection/*/edit', ['id' => $model->getId()]);
								return;
							}
				
				
               //if no file have end  
            }
			
			
			
			
                $this->_redirect('dotsquares_filescollection/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('dotsquares_filescollection/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('dotsquares_filescollection/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('dotsquares_filescollection/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('dotsquares_filescollection/*/');
    }
}
