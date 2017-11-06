<?php
class sales_salesordertestController extends Zend_Controller_Action
{	
	
    public function init()
    {
        /* Initialize action controller here */
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
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$sale_order_sql = "SELECT o.order_id,o.order,o.date_order, o.status,c.cust_name, o.all_total, u.username
					  FROM tb_sales_order AS o ,tb_customer AS c, rsv_acl_user AS u
					  WHERE c.customer_id=o.customer_id AND o.user_mod = u.user_id ";
		
// 		$sale_order_sql = "SELECT o.order_id,o.order,o.date_order, o.status,c.cust_name, o.all_total, o.paid,o.balance
// 		FROM tb_sales_order AS o INNER JOIN tb_customer AS c ON c.customer_id=o.customer_id";
		
		
		$user = $this->GetuserInfoAction();
		$str_condition = " AND o.LocationId" ;
		$sale_order_sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
		
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			//echo $post["order"];
			if($post['order'] !=''){
					$sale_order_sql .= " AND o.order LIKE '%".$post['order']."%'";
			}
// 			if($post['customer_id'] !=''){
// 				$sale_order_sql .= " AND c.customer_id LIKE '%".$post['customer_id']."%'";
// 			}
			if($post['sale_agent_id']!='' AND $post['sale_agent_id']!=0){
				$sale_order_sql .= " AND o.sales_ref = ".$post['sale_agent_id'];
			}
			if($post['status'] !=''){
				$sale_order_sql .= " AND o.status =".$post['status'];
			}
			$start_date = $post['search_start_date'];
			$end_date = $post['search_end_date'];
			
			if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
				$sale_order_sql .= " AND o.date_order BETWEEN '$start_date' AND '$end_date'";
			}
		}
		else{
			//$sale_order_sql.="";
		}
		$sale_order_sql.=" ORDER BY o.order_id DESC";
			
		$rows=$db->getGlobalDb($sale_order_sql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getStatusType($rows, BASE_URL, true);
		$columns=array("ORDER_ADD_CAP","ORDER_DATE_CAP","STATUS_CAP", "CON_NAME_CAP","TOTAL_CAP_DOLLAR","BY_USER_CAP");
		//$columns=array("ORDER_ADD_CAP","ORDER_DATE_CAP","STATUS_CAP", "CON_NAME_CAP","TOTAL_CAP_DOLLAR","PAID_DOLLAR_CAP","BALANCE_CAP");
		$link=array(
				'module'=>'sales','controller'=>'sales-order','action'=>'detail-sales-order',
		);
		$urlEdit = BASE_URL . "/sales/sales-order/update-sales";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('order'=>$link,'cust_name'=>$link,'contact_name'=>$link),$urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	public function addCustomerOrderAction() {
		if($this->getRequest()->isPost()) {
		$data = $this->getRequest()->getPost();
			if(@$data['payment']=='payment'){
				$addOrder = new sales_Model_DbTable_DbSalesOrder();
				//new update but not done	$addOrder = new sales_Model_DbTable_DbSalesOrder();
				$addOrder->CustomerAddOrderPayment($data);
				$this->_redirect("sales/sales-order/index");
			}
			elseif(@$data['Save']=='Save'){
				$addOrder = new sales_Model_DbTable_DbSalesOrder();
				$addOrder->CustomerOrderSave($data);
				$this->_redirect("sales/sales-order/index");
			}	
	     //print_r($data); exit();	
		}
		$formStock = new Application_Form_purchase(null);
		$formStockAdd = $formStock->SalesOrder(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->items = $itemRows;
		
		$formControl = new Application_Form_FrmAction(null);
		$formViewControl = $formControl->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formViewControl);
		$this->view->control = $formViewControl;

		//for add product;
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
		
	}
	public function customerOrderAction() {
		if($this->getRequest()->isPost()) {
			try{
				$data = $this->getRequest()->getPost();
				$addOrder = new sales_Model_DbTable_DbSalesOrder();
				//new update but not done	$addOrder = new sales_Model_DbTable_DbSalesOrder();
				$addOrder->CustomerAddOrderPayment($data);
				if(@$data['payment']=='Save New'){
					Application_Form_FrmMessage::message("Sales order has been saved !");
					//Application_Form_FrmMessage::redirectUrl("sales/sales-order/customer-order");
					//$this->_redirect("sales/sales-order/customer-order");
				}
				else{
					Application_Form_FrmMessage::message("Sales order has been saved !");
					//Application_Form_FrmMessage::redirectUrl("sales/sales-order/customer-order");
// 					$this->_redirect("sales/sales-order/customer-order");
// 					$this->_redirect("sales/sales-order/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Sales order has been insert failed!");
				
			}
		}
		$formStock = new Application_Form_purchase(null);
		$formStockAdd = $formStock->SalesOrder(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->selectProductOption();
		$this->view->items = $itemRows;
	
		// 		//for search left
		// 		$search = new purchase_Form_FrmSearch();
		// 		$frmsearch= $search->formSearch();
		// 		Application_Model_Decorator::removeAllDecorator($frmsearch);
		// 		$this->view->get_frmsearch= $frmsearch;
	
		//for add product;
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
	public function deleteCustomerOrderAction() {
		$id = ($this->getRequest()->getParam('id'));
		$sql = "DELETE FROM tb_sales_order WHERE order_id IN ($id)";
		$deleteObj = new Application_Model_DbTable_DbGlobal();
		$deleteObj->deleteRecords($sql);
		$this->_redirect('/sales/sales-order/index');
	}
	public function updateCustomerOrderAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost()){
			//just block only but can use other version
			$data = $this->getRequest()->getPost();
// 			if($data["status"]!=="Paid"){
// 				if(@$data['payment']!==''){
// 					$update_payment_order = new sales_Model_DbTable_DbSalesOrder();
// 					$update_payment_order->updateCustomerOrderPayment($data);
// 				}
// 				elseif(@$data['Update']=='Update'){
// 					$update_order = new sales_Model_DbTable_DbSalesOrder();
// 					//not yet dork stock in table inventory
// 					$update_order->updateCustomerOrder($data);
// 				}
// 			}
// 			else{
// 				Application_Form_FrmMessage::message("Cann't Edit!Sales Order Has Been Payment Already");
// 				Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
// 			}
		//for new version 
			$sale_order = new sales_Model_DbTable_DbCustomerOrder();
			if(isset($data["payment"])){
				if($data["oldStatus"]==6){
					$addOrder = new sales_Model_DbTable_DbSalesOrder();
					$addOrder->CustomerAddOrderPayment($data);
					Application_Form_FrmMessage::message("You has been Re-Order successe!");
					Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				}
				else{
					$sale_order->updateCustomerOrder($data);
					Application_Form_FrmMessage::message("You have been Update customer order success! ");
					Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				}
			}
			elseif(isset($data["cancel_order"])){
				//for cancel customer order
				if($data["oldStatus"]!=6){
					$sale_order->cancelCustomerOrder($data);
					Application_Form_FrmMessage::message("You have been cancel customer order success! ");
					Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				}else{
					Application_Form_FrmMessage::message("You have been cancel customer order already!");
					Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				}
				
// 				$sale_order->cancelCustomerOrder($data);
// 				Application_Form_FrmMessage::message("You have been cancel customer order success! ");
// 				Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
			}
		}
		$user = $this->GetuserInfoAction();
		if($user["level"]!=1 AND $user["level"]!=2){
			echo $user["level"];
			$gb = 	new Application_Model_DbTable_DbGlobal();
			$exist = $gb->userSaleOrderExist($id, $user["location_id"]);
			if($exist==""){
				$this->_redirect("sales/sales-order/index");
			}
		}
		
		$sql = "SELECT o.order_id,o.customer_id,o.LocationId,o.order,o.sales_ref,o.date_order,o.status,o.payment_method,o.currency_id,
		o.remark,o.net_total,o.discount_type,o.discount_value,o.paid,o.all_total,o.balance,
		c.contact_name,c.phone,c.add_name,c.add_remark
		FROM tb_sales_order AS o
		INNER JOIN tb_customer AS c ON c.customer_id= o.customer_id
		INNER JOIN tb_sales_order_item AS so ON so.order_id=o.order_id
		WHERE o.order_id=".$id;
		$db = new Application_Model_DbTable_DbGlobal();
		$row = $db->getGlobalDbRow($sql);
	
		$formStock = new Application_Form_purchase();
		$formStockEdit = $formStock->SalesOrder($row);
	
		Application_Model_Decorator::removeAllDecorator($formStockEdit);// omit default zend html tag
		$this->view->form = $formStockEdit;
	
		if($row['status']==1){
			$this->_redirect("sales/sales-order/update-customer-quote/id/$id");
		}
		//get item of this lost
		$orderModel = new sales_Model_DbTable_DbOrder();
		$orderDetail = $orderModel->getSalesOderID($id);
		$this->view->rowsOrder = $orderDetail;
	
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->selectProductOption();
		$this->view->itemsOption = $itemRows;
	
// 		$formControl = new Application_Form_FrmAction(null);
// 		$formViewControl = $formControl->AllAction(null);
// 		Application_Model_Decorator::removeAllDecorator($formViewControl);
// 		$this->view->control = $formViewControl;
	
	
		//for add product;
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
	//for update sale order when sale status paid
	public function abcAction(){
		//Application_Form_FrmMessage::message("Cann't Edit!Sales Order Has Been Payment Already");
		//Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
		
	}	
	public function addCustomerQuoteAction(){
		if($this->getRequest()->isPost()) {
			    $data = $this->getRequest()->getPost();
				$addOrder = new sales_Model_DbTable_DbSalesOrder();
				if($data['Save']=="Save"){
					$addOrder->addQuote($data);
					$this->_redirect("/sales/sales-order/index");
				}
				elseif($data['quote']!==""){
					$addOrder->convertQuote($data);
					$this->_redirect("/sales/sales-order/index");
				}	
		}
		$session_stock=new Zend_Session_Namespace('stock');
		$formStock = new Application_Form_purchase(null);
		$formStockAdd = $formStock->SalesOrder(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption($session_stock->stockID);
		$this->view->items = $itemRows;
		$formControl = new Application_Form_FrmAction(null);
		$formViewControl = $formControl->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formViewControl);
		$this->view->control = $formViewControl;
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form_product = $formproduct;
		
		//for customer
		$formpopup = $formpopup->popupCustomer(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		//for add location
		$formAdd = $formpopup->popuLocation(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;		
	}
	public function updateCustomerQuoteAction(){
		$session_stock=new Zend_Session_Namespace('stock');
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			
			$update_to_quote = new sales_Model_DbTable_DbSalesOrder();
				if(@$data["quote"]!=""){
					
					$update_to_quote->updateCustomerQuote($data);
				}
				elseif(@$data['Update']!=""){		
					
					$update_to_quote->quoteUpdate($data); 
									}
				$this->_redirect("sales/sales-order/index");
		}
		
		$sql = "SELECT o.order_id,o.customer_id,o.LocationId,o.order,o.sales_ref,o.date_order,o.status,o.payment_method,o.currency_id,
		o.remark,o.net_total,o.discount_type,o.discount_value,o.paid,o.all_total,o.balance,
		c.contact_name,c.phone,c.add_name,c.add_remark
		FROM tb_sales_order AS o
		INNER JOIN tb_customer AS c ON c.customer_id= o.customer_id
		INNER JOIN tb_sales_order_item AS so ON so.order_id=o.order_id
		WHERE o.order_id=".$id;
		$db = new Application_Model_DbTable_DbGlobal();
		$row = $db->getGlobalDbRow($sql);
		
		$formStock = new Application_Form_purchase();
		$formStockEdit = $formStock->SalesOrder($row, $session_stock->stockID);
		
		Application_Model_Decorator::removeAllDecorator($formStockEdit);// omit default zend html tag
		$this->view->form = $formStockEdit;
		
		//get item of this lost
		$orderModel = new sales_Model_DbTable_DbOrder();
		$orderDetail = $orderModel->getSalesOderID($id);
		$this->view->rowsOrder = $orderDetail;
		
		// item option in select
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption($session_stock->stockID);
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
		$formproduct = $formpopup->popuProduct(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form_product = $formproduct;
		
		//for customer
		$formpopup = $formpopup->popupCustomer(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		//for add location
		$formAdd = $formpopup->popuLocation(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	}
	
	/* check available order Price*/
	public function checkAction(){
			$db = new Application_Model_DbTable_DbGlobal();
			$this->_helper->layout->disableLayout();
			$username = @$_POST['username'];
			if($this->getRequest()->isPost()){
				$post = $this->getRequest()->getPost();
				
			}
			if(isset($username)){
				    $sql = "SELECT item_name FROM tb_product WHERE item_name = '$username'";
					$row=$db->getGlobalDbRow($sql);
					if($row){
						echo "<div style='color: red; font-weight: bold;'>Product name is exist.</div>";
					}
					else{
						echo "<span style='font-weight: bold;'>$username</span> is available!";						
					}
				}
			else{
				echo "Enter Product Name";
			}
				exit();
		}
		public function detailSalesOrderAction()
		{
			if($this->getRequest()->getParam('id')) {
				$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
				//if user type wrong url
				$user = $this->GetuserInfoAction();
				if($user["level"]!=1 AND $user["level"]!=2){
					echo $user["level"];
					$gb = 	new Application_Model_DbTable_DbGlobal();
					$exist = $gb->userSaleOrderExist($id, $user["location_id"]);
 					if($exist==""){
 						$this->_redirect("sales/sales-order/index");
 					}
				}
				$orderModel = new sales_Model_DbTable_DbOrder();
				$rowOrder = $orderModel->getOrderItemInfoByID($id);
				if(!empty($rowOrder)){
					$this->view->orderIteminfo = $rowOrder;
				}
				else{
					$this->_redirect("sales/sales-order/index");
				}
		
			}
			$formStock = new Application_Form_purchase(null);
			$session_stock=new Zend_Session_Namespace('stock');
			
			$sales = new sales_Model_DbTable_DbOrder();
			$row = $sales->getSalesOderID($id);
		    $this->view->orderItemDetail=$row;
		
		    $purchase_info= $sales->getOrderItemInfoByID($id);
			$this->view->sales_info = $purchase_info;
		
		}
		//for get customer info
		public function getCustomerInfoAction(){
			$post=$this->getRequest()->getPost();
			$get_customer = new sales_Model_DbTable_DbOrder();
			$result = $get_customer->getCustomerInfo($post);
			if(!$result){
				$result = array('contact'=>'','phone'=>'');
			}
			echo Zend_Json::encode($result);
			exit();
		}
		//for get current price getCurrentPrice
		public function getCurrentPriceAction(){//dynamic by customer
		
			$post=$this->getRequest()->getPost();
			$get_current_price = new sales_Model_DbTable_DbReturnStock();
			$result = $get_current_price->getPriceByCustomer($post["item_id"],$post["customerid"]);
			if(!$result){
				$result = array('price'=>'0');
			}
			echo Zend_Json::encode($result);
			exit();
		}
		public function getqtyByitemAction(){
			if($this->getRequest()->isPost()){
				$_data = $this->getRequest()->getPost();
				$_dbdata = new sales_Model_DbTable_DbReturnStock();
				$result = $_dbdata->getQtyByItemId($_data["item_id"],$_data["item_qty"]);
				if(empty($result)){
					$result= array("message"=>"");
				}
				echo Zend_Json::encode($result);
				exit();
			}
		}
		
		public function salesAction(){
				
			if($this->getRequest()->isPost()) {
				try{
					$data = $this->getRequest()->getPost();
					$addOrder = new sales_Model_DbTable_DbSalesOrder();
					$addOrder->CustomerAddOrderPayment($data);
					Application_Form_FrmMessage::message("Sales order has been saved !");
					//Application_Form_FrmMessage::redirectUrl("/sales/sales-order/sales");
				}catch (Exception $e){
					Application_Form_FrmMessage::message("Sales order has been insert failed!");
						
				}
			}
			$formStock = new Application_Form_purchase(null);
			$formStockAdd = $formStock->SalesOrder(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			// item option in select
			$items = new Application_Model_GlobalClass();
			$itemRows = $items->selectProductOption();
			$this->view->items = $itemRows;
				
			// 		//for search left
			// 		$search = new purchase_Form_FrmSearch();
			// 		$frmsearch= $search->formSearch();
			// 		Application_Model_Decorator::removeAllDecorator($frmsearch);
			// 		$this->view->get_frmsearch= $frmsearch;
				
			//for add product;
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
		public function updateSalesAction(){
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
			if($this->getRequest()->isPost()){
				//just block only but can use other version
				$data = $this->getRequest()->getPost();
					
				// 			if($data["status"]!=="Paid"){
				// 				if(@$data['payment']!==''){
				// 					$update_payment_order = new sales_Model_DbTable_DbSalesOrder();
				// 					$update_payment_order->updateCustomerOrderPayment($data);
				// 				}
				// 				elseif(@$data['Update']=='Update'){
				// 					$update_order = new sales_Model_DbTable_DbSalesOrder();
				// 					//not yet dork stock in table inventory
				// 					$update_order->updateCustomerOrder($data);
				// 				}
				// 			}
				// 			else{
				// 				Application_Form_FrmMessage::message("Cann't Edit!Sales Order Has Been Payment Already");
				// 				Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				// 			}
				//for new version
				$sale_order = new sales_Model_DbTable_DbCustomerOrder();
				if(isset($data["payment"])){
					if($data["oldStatus"]==6){
						$addOrder = new sales_Model_DbTable_DbSalesOrder();
						$addOrder->CustomerAddOrderPayment($data);
						Application_Form_FrmMessage::message("You has been Re-Order successe!");
						Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
					}
					else{
						$sale_order->updateCustomerOrder($data);
						Application_Form_FrmMessage::message("You have been Update customer order success! ");
						Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
					}
				}
				elseif(isset($data["cancel_order"])){
					//for cancel customer order
					if($data["oldStatus"]!=6){
						$sale_order->cancelCustomerOrder($data);
						Application_Form_FrmMessage::message("You have been cancel customer order success! ");
						Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
					}else{
						Application_Form_FrmMessage::message("Can not cancel again!Becuase You have been cancel customer order already!");
						Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
					}
		
					// 				$sale_order->cancelCustomerOrder($data);
					// 				Application_Form_FrmMessage::message("You have been cancel customer order success! ");
					// 				Application_Form_FrmMessage::redirectUrl("/sales/sales-order/index");
				}
			}
			$user = $this->GetuserInfoAction();
			if($user["level"]!=1 AND $user["level"]!=2){
				$gb = 	new Application_Model_DbTable_DbGlobal();
				$exist = $gb->userSaleOrderExist($id, $user["location_id"]);
				if($exist==""){
					$this->_redirect("sales/sales-order/index");
				}
			}
		
			$sql = "SELECT o.order_id,o.customer_id,o.LocationId,o.order,o.sales_ref,o.date_order,o.status,o.payment_method,o.currency_id,
			o.remark,o.net_total,o.discount_type,o.discount_value,o.paid,o.all_total,o.balance,
			c.contact_name,c.phone,c.add_name,c.add_remark
			FROM tb_sales_order AS o
			INNER JOIN tb_customer AS c ON c.customer_id= o.customer_id
			INNER JOIN tb_sales_order_item AS so ON so.order_id=o.order_id
			WHERE o.order_id=".$id;
			$db = new Application_Model_DbTable_DbGlobal();
			$row = $db->getGlobalDbRow($sql);
		
			$formStock = new Application_Form_purchase();
			$formStockEdit = $formStock->SalesOrder($row);
		
			Application_Model_Decorator::removeAllDecorator($formStockEdit);// omit default zend html tag
			$this->view->form = $formStockEdit;
			$this->view->status_so = $row['status'];
		
			if($row['status']==1){
				$this->_redirect("sales/sales-order/update-customer-quote/id/$id");
			}
			//get item of this lost
			$orderModel = new sales_Model_DbTable_DbOrder();
			$orderDetail = $orderModel->getSalesOderID($id);
			$this->view->rowsOrder = $orderDetail;
		
			// item option in select
			$items = new Application_Model_GlobalClass();
			$itemRows = $items->getProductOption();
			$this->view->itemsOption = $itemRows;
		
			$items = new Application_Model_GlobalClass();
			$itemRows = $items->getProductOption();
			$this->view->items = $itemRows;
		
			// 		$formControl = new Application_Form_FrmAction(null);
			// 		$formViewControl = $formControl->AllAction(null);
			// 		Application_Model_Decorator::removeAllDecorator($formViewControl);
			// 		$this->view->control = $formViewControl;
		
		
			//for add product;
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
}