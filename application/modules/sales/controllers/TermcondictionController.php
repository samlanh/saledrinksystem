<?php
class Sales_TermcondictionController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_DbTermCondiction();
		$rows = $db->getAllTerm();
// 		$list = new Application_Form_Frmlist();
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getImgStatus($rows, BASE_URL, true);
		$list = new Application_Form_Frmlist();
		$columns=array("NAME_KH","NAME_EN","TYPE","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'termcondiction','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('con_khmer'=>$link,'con_english'=>$link));
		
		
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
				Application_Form_FrmMessage::redirectUrl('/sales/termcondiction/index');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				//Application_Form_FrmMessage::redirectUrl('/sales/termcondiction/index/add');
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
			$this->_redirect('/sales/termcondiction/index');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Sales_Model_DbTable_DbTermCondiction();
			$db->edit($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/sales/termcondiction/index');
			}
		}
		$rs = $db->getTermById($id);
		//print_r($rs);
		$formFilter = new Sales_Form_FrmTermCondiction();
		$form = $formFilter->Formterm();
		$this->view->frmAdd = $formFilter->Formterm($rs);
		Application_Model_Decorator::removeAllDecorator($form);
	}
	//view category 27-8-2013
	
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

