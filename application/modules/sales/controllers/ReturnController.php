<?php
class sales_returnController extends Zend_Controller_Action
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
		$formFilter = new sales_Form_FrmSearch();
		$frmFilter=$formFilter->FrmSearchFromCustomer();
		$this->view->formFilter = $frmFilter;
		Application_Model_Decorator::removeAllDecorator($frmFilter);
		
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT 
					r.return_id, 
					r.return_no,
					r.invoice_no, 
					r.date_return, 
					c.cust_name,
				    r.status, 
					r.all_total
				FROM tb_return_customer_in AS r 
					INNER JOIN tb_customer AS c ON c.customer_id=r.customer_id 
				
				";
		
		$user = $this->GetuserInfoAction();
    	$str_condition = " AND r.location_id" ; 
    	$sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
		
		if($this->getRequest()->isPost()){
				$post = $this->getRequest()->getPost();
				//echo $post["order"];
				if($post['order'] !=''){
						$sql .= " AND r.return_no LIKE '%".trim($post['order'])."%'";
				}
				if($post['customer_id'] !='' AND  trim($post['customer_id']) !=0){
					$sql .= " AND c.customer_id =".trim($post['customer_id']);
				}
				$start_date = trim($post['search_start_date']);
				$end_date = trim($post['search_end_date']);
				
				if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
					$sql .= " AND r.date_return BETWEEN '$start_date' AND '$end_date'";
				}
		}
		$sql.=" ORDER BY r.return_id DESC";
		$rows=$db->getGlobalDb($sql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getReturnStatusType($rows, BASE_URL, true);
		$columns=array("RETURN_NO","INVOICE_NO","RETURN_DATE_CAP", "CUSTOMER_CAP","STATUS",
				 "TOTAL_CAP_DOLLAR");
		$link=array(
				'module'=>'sales','controller'=>'return','action'=>'detail-return-item',
		);
		$urlEdit = BASE_URL . "/sales/return/update-return-item";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('return_no'=>$link),$urlEdit);
	}	
	public function addReturnItemAction(){
		if($this->getRequest()->isPost()){
			try{//for get return item form customer
			    $data = $this->getRequest()->getPost();
				$return = new sales_Model_DbTable_DbReturnItem();
				$return ->returnItem($data);
				if(isset($data["Save"])){
					$this->_redirect("/sales/return");
				}else{
					Application_Form_FrmMessage::message("Data has been insert!");
				}
			}catch (Exception $e){
				echo $e->getMessage();
			}
		}
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->CustomerReturnItem(null);
		Application_Model_Decorator::removeAllDecorator($frm_return);
		$this->view->form_return = $frm_return;
		
		$formAddProdcut = new Application_Form_FrmAction(null);
		$FrmAdd = $formAddProdcut->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($FrmAdd);
		$this->view->control = $FrmAdd;
		
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$itemRows = $getOption->getProductOption();
		$this->view->productOption = $itemRows;
		
		
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
		
		//for add CUSTOMER
		
		$formcustomer = $formpopup->popupCustomer(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form_customer = $formcustomer;
		
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	}
	public function returnOutAction()
	{
		$formFilter = new sales_Form_FrmSearch();
		$frmFilter=$formFilter->FrmSearchFromCustomer();
		$this->view->formFilter = $frmFilter;
		Application_Model_Decorator::removeAllDecorator($frmFilter);
	
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT 
				  ro.returnout_id,
				  ro.returnout_no,
				  ri.return_no,
				  ro.date_return_in,
				  ro.date_return_out,
				  ro.all_total 
				FROM
				  tb_return_customer_in AS ri,
				  tb_return_customer_out AS ro 
				WHERE ri.return_id = ro.returnin_id ";
	
		$user = $this->GetuserInfoAction();
		$str_condition = " AND ro.location_id" ;
		$sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
	
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			if($post['order'] !=''){
				$sql .= " AND ro.returnout_no LIKE '%".trim($post['order'])."%'";
			}
			if($post['return_in']!=''){
				$sql .= " AND ri.return_no LIKE '%".trim($post['return_in'])."%'";
			}
			$start_date = trim($post['search_start_date']);
			$end_date = trim($post['search_end_date']);
	
			if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
				$sql .= " AND ro.date_return_out BETWEEN '$start_date' AND '$end_date'";
			}
		}
		$sql.=" ORDER BY ro.returnout_id DESC";
		$rows=$db->getGlobalDb($sql);
		$columns=array("RETURN_OUT_CAP","RETURN_IN_CAP","RETURN_DATE_CAP","RETURN_OUT_DATE",
				"TOTAL_CAP_DOLLAR");
		$link=array(
				'module'=>'sales','controller'=>'return','action'=>'detail-return-itemout',
		);
		$urlEdit = BASE_URL . "/sales/return/update-item-tocustomer";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('returnout_no'=>$link),$urlEdit);
	}
	
	public function itemTocustomerAction(){ ///return item to customer
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$return = new sales_Model_DbTable_DbReturnItem();
				$return ->returnItemToCustomer($data);///return item to customer out
				if(isset($data["Save"])){
					$this->_redirect("/sales/return/return-out");
				}else{
					Application_Form_FrmMessage::message("Data has been insert!");
				}
			}catch (Exception $e){
				echo $e->getMessage();
			}
		}
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->ReturnItemToCustomer(null);
		Application_Model_Decorator::removeAllDecorator($frm_return);
		$this->view->form_return = $frm_return;
	
		$formAddProdcut = new Application_Form_FrmAction(null);
		$FrmAdd = $formAddProdcut->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($FrmAdd);
		$this->view->control = $FrmAdd;
	
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$itemRows = $getOption->getProductOption();
		$this->view->productOption = $itemRows;
	
	
	
// 		//for add product;
// 		$formpopup = new Application_Form_FrmPopup(null);
// 		$formproduct = $formpopup->popuProduct(null);
// 		Application_Model_Decorator::removeAllDecorator($formproduct);
// 		$this->view->form = $formproduct;
	
// 		//for add CUSTOMER
	
// 		$formcustomer = $formpopup->popupCustomer(null);
// 		Application_Model_Decorator::removeAllDecorator($formcustomer);
// 		$this->view->form_customer = $formcustomer;
	
// 		//for add location
// 		$formAdd = $formpopup->popuLocation(null);
// 		Application_Model_Decorator::removeAllDecorator($formAdd);
// 		$this->view->form_addstock = $formAdd;
	}
	
	public function updateItemTocustomerAction(){ ///return item to customer
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				//print_r($data);exit();
				$return = new sales_Model_DbTable_DbReturnItem();
				$return ->updateReturnItemToCustomer($data);///return item to customer out
				if(isset($data["Save"])){
					$this->_redirect("/sales/return/return-out");
				}else{
					Application_Form_FrmMessage::message("Data has been insert!");
				}
			}catch (Exception $e){
				echo $e->getMessage();
			}
		}
		
		$db = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT 
				  ci.`return_id`,
				  ci.`return_no`,
				  ci.`customer_id`,
				  co.`returnout_id`,
				  co.`returnout_no`,
				  co.date_return_in,
				  co.date_return_out,
				  co.`location_id`,
				  co.`all_total` 
				FROM
				  tb_return_customer_in AS ci,
				  tb_return_customer_out AS co 
				WHERE co.`returnin_id` = ci.`return_id` 
				  AND co.`returnout_id` = $id ";
		$result = $db->getGlobalDbRow($sql);
		//print_r($result);
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->ReturnItemToCustomer($result);
		Application_Model_Decorator::removeAllDecorator($frm_return);
		$this->view->form_return = $frm_return;
		
		$sql_return_item ="SELECT 
							  rco.`id`,
							  rco.`return_id`,
							  rco.`pro_id`,
							  rco.`qty_return`,
							  rco.`price`,
							  rco.`sub_total`,
							  rco.`return_remark` 
							FROM
							  tb_return_customer_item_out AS rco,
							  tb_return_customer_out AS co 
							WHERE rco.`return_id` = co.`returnout_id` 
							  AND co.`returnout_id` = $id ";
		$result_return_item = $db->getGlobalDb($sql_return_item);
		//print_r($result_return_item);
		$this->view->return_item = $result_return_item;
		
		$formAddProdcut = new Application_Form_FrmAction(null);
		$FrmAdd = $formAddProdcut->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($FrmAdd);
		$this->view->control = $FrmAdd;
	
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$itemRows = $getOption->getProductOption();
		$this->view->productOption = $itemRows;
	
	
	
		// 		//for add product;
		// 		$formpopup = new Application_Form_FrmPopup(null);
		// 		$formproduct = $formpopup->popuProduct(null);
		// 		Application_Model_Decorator::removeAllDecorator($formproduct);
		// 		$this->view->form = $formproduct;
	
		// 		//for add CUSTOMER
	
		// 		$formcustomer = $formpopup->popupCustomer(null);
		// 		Application_Model_Decorator::removeAllDecorator($formcustomer);
		// 		$this->view->form_customer = $formcustomer;
	
		// 		//for add location
		// 		$formAdd = $formpopup->popuLocation(null);
		// 		Application_Model_Decorator::removeAllDecorator($formAdd);
		// 		$this->view->form_addstock = $formAdd;
		
		
		$return_info = "SELECT 
						  ci.`return_id`,
						  ci.`return_no`,
						  ci.`customer_id`,
						  ci.`date_return`,
						  rco.`returnout_id`,
						  rco.`returnout_no`,
						  rco.`date_return_out`,
						  rco.`all_total`,
						  c.`cust_name`,
						  c.`add_name`,
						  c.`phone`
						FROM
						  tb_return_customer_in AS ci ,
						  tb_return_customer_out AS rco,
						  tb_customer AS c
						  WHERE ci.`customer_id`=c.`customer_id`
						  AND rco.`returnin_id`=ci.`return_id`
						  AND rco.`returnout_id`=$id";
		
		$rs_returninfo = $db->getGlobalDbRow($return_info);
		$this->view->returninfo = $rs_returninfo;
		//print_r($rs_returninfo);
		$sql_return = "SELECT 
						  cio.* ,
						  p.`item_name`,
						  vs.`qty_return` as v_qtyreturn,
						  vs.`sub_total` as v_subtotal
						FROM
						  `tb_return_customer_item_out` AS cio,
						  tb_return_customer_out AS ro,
						  tb_product AS p,
						  v_sumreturnout AS vs
						WHERE cio.`return_id` = ro.`returnout_id`
						  AND ro.`returnout_id`=vs.`returnout_id`
						  AND cio.`pro_id`=p.`pro_id`
						  AND ro.`returnout_id`=$id";
		
		$rs_return = $db->getGlobalDb($sql_return);
		$this->view->rs_return = $rs_return;
	}
	
	public function updateReturnItemAction(){
		$session_stock = new Zend_Session_Namespace('stock');
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Application_Model_DbTable_DbGlobal();
		//print_r($id);
		$sql = "SELECT status FROM tb_return_customer_in WHERE return_id =".$id;
		$rs = $db->getGlobalDbRow($sql);
		//print_r($rs);//exit();
		
			$row=$db->getSettingById(17);
			//print_r($row);
			if($rs["status"] == 0 AND $row['key_value']==0){
				Application_Form_FrmMessage::message("Can not Update returen Because Return To customer already!!");
				Application_Form_FrmMessage::redirectUrl("/sales/return");
			}else

		if($this->getRequest()->isPost()){
			try{//for get return item form customer
				$data = $this->getRequest()->getPost();
				$return = new sales_Model_DbTable_DbReturnItem();
				$return ->updateReturnItem($data);
				if(isset($data["Save"])){
					//$this->_redirect("/sales/return");
				}else{
					Application_Form_FrmMessage::message("Data has been insert!");
				}
			}catch (Exception $e){
				echo $e->getMessage();
			}
		}
		
		
		$return_info = "SELECT 
						  ci.`return_id`,
						  ci.`return_no`,
						  ci.`customer_id`,
						  ci.`date_return`,
						  ci.`location_id`,
						  ci.`all_total` ,
						  c.`cust_name`,
						  c.`add_name`,
						  c.`phone`
						FROM
						  tb_return_customer_in AS ci ,
						  tb_customer AS c
						  WHERE ci.`customer_id`=c.`customer_id`
						  AND ci.`return_id`=$id";
		
		$rs_returninfo = $db->getGlobalDbRow($return_info);
		$this->view->returninfo = $rs_returninfo;
			//print_r($rs_returninfo);
		$sql_return = "SELECT 
						  cin.* ,
						  p.`item_name`,
						  vs.`qty_return` AS v_qtyreturn,
						  vs.`sub_total` AS v_subtotal
						FROM
						  tb_return_customer_item_in AS cin,
						  tb_return_customer_in AS c ,
						  tb_product AS p,
						  v_sumreturn AS vs
						WHERE cin.`return_id` = c.`return_id` 
						  AND c.`return_id`=vs.`return_id`
						  AND cin.`pro_id`=p.`pro_id`
						  AND c.`return_id`=$id";
		
		$rs_return = $db->getGlobalDb($sql_return);
		$this->view->rs_return = $rs_return;
			//print_r($rs_return);
			
		$returnModel = new sales_Model_DbTable_DbReturnQuery();
		$row_info= $returnModel->getCustomerInfoIn($id);
		//print_r($row_info);
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->CustomerReturnItem($row_info);
		Application_Model_Decorator::removeAllDecorator($frm_return);
		$this->view->form_return = $frm_return;
		
		$returnDetail = $returnModel->getReturnInItem($id);
		$this->view->returnOrder = $returnDetail;
		$date = date("Y/m/d");
		//print_r($returnDetail);//exit();
		
		$formAddProdcut = new Application_Form_FrmAction(null);
		$FrmAdd = $formAddProdcut->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($FrmAdd);
		$this->view->control = $FrmAdd;
		
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$itemRows = $getOption->getProductOption();
		$this->view->productOption = $itemRows;
		
		
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
		
		//for add CUSTOMER
		
		$formcustomer = $formpopup->popupCustomer(null);
		Application_Model_Decorator::removeAllDecorator($formcustomer);
		$this->view->form_customer = $formcustomer;
		
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
		///////////////////////////////////////////////////////////////////////////////////////////////////
		
		
// 		$get_form = new Application_Form_FrmReturnItem();
// 		$session_stock = new Zend_Session_Namespace('stock');
// 		$frm_return = $get_form->returnItemForm($row_info);
// 		Application_Model_Decorator::removeAllDecorator($frm_return);
// 		$this->view->form_return = $frm_return;
		
		//get qty of return item
// 		$getReturnItem = $returnModel->getReturnItem($id);
// 		$this->view->returnItemDetail = $getReturnItem;
		
		//get return item		
		
// 		$getOption = new Application_Model_GlobalClass();
// 		$locationRows = $getOption->getLocationOption();
// 		$this->view->locationOption = $locationRows;
		
// 		$itemRows = $getOption->getProductOption();
// 		$this->view->productOption = $itemRows;
		
		
		$this->view->getorder_id = $id;
	}
	public function detailReturnItemAction() {
		if($this->getRequest()->getParam('id')) {
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
			$returnModel = new sales_Model_DbTable_DbReturnQuery();
			$customer_info = $returnModel->getCustomerInfoIn($id);
			if(!empty($customer_info)){
				$this->view->return_info = $customer_info;
			}
			else{
				$this->_redirect("/sales/return");
			}
			//get qty of itme 
 			$getReturnItem = $returnModel->getReturnInItem($id);
			$this->view->returnItemDetail = $getReturnItem;
		}
	}
	public function detailReturnItemoutAction() {//detail return customer to stock out
		if($this->getRequest()->getParam('id')) {
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
	
			$returnModel = new sales_Model_DbTable_DbReturnQuery();
			$reference_info = $returnModel->getReferenceInfo($id);
			if(!empty($reference_info)){
				$this->view->refer_info = $reference_info;
			}
			else{
				$this->_redirect("/sales/return/return-out");
			}
			//get qty of itme
			$getReturnItem = $returnModel->getReturnItemOut($id);
			$this->view->returnItemDetail = $getReturnItem;
		}
	}

	public function getReturnAction(){
		if($this->getRequest()->isPost()){
			$db= new Application_Model_DbTable_DbGlobal();
			$post=$this->getRequest()->getPost();
			$return_id = $post['return'];
			$sqlinfo ="SELECT * FROM `tb_return_customer_in` WHERE return_id = $return_id LIMIT 1";
			$rowinfo=$db->getGlobalDbRow($sqlinfo);
			$sql = "SELECT 
					  rvi.qty_return,
					  rvi.pro_id,
					  rvi.price,
					  rvi.sub_total,
					  rvi.return_remark,
					  (SELECT rt.return_no FROM tb_return_customer_in AS rt WHERE rt.return_id = rvi.return_id LIMIT 1) AS return_no,
					  (SELECT rt.all_total FROM tb_return_customer_in AS rt WHERE rt.return_id = rvi.return_id LIMIT 1) AS all_total,
					  (SELECT pr.qty_perunit FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS qty_perunit,
					  (SELECT pr.item_name FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS item_name,
					  (SELECT pr.pro_id FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS pro_id,
					  (SELECT `label` FROM tb_product AS pr  WHERE pr.pro_id = rvi.pro_id  LIMIT 1) AS label,
					  (SELECT `measure_name` FROM `tb_measure` AS ms WHERE ms.id = (SELECT measure_id FROM tb_product WHERE pro_id = rvi.`pro_id`)) AS measure_name 
					FROM
					  `tb_return_customer_item_in` AS rvi 
					WHERE rvi.return_id =".$return_id;
			$rows=$db->getGlobalDb($sql);
			$result = array('poinfo'=>$rowinfo,'item'=>$rows);
			echo Zend_Json::encode($result);
			exit();
		}
	
	}
	
}
