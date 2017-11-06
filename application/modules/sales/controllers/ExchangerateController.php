<?php
class Sales_ExchangerateController extends Zend_Controller_Action
{
public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
		$db = new Sales_Model_DbTable_Dbexchangerate();
		$rows = $db->getAllExchange();
		
		$glClass = new Application_Model_GlobalClass();
		$list = new Application_Form_Frmlist();
		$columns=array("NAME_KH","តម្លៃ");
		$link=array('module'=>'sales','controller'=>'exchangerate','action'=>'edit',);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('code'=>$link,'key_name'=>$link));		
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbTermCondiction();
			$db->add($data);
			if($data['save_close']){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/sales/exchangerate/index');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}
		}
		$formFilter = new Sales_Form_FrmTermCondiction();
		$this->view->frmAdd = $formFilter->Formterm();
		Application_Model_Decorator::removeAllDecorator($formFilter->Formterm());
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbTermCondiction();
		if($id==0){
			$this->_redirect('/sales/exchangerate/index');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Sales_Model_DbTable_Dbexchangerate();
			$db->edit($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/sales/exchangerate/index');
			}
		}
		$rs = $db->getTermById($id);
		$formFilter = new Sales_Form_FrmTermCondiction();
		$form = $formFilter->Formterm();
		$this->view->frmAdd = $formFilter->Formterm($rs);
		Application_Model_Decorator::removeAllDecorator($form);
	}
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

