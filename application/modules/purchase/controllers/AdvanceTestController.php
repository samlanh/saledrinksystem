<?php
class purchase_advancetestController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
	public function indexAction()
	{
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$vendor_sql = "SELECT p.order_id, p.order, p.date_order, p.status, v.v_name, p.all_total,p.paid,p.balance
						FROM tb_purchase_order AS p INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id ";
		if($this->getRequest()->isPost()){
				$post = $this->getRequest()->getPost();
				//echo $post["order"];
				if($post['order'] !=''){
						$vendor_sql .= " AND p.order LIKE '%".$post['order']."%'";
				}
				if($post['vendor_name'] !=''){
					$vendor_sql .= " AND v.vendor_id =".$post['vendor_name'];
								}
				if($post['phone'] !=''){
					$vendor_sql .= " AND v.phone LIKE '%".$post['phone']."%'";
				}
				if($post['status'] !=''){
					$vendor_sql .= " AND p.status =".$post['status'];
				}
				$start_date = $post['search_start_date'];
				$end_date = $post['search_end_date'];
				
				if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
					$vendor_sql .= " AND p.date_order BETWEEN '$start_date' AND '$end_date'";
				}
		}
		$vendor_sql.=" ORDER BY p.timestamp DESC";
		$rows=$db->getGlobalDb($vendor_sql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getStatusType($rows, BASE_URL, true);
		$columns=array("PURCHASE_ORDER_CAP","ORDER_DATE_CAP","STATUS_CAP", "VENDOR_NAME_CAP",
				 "TOTAL_CAP_DOLLAR","PAID_DOLLAR_CAP","BALANCE_CAP");
		$link=array(
				'module'=>'purchase','controller'=>'index','action'=>'detail-purchase-order',
		);
		$urlEdit = BASE_URL . "/purchase/index/update-purchase-order";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('order'=>$link),$urlEdit);
	}
	public function advanceTestAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		
		$r = new purchase_Model_DbTable_DbPurchaseVendor();
		$r->updatePurcaheToInProgress($id);
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
		}
		///link left not yet get from DbpurchaseOrder
		
		$session_vendor_info = new Zend_Session_Namespace('vendor_info');
		$rows= $session_vendor_info->vendorinfo;		
 //		print_r($rows);exit();
// 		$purchase = new purchase_Model_DbTable_DbPurchaseOrder();
// 		$rows = $purchase->purchaseInfo($id);
		$formStock = new Application_Form_purchase();
		$formpurchase_info = $formStock->productOrder($rows);
		Application_Model_Decorator::removeAllDecorator($formpurchase_info);// omit default zend html tag
		$this->view->form_purchase = $formpurchase_info;
		
		//get item of this order
// 		$orderModel = new purchase_Model_DbTable_DbPurchaseOrder();
// 		$orderDetail = $orderModel->getPurchaseID($id);
// 		$this->view->rowsOrder = $orderDetail;
		
		 $session_record_order = new Zend_Session_Namespace('record_order'); //create in update purchase order in page indexcontroller action update
		 $orderDetail=$session_record_order->orderDetail;
		 $this->view->rowsOrder = $orderDetail;
		 
		 if($rows['status']==4){
		 	Application_Form_FrmMessage::message("You Can't Access Advance! Order Is Payment Already");
		 	//$this->_redirect("/purchase/index/detail-purchase-order/id/".$id);
		 }
		
		 //for get item receive qty order
		$qty_receive = new  purchase_Model_DbTable_DbPurchaseAdvance();
		$row_receive = $qty_receive->getProductReceived($id);
	
		//print_r($row_receive);
		$this->view->rowsreceived = $row_receive;
		
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->itemsOption = $itemRows;
		
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->items = $itemRows;
	
		//get control
		$formControl = new Application_Form_FrmAction(null);
		$formViewControl = $formControl->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formViewControl);
		$this->view->control = $formViewControl;
			
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
	
		//for add vendor
		$formStockAdd = $formpopup->popupVendor(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form_vendor = $formStockAdd;
	
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
		
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->itemsOption = $itemRows;
	}
	//get select from purchase order and update status
	public function getPurchaeOrderAction(){
		$post=$this->getRequest()->getPost();
		
		if($post){
			$db= new Application_Model_DbTable_DbGlobal();
			$getorder = new purchase_Model_DbTable_DbPurchaseAdvance();
			$data_staust = array('status'=>3);
			$db->updateRecord($data_staust, $post['purchase_order'],"order_id", "tb_purchase_order");
			
			$rows = $getorder->getStatusOrder($post['purchase_order']);//for update status in purchase order history
			if($rows){
				foreach ($rows as $order){
					$db->updateRecord($data_staust, $order["history_id"],"history_id", "tb_order_history");//must update  status too
				}		
			}
			$check_purchase_tmp = new purchase_Model_DbTable_DbAdvance();
			$exist = $check_purchase_tmp->purchaseTMPExist($post);
			if(!$exist){
				$sql_insert="INSERT INTO tb_purchase_order_item_tmp
							SELECT id,order_id,pro_id, sum(qty_order) as qty_order
							FROM tb_purchase_order_item WHERE order_id = ".$post['purchase_order']." GROUP BY pro_id";
				$db->query($sql_insert);	//b4 for 

				//after insert get it againt for check condition
				$result=$getorder->getPurchaseOrderExist($post);
			}
			else{
				$result=$getorder->getPurchaseOrderExist($post);//can select already
			}
		}
		echo Zend_Json::encode($result);		
		exit();
		
	}
	//for get purchase receive order 
	public function addPurchaseReceiveAction(){
		$post = $this->getRequest()->getPost();
		$add_receive = new purchase_Model_DbTable_DbAdvance();
		$get_result = $add_receive ->addReveivPurchaseOrder($post);
		echo Zend_Json::encode($get_result);
		exit();
	}
	public function receivedOrderCompleteAction(){
		$data=$this->getRequest()->getPost();
		$db= new Application_Model_DbTable_DbGlobal();
		//if($post){
			$data_staust = array('status'=> 5 );
			$getorder = new purchase_Model_DbTable_DbPurchaseAdvance();
			$rows = $getorder->getStatusOrder($data['purchase_order']);//for update status in purchase order history
			if($rows){
				foreach ($rows as $order){
					$db->updateRecord($data_staust, $order["history_id"],"history_id", "tb_order_history");//must update  status too
				}
				//$result=$getorder->getPurchaseOrderExist($post);
			}
			//get fully received 
			$fully_receive = new purchase_Model_DbTable_DbAdvance();
			$get_result = $fully_receive ->receivedCompleted($data);
			$suc = array("test"=>1);
			echo Zend_Json::encode($suc);
			exit();
		//}
	}
	public function calculatePaymentAction(){
		$post = $this->getRequest()->getPost();
			$payment = new purchase_Model_DbTable_DbAdvance();
			$suc = $payment->calCulatePayment($post);
		echo Zend_Json::encode($suc);
		exit();
	}
	
	
}