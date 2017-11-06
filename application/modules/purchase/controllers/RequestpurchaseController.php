<?php
class Purchase_indexController extends Zend_Controller_Action
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
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					'purchase_status'=>0,
					);
		}
		$db = new Purchase_Model_DbTable_DbPurchaseOrder();
		$rows = $db->getAllPurchaseOrder($search);
		$list = new Application_Form_Frmlist();
		$columns=array("BRANCH_NAME","VENDOR_NAME","PURCHASE_ORDER","ORDER_DATE","DATE_IN",
				 "CURRNECY_TYPE","TOTAL_AMOUNT","PAID","BALANCE","ORDER_STATUS","STATUS","BY_USER");
		$link=array(
				'module'=>'purchase','controller'=>'index','action'=>'edit',
		);
		
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'vendor_name'=>$link,'order_number'=>$link,'date_order'=>$link));
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
			$payment_purchase_order = new Purchase_Model_DbTable_DbPurchaseVendor();
			 if(!empty($data['identity'])){
				$payment_purchase_order->addPurchaseOrder($data);
			 }
			Application_Form_FrmMessage::message("Purchase has been Saved!");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		///link left not yet get from DbpurchaseOrder 	
		$frm_purchase = new Application_Form_purchase(null);
		$form_add_purchase = $frm_purchase->productOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
		
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();;
		
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
		$this->view->form_branch = $formAdd;	
	}
	public function addoldAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$payment_purchase_order = new Purchase_Model_DbTable_DbPurchaseVendor();
				if(!empty($data['identity'])){
					$payment_purchase_order->addPurchaseOrder($data);
				}
				Application_Form_FrmMessage::message("Purchase has been Saved!");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Application_Form_purchase(null);
		$form_add_purchase = $frm_purchase->productOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();;
	
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
		$this->view->form_branch = $formAdd;
	}
	public function updatePurchaseOrderAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db_global = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT * FROM tb_purchase_order WHERE order_id =".$id;
		$rs = $db_global->getGlobalDbRow($sql);
		//print_r($rs);exit();
		if($rs["status"]==4 OR $rs["status"]==5){
			$db = new Application_Model_DbTable_DbGlobal();
			$row=$db->getSettingById(15);
			if($row['key_value']==0){
				Application_Form_FrmMessage::message("You don't have permission to update purchase that recieved already");
				Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
			}else
	
				$this->ActionPurchaseAction();
				
		}else
			$this->ActionPurchaseAction();
	}
	public function addNewproudctAction(){
		$post=$this->getRequest()->getPost();
		$add_new_product = new Product_Model_DbTable_DbAddProduct();
		$pid = $add_new_product->addNewItem($post);
		$result = array("pid"=>$pid);
		echo Zend_Json::encode($result);
		exit();
	}
	protected function ActionPurchaseAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
	
			//    		if($data["status"]!=="Paid"){
			//    			if(@$data['payment']=='payment'){
			//     				$update_payment_order = new purchase_Model_DbTable_DbPurchaseVendor();
			//     				$update_payment_order-> updateVendorOrderPayment($data);
	
			//    			}
			//    			elseif(@$data['Update']=='Update'){
			//    				$update_order = new purchase_Model_DbTable_DbPurchaseVendor();
			//    				$update_order->updateVendorOrder($data);
			//    			}
			//    			$this->_redirect("purchase/index/index");
			//    		}
			//    		else{
			//    			Application_Form_FrmMessage::message("Cann't Edit!Purchase Order Has Been Payment Already");
			//    		    Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
			//    		}
			$update_order = new purchase_Model_DbTable_DbPurchaseVendor();
			if(isset($data["cancel_order"])){
				if($data["oldStatus"]!=6){
					$update_order->cancelPurchaseOrder($data);
					Application_Form_FrmMessage::message("You have been cancel purchase order success! ");
					Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
				}
				else{
					Application_Form_FrmMessage::message("Cannot cancel again! Becuase cancel order has been cancel already! ");
					Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
				}
			}
			else{
				if(isset($data["Update"]) or $data["update"]){
					if($data["oldStatus"]==6){
						if($data["status"]==6){
							Application_Form_FrmMessage::message("Cannot cancel again! Becuase cancel order has been cancel already! ");
						}else{
							$update_order->updateVendorCancellOrder($data);
							Application_Form_FrmMessage::message("You has been re-order success!");
							Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
						}
					}else{
						$update_order->updateVendorStock($data);
						Application_Form_FrmMessage::message("You have been Update order success! ");
						//print_r($data);exit();
						//Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
					}
	
				}
			}
	
			// 		if($data["Update"]=="Update"){
			// 			Application_Form_FrmMessage::message("Cann't Edit!Purchase Order Has Been Payment Already");
			// 			Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
	
			// 		}
		}
		$user = $this->GetuserInfoAction();
		if($user["level"]!=1 AND $user["level"]!=2){
			if($user["level"]==4){
				$this->_redirect("purchase/index/index");
			}
			$gb = 	new Application_Model_DbTable_DbGlobal();
			$exist = $gb->userSaleOrderExist($id, $user["location_id"]);
			if($exist==""){
				$this->_redirect("purchase/index/index");
			}
		}
		$purchase = new purchase_Model_DbTable_DbPurchaseOrder();
		$rows = $purchase->purchaseInfo($id);
		$db = new Application_Model_DbTable_DbGlobal();
		$formStock = new Application_Form_purchase();
		$formpurchase_info = $formStock->productOrder($rows);
		Application_Model_Decorator::removeAllDecorator($formpurchase_info);// omit default zend html tag
		$this->view->form = $formpurchase_info;
		$this->view->status = $rows["status"];
	
		//veiw sales order left 23/8/13
		$row_purchase = $purchase->showPurchaseOrder();
		$this->view->product=$row_purchase;
	
		//get item of this lost
		$orderModel = new purchase_Model_DbTable_DbPurchaseOrder();
		$orderDetail = $orderModel->getPurchaseID($id);
		$this->view->rowsOrder = $orderDetail;
	
		$session_record_order = new Zend_Session_Namespace('record_order');
		$session_record_order->orderDetail =$orderDetail;
	
		$session_vendor_info = new Zend_Session_Namespace('vendor_info');
		$session_vendor_info->vendorinfo=$rows;
	
		//for check if status update
					if($rows["status"]!=0){
						$this->_redirect("purchase/advance/advance/id/".$id);
					}
	
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->itemsOption = $itemRows;
	
		$formControl = new Application_Form_FrmAction(null);
		$formViewControl = $formControl->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formViewControl);
		$this->view->control = $formViewControl;
	
		//for search left
		$search = new purchase_Form_FrmSearch();
		$frmsearch= $search->formSearch();
		Application_Model_Decorator::removeAllDecorator($frmsearch);
		$this->view->get_frmsearch= $frmsearch;
	
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form_add_product = $formproduct;
	
		//for add vendor
		$formvendor= $formpopup->popupVendor(null);
		Application_Model_Decorator::removeAllDecorator($formvendor);
		$this->view->form_vendor = $formvendor;
	
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	
		//for link advane
		$this->view->getorder_id = $id;
	
	}
 
	
	public function getTaxAction(){//dynamic by customer
	
		$post=$this->getRequest()->getPost();
		$get_tax = new purchase_Model_DbTable_DbPurchaseVendor();
		$result = $get_tax->getTax($post["item_id"]);
		if(!$result){
			$result = array('tax'=>'0');
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
//    public function updatePurchaseOrderAction(){
   	
//    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
//    	if($this->getRequest()->isPost()){
//    		$data = $this->getRequest()->getPost();
// //    		if($data["status"]!=="Paid"){
// //    			if(@$data['payment']=='payment'){
// //     				$update_payment_order = new purchase_Model_DbTable_DbPurchaseVendor();
// //     				$update_payment_order-> updateVendorOrderPayment($data);
   				
// //    			}
// //    			elseif(@$data['Update']=='Update'){
// //    				$update_order = new purchase_Model_DbTable_DbPurchaseVendor();
// //    				$update_order->updateVendorOrder($data);
// //    			}
// //    			$this->_redirect("purchase/index/index");
// //    		}
// //    		else{
// //    			Application_Form_FrmMessage::message("Cann't Edit!Purchase Order Has Been Payment Already");
// //    		    Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
// //    		}
//    			$update_order = new purchase_Model_DbTable_DbPurchaseVendor();
//    			try {
// 		if(isset($data["cancel_order"])){
// 			if($data["oldStatus"]!=6){
// 					$update_order->cancelPurchaseOrder($data);
// 					Application_Form_FrmMessage::message("You have been cancel purchase order success! ");
// 					Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
// 			}
// 			else{
// 				Application_Form_FrmMessage::message("Cannot cancel again! Becuase cancel order has been cancel already! ");
// 				Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
// 			}
// 		}
//    		else{
//    			if(isset($data["Update"]) or $data["update"]){
//    				if($data["oldStatus"]==6){
//    					$update_order->vendorPurchaseOrderPayment($data);
//    					Application_Form_FrmMessage::message("You has been re-order success!");
//    					Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
//    				}else{
//    					$update_order->updateVendorStock($data);
//    					Application_Form_FrmMessage::message("You have been Update order success! ");
//    					//print_r($data);exit();
//    					Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
//    				}
//    			}
//    		}
//    			}catch (Exception $e){
//    				Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
//    			}
   		
// // 		if($data["Update"]=="Update"){
// // 			Application_Form_FrmMessage::message("Cann't Edit!Purchase Order Has Been Payment Already");
// // 			Application_Form_FrmMessage::redirectUrl("/purchase/index/index");
			
// // 		}
//    	}
//    	$user = $this->GetuserInfoAction();
//    	if($user["level"]!=1 AND $user["level"]!=2){
//    		if($user["level"]==4){
//    			$this->_redirect("purchase/index/index");
//    		}
//    		$gb = 	new Application_Model_DbTable_DbGlobal();
//    		$exist = $gb->userSaleOrderExist($id, $user["location_id"]);
//    		if($exist==""){
//    			$this->_redirect("purchase/index/index");
//    		}
//    	}
//    	$purchase = new purchase_Model_DbTable_DbPurchaseOrder();
//    	$rows = $purchase->purchaseInfo($id);
//     $db = new Application_Model_DbTable_DbGlobal();
//    	$formStock = new Application_Form_purchase();
//    	$formpurchase_info = $formStock->productOrder($rows);
//    	Application_Model_Decorator::removeAllDecorator($formpurchase_info);// omit default zend html tag
//    	$this->view->form = $formpurchase_info;
//    	$this->view->status = $rows["status"];
   	
//    	//veiw sales order left 23/8/13
//    	$row_purchase = $purchase->showPurchaseOrder();
//    	$this->view->product=$row_purchase;
   	
//    	//get item of this lost
//     $orderModel = new purchase_Model_DbTable_DbPurchaseOrder();
//    	$orderDetail = $orderModel->getPurchaseID($id);
//    	$this->view->rowsOrder = $orderDetail;
   	
//    	$session_record_order = new Zend_Session_Namespace('record_order');
//    	$session_record_order->orderDetail =$orderDetail;
   	
//    	$session_vendor_info = new Zend_Session_Namespace('vendor_info');
//    	$session_vendor_info->vendorinfo=$rows;
   	
//    	//for check if status update
//    	if($rows["status"]==3){
//    		$this->_redirect("purchase/advance/advance/id/".$id); 
//    	}
  	   	
//    	// item option in select
//    	$items = new Application_Model_GlobalClass();
//    	$itemRows = $items->getProductOption();
//    	$this->view->itemsOption = $itemRows;
   	
//    	$formControl = new Application_Form_FrmAction(null);
//    	$formViewControl = $formControl->AllAction(null);
//    	Application_Model_Decorator::removeAllDecorator($formViewControl);
//    	$this->view->control = $formViewControl;
   	
//    	//for search left
//    	$search = new purchase_Form_FrmSearch();
//    	$frmsearch= $search->formSearch();
//    	Application_Model_Decorator::removeAllDecorator($frmsearch);
//    	$this->view->get_frmsearch= $frmsearch;
   	
//    	//for add product;
//    	$formpopup = new Application_Form_FrmPopup(null);
//    	$formproduct = $formpopup->popuProduct(null);
//    	Application_Model_Decorator::removeAllDecorator($formproduct);
//    	$this->view->form_add_product = $formproduct;
   	
//    	//for add vendor
//    	$formvendor= $formpopup->popupVendor(null);
//    	Application_Model_Decorator::removeAllDecorator($formvendor);
//    	$this->view->form_vendor = $formvendor;
   	
//    	//for add location
//    	$formAdd = $formpopup->popuLocation(null);
//    	Application_Model_Decorator::removeAllDecorator($formAdd);
//    	$this->view->form_addstock = $formAdd;
   	
//    	//for link advane
//    	$this->view->getorder_id = $id;   	
   	
//    }
   
   public function detailPurchaseOrderAction() {
   	if($this->getRequest()->getParam('id')) {
   		    $id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';   
   		    $user = $this->GetuserInfoAction();
   		    if($user["level"]!=1 AND $user["level"]!=2){
   		    	$gb = 	new Application_Model_DbTable_DbGlobal();
   		    	$exist = $gb->userPurchaseOrderExist($id, $user["location_id"]);
   		    	if($exist==""){
   		    		$this->_redirect("purchase/index/index");
   		    	}
   		    } 		
    		$orderModel = new purchase_Model_DbTable_DbPurchaseOrder();
    		//get purchase info 23/8/13
    		$orderItemDetail=$orderModel->purchaseInfo($id);
    		    		
    		$this->view->order_info = $orderItemDetail;    		
    		//get qty on purchase order 23/8/13
    		$orderDetail = $orderModel->getPurchaseID($id);
    		$this->view->orderItemDetail = $orderDetail;
   	}
  }
   public function checkAction(){
   	$db = new Application_Model_DbTable_DbGlobal();
   	$this->_helper->layout->disableLayout();
   	$invoice = @$_POST['username'];
   	if($this->getRequest()->isPost()){
   		$post = $this->getRequest()->getPost();   			
   	}
   	if(isset($invoice)){
   		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
   		if($row){
   			Application_Form_FrmMessage::message("Order Is Exist !Please check again");
   		}
   		else{
   			echo "Is available!";
   		}
   	}
   	else{
   		echo "Invoice Number";
   }
   exit();
   }
   public function checkPurchasenoAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$_invoiceno = $_data["pur_no"];
   		$db = new Application_Model_DbTable_DbGlobal();
   		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE status=4 AND `order`= '$_invoiceno' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
   		$db_table = new Product_Model_DbTable_DbAddLocation();
   		$items_code = $db_table->getCodeItem($_invoiceno);
   		echo Zend_Json::encode($row);
   		exit();
   	}
   }
   
  public function getCustomerInfoAction(){
  
  	$post=$this->getRequest()->getPost();
  	$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
   		$row=$db->getGlobalDbRow($sql);
  	if(!$result){
  		$result = array('contact'=>'','phone'=>'');
  	}
  	echo Zend_Json::encode($result);
  	exit();
  } 
  
  
 
  public function addRecieveAction(){
  	$db = new Application_Model_DbTable_DbGlobal();
  	if($this->getRequest()->isPost()) {
  		$data = $this->getRequest()->getPost();
  		$payment_purchase_order = new purchase_Model_DbTable_DbPurchaseVendor();
  		$payment_purchase_order -> vendorPurchaseOrderPayment($data);
  		Application_Form_FrmMessage::message("Purchase has been received!");
  		if($data['payment']=='Save New'){
  			Application_Form_FrmMessage::redirectUrl("/purchase/index/add-purchase");
  			//$this->_redirect("purchase/index/add-purchase");
  		}
  		else{
  			$this->_redirect("/purchase/index/index");
  		}
  		//not yet use in this version
  		// 				elseif(@$data['Save']=='Save'){
  		// 					$payment_purchase_order = new purchase_Model_DbTable_DbPurchaseVendor();
  		// 					$payment_purchase_order -> VendorOrder($data);
  		// 					$this->_redirect("purchase/index/index");
  		// 				}
  		// 				elseif(@$data['New']=='New'){
  		// 					$this->_redirect("purchase/index/add-purchase");
  		// 				}
  	}
  	
  	$user = $this->GetuserInfoAction();
  	if($user["level"]!=1 AND $user["level"]!=2){
  		$this->_redirect("purchase/index/index");
  	}
  	///link left not yet get from DbpurchaseOrder
  	$frm_purchase = new Application_Form_purchase(null);
  	$form_add_purchase = $frm_purchase->productOrder(null);
  	Application_Model_Decorator::removeAllDecorator($form_add_purchase);
  	$this->view->form_purchase = $form_add_purchase;
  	
  	$formpopup = new Application_Form_FrmPopup(null);
  	$formproduct = $formpopup->popuProduct(null);
  	Application_Model_Decorator::removeAllDecorator($formproduct);
  	$this->view->form_product = $formproduct;
  	
  	//for customer
  	$formpopup = $formpopup->popupCustomer(null);
  	Application_Model_Decorator::removeAllDecorator($formpopup);
  	$this->view->form_customer = $formpopup;
  	//for add location
  	$formAdd = $formpopup->popuLocation(null);
  	Application_Model_Decorator::removeAllDecorator($formAdd);
  	$this->view->form_addstock = $formAdd;
  	
  	$form_agent = $formpopup->popupSaleAgent(null);
  	Application_Model_Decorator::removeAllDecorator($form_agent);
  	$this->view->form_agent = $form_agent;
  }
//   public function addPurchaseTestAction(){
//   	$db = new Application_Model_DbTable_DbGlobal();
//   	if($this->getRequest()->isPost()) {
//   		$data = $this->getRequest()->getPost();
//   		$payment_purchase_order = new purchase_Model_DbTable_DbPurchaseVendorTest();
//   		$payment_purchase_order -> vendorPurchaseOrderPayment($data);
//   		Application_Form_FrmMessage::message("Purchase has been received!");
//   		if($data['payment']=='Save New'){
//   			Application_Form_FrmMessage::redirectUrl("/purchase/index/add-purchase");
//   			//$this->_redirect("purchase/index/add-purchase");
//   		}
//   		else{
//   			$this->_redirect("/purchase/index/index");
//   		}
//   		//not yet use in this version
//   		// 				elseif(@$data['Save']=='Save'){
//   		// 					$payment_purchase_order = new purchase_Model_DbTable_DbPurchaseVendor();
//   		// 					$payment_purchase_order -> VendorOrder($data);
//   		// 					$this->_redirect("purchase/index/index");
//   		// 				}
//   		// 				elseif(@$data['New']=='New'){
//   		// 					$this->_redirect("purchase/index/add-purchase");
//   		// 				}
//   	}
  
//   	$user = $this->GetuserInfoAction();
//   	if($user["level"]!=1 AND $user["level"]!=2){
//   		$this->_redirect("purchase/index/index");
//   	}
//   	///link left not yet get from DbpurchaseOrder
//   	$frm_purchase = new Application_Form_purchase(null);
//   	$form_add_purchase = $frm_purchase->productOrder(null);
//   	Application_Model_Decorator::removeAllDecorator($form_add_purchase);
//   	$this->view->form_purchase = $form_add_purchase;
  
//   	// item option in select
//   	$items = new Application_Model_GlobalClass();
//   	$itemRows = $items->getProductOption();
//   	$this->view->items = $itemRows;
  
//   	//get control
//   	$formControl = new Application_Form_FrmAction(null);
//   	$formViewControl = $formControl->AllAction(null);
//   	Application_Model_Decorator::removeAllDecorator($formViewControl);
//   	$this->view->control = $formViewControl;
  
//   	// 		//for search
//   	// 		$search = new purchase_Form_FrmSearch();
//   	// 		$frmsearch= $search->formSearch();
//   	// 		Application_Model_Decorator::removeAllDecorator($frmsearch);
//   	// 		$this->view->get_frmsearch= $frmsearch;
  
//   	//for view left purchase order
//   	$vendor_sql = "SELECT p.order, p.all_total,p.paid,p.balance
// 		FROM tb_purchase_order AS p INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id ORDER BY p.timestamp DESC ";
//   	$rows=$db->getGlobalDb($vendor_sql);
//   	$this->view->list=$rows;
  
//   	//for add product;
//   	$formpopup = new Application_Form_FrmPopup(null);
//   	$formproduct = $formpopup->popuProduct(null);
//   	Application_Model_Decorator::removeAllDecorator($formproduct);
//   	$this->view->form = $formproduct;
  
//   	//for add vendor
//   	$formStockAdd = $formpopup->popupVendor(null);
//   	Application_Model_Decorator::removeAllDecorator($formStockAdd);
//   	$this->view->form_vendor = $formStockAdd;
  
//   	//for add location
//   	$formAdd = $formpopup->popuLocation(null);
//   	Application_Model_Decorator::removeAllDecorator($formAdd);
//   	$this->view->form_addstock = $formAdd;
  
//   }
  public function getpobyidAction(){
  	if($this->getRequest()->isPost()){
  		$db = new Application_Model_DbTable_DbGlobal();
  		$post=$this->getRequest()->getPost();
  		$invoice = $post['invoice_id'];
  		$sql = "SELECT `order` FROM `tb_purchase_order` WHERE `order`= '$invoice' LIMIT 1 ";
  		$row=$db->getGlobalDbRow($sql);
  		/*if(!$result){
  			$result = array('contact'=>'','phone'=>'');
  		}*/
  		echo Zend_Json::encode($row);
  		exit();
  	}
  }
  public function getqtybyidAction(){
  	if($this->getRequest()->isPost()){
  		$post=$this->getRequest()->getPost();
  		$item_id = $post['item_id'];
  		$sql = "SELECT `qty_perunit` FROM tb_product WHERE id= $item_id LIMIT 1 ";
  		$db = new Application_Model_DbTable_DbGlobal();
  		$row=$db->getGlobalDbRow($sql);
  		echo Zend_Json::encode($row);
  		exit();
  	}
  }
// update test

  public function checkInAction(){
  	try{
  	$db = new Application_Model_DbTable_DbGlobal();
  	if($this->getRequest()->isPost()) {
  		$data = $this->getRequest()->getPost();
  		$recieve_order = new purchase_Model_DbTable_DbPurchaseVendor();
  		
  		
  		if(isset($data['SaveNew'])){
  			try {
  			$recieve_order->RecievedPurchaseOrder($data);
  			Application_Form_FrmMessage::message("Purchase has been received!");
  			}catch (Exception $e){
  				echo $e->getMessage();
  			}
  			//Application_Form_FrmMessage::redirectUrl("/purchase/index/check-in");
  			//$this->_redirect("purchase/index/add-purchase");
  		}
  		else{
  			$recieve_order -> RecievedPurchaseOrder($data);
  			$this->_redirect("/purchase/index/index");
  		}
  		//not yet use in this version
  		// 				elseif(@$data['Save']=='Save'){
  		// 					$payment_purchase_order = new purchase_Model_DbTable_DbPurchaseVendor();
  		// 					$payment_purchase_order -> VendorOrder($data);
  		// 					$this->_redirect("purchase/index/index");
  		// 				}
  		// 				elseif(@$data['New']=='New'){
  		// 					$this->_redirect("purchase/index/add-purchase");
  		// 				}
  	}
  
  	$user = $this->GetuserInfoAction();
  	if($user["level"]!=1 AND $user["level"]!=2){
  		$this->_redirect("purchase/index/index");
  	}

  	$frm_purchase = new Application_Form_FrmCheckIn(null);
  	$form_add_purchase = $frm_purchase->productOrder(null);
  	Application_Model_Decorator::removeAllDecorator($form_add_purchase);
  	$this->view->form_purchase = $form_add_purchase;
  
  	// item option in select
  	$items = new Application_Model_GlobalClass();
  	$itemRows = $items->getProductOption();
  	$this->view->items = $itemRows;
  
  	//get control
  	$formControl = new Application_Form_FrmAction(null);
  	$formViewControl = $formControl->AllAction(null);
  	Application_Model_Decorator::removeAllDecorator($formViewControl);
  	$this->view->control = $formViewControl;
  
  	// 		//for search
  	// 		$search = new purchase_Form_FrmSearch();
  	// 		$frmsearch= $search->formSearch();
  	// 		Application_Model_Decorator::removeAllDecorator($frmsearch);
  	// 		$this->view->get_frmsearch= $frmsearch;
  
  	//for view left purchase order
  	$vendor_sql = "SELECT p.order, p.all_total,p.paid,p.balance
		FROM tb_purchase_order AS p INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id ORDER BY p.timestamp DESC ";
  	$rows=$db->getGlobalDb($vendor_sql);
  	$this->view->list=$rows;
  
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
  	 
  	}catch (Exception $e){
  		Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
  	}
  }

	
}