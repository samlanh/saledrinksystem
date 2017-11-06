<?php
class Product_brandController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
		$db = new Product_Model_DbTable_DbBrand();
		$formFilter = new Product_Form_FrmBrand();
		$frmsearch = $formFilter->BrandFilter();
		$this->view->formFilter = $frmsearch;
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'name'	=>	'',
					'brand'		=>	'',
					'status'	=>	1
			);
		}
		$result = $db->getAllBrands($data);
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbBrand();
			$db->add($data);
			if($data['saveclose']){
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/brand/index');
			}
		}
		$formFilter = new Product_Form_FrmBrand();
		$formAdd = $formFilter->Brand();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Product_Model_DbTable_DbBrand();
		
		if($id==0){
			$this->_redirect('product/brand/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Product_Model_DbTable_DbBrand();
			$db->edit($data);
			if($data['saveclose']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/brand/index');
			}
		}
		$rs = $db->getBrand($id);
		$formFilter = new Product_Form_FrmBrand();
		$formAdd = $formFilter->Brand($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	//view Brand 27-8-2013
	
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
}

