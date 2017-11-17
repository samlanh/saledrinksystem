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
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'text_search'=>'',
					'customer_id'=>0,
					'customer_type'=>0,
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
			);
		}
		$db = new Sales_Model_DbTable_DbClearPoint();
		$rows = $db->getAllClearPoint($search);
		$list = new Application_Form_Frmlist();
		$columns=array("CUSTOMER_NAME","DATE",
		"TOTAL_POINT","CLEAR_POINT","BALANCE_POINT","BY_USER","STATUS");
		$link=array(
				'module'=>'sales','controller'=>'clearpoint','action'=>'index',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'cust_name'=>$link,'contact_name'=>$link,'level'=>$link));
		
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
			try{
				$post = $this->getRequest()->getPost();
				//$post["id"]=$id;
				$customer= new Sales_Model_DbTable_DbCustomer();
				$customer->updateCustomer($post);
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/sales/customer");
				//$this->_redirect('/sales/customer/index');
			}catch (Exception $e){
				
				Application_Form_FrmMessage::message("Update customer failed !");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
			$sql = "SELECT c.* FROM `tb_customer`AS c WHERE c.id=".$id." LIMIT 1";
		$db = new Application_Model_DbTable_DbGlobal();
		$row = $db->getGlobalDbRow($sql);
		// lost item info
		$formStock=new Sales_Form_FrmCustomer($row);
		$formStockEdit = $formStock->Formcustomer($row);
		Application_Model_Decorator::removeAllDecorator($formStockEdit);// omit default zend html tag
		$this->view->form = $formStockEdit;
	
		//control action
		$formControl = new Application_Form_FrmAction(null);
		$formViewControl = $formControl->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formViewControl);
		$this->view->control = $formViewControl;
		
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