<?php
class Sales_ClearpointController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
// 			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
// 			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'text_search'=>'',
					'customer_id'=>0,
			);
		}
		$db = new Sales_Model_DbTable_DbClearPoint();
		$rows = $db->getAllClearPoint($search);
		$list = new Application_Form_Frmlist();
		$columns=array("CUSTOMER_NAME","DATE",
		"TOTAL_POINT","CLEAR_POINT","BALANCE_POINT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'clearpoint','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('cus_name'=>$link,'create_date'=>$link,'total_point'=>$link));
		
        $formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
		//$db = new Application_Model_DbTable_DbGlobal();
 	   // $db->getUpdatetermcustomer();
		
		//$db = new Sales_Model_DbTable_DbCustomer();
 	   // $db->updatecustomerId();
		
	}
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$db = new Sales_Model_DbTable_DbClearPoint();
				$db->addClearPointr($post);
				if(!empty($post['saveclose']))
				{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/sales/clearpoint/');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/sales/clearpoint/add');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		/////////////////for veiw form
		$formcustomer = new Sales_Form_FrmClearpoint(null);
		$formStockAdd = $formcustomer->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form = $formcustomer;
		
		$formcustomer = new Sales_Form_FrmPayment(null);
		$formStockAdd = $formcustomer->Payment(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form_payment = $formcustomer;
		
		///frm zone name
		$fm = new Sales_Form_FrmCustomerType();
		$frm = $fm->add();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->Form = $frm;
	
	}	
	public function editAction() {
		$id = $this->getRequest()->getParam('id');
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$db = new Sales_Model_DbTable_DbClearPoint();
				$db->updateClearPoint($post,$id);
				if(!empty($post['saveclose']))
				{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/sales/clearpoint/');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/sales/clearpoint/add');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///slect row and rows detail 
		$db = new Sales_Model_DbTable_DbClearPoint();
		$this->view->row=$db->getClearPointById($id);
		$this->view->row_detail=$db->getClearPointDetailByid($id);
		/////////////////for veiw form
		$formcustomer = new Sales_Form_FrmClearpoint(null);
		$formStockAdd = $formcustomer->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form = $formcustomer;
		
		$formcustomer = new Sales_Form_FrmPayment(null);
		$formStockAdd = $formcustomer->Payment(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form_payment = $formcustomer;
		
		///frm zone name
		$fm = new Sales_Form_FrmCustomerType();
		$frm = $fm->add();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->Form = $frm;
	} 
	
	function addNewZoneAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Sales_Model_DbTable_DbCustomer();
		$result = $get_code->addZone($post);
		echo Zend_Json::encode($result);
		exit();
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllPoint($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}