<?php
class Sales_CustomerController extends Zend_Controller_Action
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
					'branch_id'=>0,
					'customer_id'=>0,
					'customer_type'=>0,
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
			);
		}
		$db = new Sales_Model_DbTable_DbCustomer();
		$rows = $db->getAllCustomer($search);
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","CUSTOMER_TYPE",
		"CONTACT_NAME","CONTACT_NUMBER","ADDRESS","CREDIT_TERM","CREDIT_LIMIT","STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'customer','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'cust_name'=>$link,'customer_type'=>$link,'level'=>$link));
		
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
				$db = new Sales_Model_DbTable_DbCustomer();
				$db->addCustomer($post);
				if(!empty($post['saveclose']))
				{
// 					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', sel . '/customer/index');
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		/////////////////for veiw form
		$formcustomer = new Sales_Form_FrmCustomer(null);
		$formStockAdd = $formcustomer->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form = $formcustomer;
	
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
	}


	public function customertypelistAction()
    {
    	try{
    		$db = new Sales_Model_DbTable_DbCustomer();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => '',
    					'type' => ''
    			);
    		}
    		$rs_rows= $db->getCustomerType($search);//call frome model
    		$this->view->rs = $rs_rows;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$fm = new Product_Form_FrmOther();
    	$frm = $fm->search();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
    public function addcustomertypeAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		
    		$db = new Sales_Model_DbTable_DbCustomer();
    		try {
    			$db->addCustomerType($data);
    			if(isset($data['save_new'])){
    
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}
    			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/sales/customer/customertypelist");
    				//Application_Form_FrmMessage::redirectUrl('/other/loantype');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$fm = new Sales_Form_FrmCustomerType();
    	$frm = $fm->add();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }

	public function editcustomertypeAction()
    {
    	$id = $this->getRequest()->getParam("id");
    	$db = new Sales_Model_DbTable_DbCustomer();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$data["id"] = $id;
    		try {
    			$db->editCustomerType($data);
    			if(isset($data['save_new'])){
    
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}
    			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ',"/sales/customer/customertypelist");
    				//Application_Form_FrmMessage::redirectUrl('/other/loantype');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$rs = $db->getCustomerTypeId($id);
    	$fm = new Sales_Form_FrmCustomerType();
    	$frm = $fm->add($rs);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form = $frm;
    }
	
	public function addCustomerAction(){
		if($this->getRequest()->isPost()){
			try {
			$post=$this->getRequest()->getPost();
			$add_customer = new Sales_Model_DbTable_DbCustomer();
			$customer_id = $add_customer->addNewCustomer($post);
			$result = array('cus_id'=>$customer_id);
			echo Zend_Json::encode($result);
			exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
	public function deleteCustomerAction() {
		$id = ($this->getRequest()->getParam('id'));
		$sql = "DELETE FROM tb_customer WHERE customer_id IN ($id)";
		$deleteObj = new Application_Model_DbTable_DbGlobal();
		$deleteObj->deleteRecords($sql);
		$this->_redirect('/sales/customer/index');
	}
	
	public function getCuCodeAction(){//dynamic by customer
	
		$post=$this->getRequest()->getPost();
		$get_code = new Sales_Model_DbTable_DbCustomer();
		$result = $get_code->getCustomerCode($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	
	function getcustomerlimitAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Sales_Model_DbTable_DbCustomer();
		$result = $get_code->getCustomerLimit($post["id"]);
		echo Zend_Json::encode($result);
		exit();
	}
	function getCustomerinfoAction(){
		$post=$this->getRequest()->getPost();
		$get_code = new Sales_Model_DbTable_DbCustomer();
		$result = $get_code->getCustomerinfo($post["customer_id"]);
		echo Zend_Json::encode($result);
		exit();
	}
}