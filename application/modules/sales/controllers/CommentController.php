<?php
class Sales_CommentController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    
    public function indexAction()
    {
    	$db =new Sales_Model_DbTable_Dbcustomercomment();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();	
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    	}else{
    		$search =array(
    				'adv_search' => '',
					'customer_id' =>'',
    				'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d')
    				);
    	}
    	$this->view->row=$search;
    	$rows = $db->getCustomerComment($search);
    	$this->view->row_rs = $rows;
    	$list = new Application_Form_Frmlist();
    	$columns=array("CUSTOMER_NAME","COMMENT","DATE","BY_USER","STATUS");
    	$link=array(
    			'module'=>'sales','controller'=>'comment','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('projectname'=>$link,'customer_name'=>$link,'comment'=>$link));
 		$this->view->rst=$rows;
 		
 		$db = new Sales_Form_Frmcustomercomment();
 		$this->view->frm_search = $db->add();
	}
	public function addAction()
	{
		if ($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$dailywork = new Sales_Model_DbTable_Dbcustomercomment();
				$dailywork->addcomment($data);
				if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/sales/comment/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/sales/comment/index/add");
				}
			}
			catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm=new Sales_Form_Frmcustomercomment();
		$frm_dailywork=$fm->add();
		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
		$this->view->frm_comment= $frm_dailywork;
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
	}// Add Product 
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
    	$db = new Sales_Model_DbTable_Dbcustomercomment();
    	if ($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	 	$db->updatedailyworkById($data);
    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/sales/comment");
    	}
    	if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_EDIT", "/sales/comment");
    	}
    	$row= $db->getdailyById($id);
    	$this->view->row_rs=$row;
    	$fm=new Sales_Form_Frmcustomercomment();
		$frm_dailywork=$fm->add($row);
		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
		$this->view->frm_comment= $frm_dailywork;
    	
    	$formpopup = new Sales_Form_FrmCustomer(null);
    	$formpopup = $formpopup->Formcustomer(null);
    	Application_Model_Decorator::removeAllDecorator($formpopup);
    	$this->view->form_customer = $formpopup;
   }
}

