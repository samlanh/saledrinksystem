<?php
class Sales_DeliveryController extends Zend_Controller_Action
{	
	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
   	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_DbDeliverys();
		$rows = $db->getAllSaleOrder($search);
		
		$this->view-> rs = $rows;
		
		$columns=array("BRANCH_NAME","Com.Name","CON_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		/*$link=array(
				'module'=>'sales','controller'=>'salesapprove','action'=>'add',
		);*/
		
			$link=array(
					'module'=>'sales','controller'=>'delivery','action'=>'add',
			);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
		
		/*if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_Dbinvoiceapprove();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","CUSTOMER_NAME","SALE_AGENT","SALE_NO", "ORDER_DATE","SALE_APP_DATE",
				"CURRNECY_TYPE","TOTAL","DISCOUNT","TOTAL_AMOUNT","APPROVED_STATUS","PENDING_STATUS","BY_USER");
		$link=array(
				'module'=>'sales','controller'=>'invoiceapprove','action'=>'add',
		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'customer_name'=>$link,'staff_name'=>$link,'sale_no'=>$link));
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);*/
		
	}
	function adddeliveryAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->addDelivery($data);
				if(!empty($data["saveprint"])){
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/deliverynote/id/".$returnid);
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/rpt-delivery");
				}else{
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/delivery");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("DELIVERY_FAIL", "/sales/delivery");
			}
		}
		
	}	
	function addAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/delivery/add");
    	}
    	$query = new Sales_Model_DbTable_Dbdeliverys();
    	$this->view->product =  $query->getProductSaleById($id);
		$row = $query->getProductSaleById($id);
    	if(empty($row)){
    		$this->_redirect("/sales/delivery/");
    	}else{
			$completed= $row[0]["is_completed"];
			if($completed==1){
				Application_Form_FrmMessage::Sucessfull("DELIVERY_HAS_COMPLETED", "/sales/delivery");
			}
		}
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionByIdIinvocie(4, null);
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->addDelivery($data);
				if(!empty($data["saveprint"])){
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/deliverynote/id/".$returnid);
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/rpt-delivery");
				}else{
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/delivery");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("DELIVERY_FAIL", "/sales/delivery");
			}
		}
	}

function cancelAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/sales/delivery/add");
    	}
    	$query = new Sales_Model_DbTable_Dbdeliverys();
    	$this->view->product =  $query->getProductSaleById($id);
		$row = $query->getProductSaleById($id);
    	/*if(empty($row)){
    		$this->_redirect("/sales/delivery/");
    	}else{
			$completed= $row[0]["is_completed"];
			if($completed==1){
				Application_Form_FrmMessage::Sucessfull("DELIVERY_HAS_COMPLETED", "/sales/delivery");
			}
		}*/
    	$db= new Application_Model_DbTable_DbGlobal();
    	$this->view->rscondition = $db->getTermConditionByIdIinvocie(4, null);
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			try {
				$dbq = new Sales_Model_DbTable_Dbdeliverys();				
				$returnid = $dbq->cancelDelivery($data);
				if(!empty($data["saveprint"])){
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/deliverynote/id/".$returnid);
					//Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/report/index/rpt-delivery");
				}else{
					Application_Form_FrmMessage::Sucessfull("DELIVERY_SUCCESS", "/sales/delivery");
				}
				
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				Application_Form_FrmMessage::Sucessfull("DELIVERY_FAIL", "/sales/delivery");
			}
		}
	}	
}