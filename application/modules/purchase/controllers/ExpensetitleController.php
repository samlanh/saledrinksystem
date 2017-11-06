<?php
class Purchase_ExpensetitleController extends Zend_Controller_Action
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
		$db = new Purchase_Model_DbTable_Dbexpensetitle();
		$rows = $db->getAllTerm();
// 		$list = new Application_Form_Frmlist();
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getImgStatus($rows, BASE_URL, true);
		$list = new Application_Form_Frmlist();
		$columns=array("TITLE","NAME_ENTITLE","STATUS");
		$link=array(
				'module'=>'purchase','controller'=>'expensetitle','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('title'=>$link));
		
		
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Purchase_Model_DbTable_Dbexpensetitle();
			$db->add($data);
			if($data['save_close']){
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::redirectUrl('/purchase/expensetitle/index');
			}
			else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}
		}
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Purchase_Model_DbTable_Dbexpensetitle();
		
		if($id==0){
			$this->_redirect('/purchase/expensetitle/index');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Purchase_Model_DbTable_Dbexpensetitle();
			$db->edit($data);
			if($data['save_close']){
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/purchase/expensetitle/index');
			}
		}
		$this->view->rs =  $db->getTermById($id);
	}
	function addexpensetitleAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_Dbexpensetitle();
		$pid = $db->addajaxtitle($post);
		$result = array("id"=>$pid);
		echo Zend_Json::encode($result);
		exit();
	}

	
}

