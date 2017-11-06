<?php
class purchase_returnController extends Zend_Controller_Action
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
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$vendor_sql = "SELECT r.return_id, r.return_no, r.date_return, v.v_name, r.all_total,r.paid,r.balance
						FROM tb_return AS r INNER JOIN tb_vendor AS v ON v.vendor_id=r.vendor_id ";
		
		$user = $this->GetuserInfoAction();
    	$str_condition = "INNER JOIN tb_return_vendor_item  As rv ON r.return_id = rv.return_id WHERE rv.location_id" ; 
    	$vendor_sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
		
		if($this->getRequest()->isPost()){
				$post = $this->getRequest()->getPost();
				//echo $post["order"];
				if($post['order'] !=''){
						$vendor_sql .= " AND r.return_no LIKE '%".trim($post['order'])."%'";
				}
				if($post['vendor_name'] !='' AND  $post['vendor_name'] !=0){
					$vendor_sql .= " AND v.vendor_id =".trim($post['vendor_name']);
				}
// 				if($post['phone'] !=''){
// 					$vendor_sql .= " AND v.phone LIKE '%".$post['phone']."%'";
// 				}
// 				if($post['status'] !=''){
// 					$vendor_sql .= " AND p.status =".$post['status'];
// 				}
				$start_date = $post['search_start_date'];
				$end_date = $post['search_end_date'];
				
				if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
					$vendor_sql .= " AND r.date_return BETWEEN '$start_date' AND '$end_date'";
				}
		}
		$vendor_sql.=" ORDER BY r.return_id DESC";
		$rows=$db->getGlobalDb($vendor_sql);
		$columns=array("RETURN_NO","RETURN_DATE_CAP", "VENDOR_NAME_CAP",
				 "TOTAL_CAP_DOLLAR","PAID_DOLLAR_CAP","BALANCE_CAP");
		$link=array(
				'module'=>'purchase','controller'=>'return','action'=>'detail-return-item',
		);
		$urlEdit = BASE_URL . "/purchase/return/update-return-item";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('return_no'=>$link),$urlEdit);
	}

	public function returnInAction()
	{
		$formFilter = new purchase_Form_FrmSearch();
		$this->view->formFilter = $formFilter->frmRetrunIn();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	
		$list = new Application_Form_Frmlist();
		$db = new Application_Model_DbTable_DbGlobal();
		$vendor_sql = "SELECT ri.returnin_id, ro.return_no, ri.returnin_no, ri.date_return_in,  ri.all_total
		FROM tb_return_vendor_in AS ri,tb_return AS ro WHERE ri.returnout_id= ro.return_id ";
	
// 		$user = $this->GetuserInfoAction();
// 		$str_condition = "INNER JOIN tb_return_vendor_item  As rv ON r.return_id = rv.return_id WHERE rv.location_id" ;
// 		$vendor_sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
	
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			//echo $post["order"];
			if($post['invoice_in'] !=''){
				$vendor_sql .= " AND ri.returnin_no LIKE '%".trim($post['invoice_in'])."%'";
			}
			if($post['invoice_out'] !=''){
				$vendor_sql .= " AND ro.return_no LIKE '%".trim($post['invoice_out'])."%'";
			}
			$start_date = trim($post['search_start_date']);
			$end_date = trim($post['search_end_date']);
			if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
				$vendor_sql .= " AND ri.date_return BETWEEN '$start_date' AND '$end_date'";
			}
		}
		$vendor_sql.=" ORDER BY ri.returnin_id DESC";
		$rows=$db->getGlobalDb($vendor_sql);
		$columns=array("RETURN_NO","INVOICE_NO","RETURN_DATE_CAP","TOTAL_CAP_DOLLAR");
		$link=array(
				'module'=>'purchase','controller'=>'return','action'=>'detail-return-itemin',
		);
		$urlEdit = BASE_URL . "/purchase/return/update-return-item-in";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('returnin_no'=>$link),$urlEdit);
	}
	
	public function addReturnItemAction(){
		$post = $this->getRequest()->getPost();
		if($post){
			$return = new purchase_Model_DbTable_DbReturnItem();
			$return ->returnItem($post);
			
			if(!empty($post["SaveNew"])){
				Application_Form_FrmMessage::message("Your items has been out from stock");
				print_r($post);
			}
			elseif(!empty($post["Save"])){
				$this->_redirect("/purchase/return");
				print_r($post);
			}
			else{
				$this->_redirect("purchase/return");
			}
			
		}
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->returnItemForm(null);
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
		
		//for add vendor
		$formStockAdd = $formpopup->popupVendor(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form_vendor = $formStockAdd;
		
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	}
	
	
	public function getReturnAction(){
		if($this->getRequest()->isPost()){
			$db= new Application_Model_DbTable_DbGlobal();
			$post=$this->getRequest()->getPost();
			$return_id = $post['return'];
			$sqlinfo ="SELECT * FROM `tb_return` WHERE return_id = $return_id LIMIT 1";
			$rowinfo=$db->getGlobalDbRow($sqlinfo);
			$sql = "SELECT rvi.qty_return,rvi.pro_id,rvi.price,rvi.sub_total
					,(SELECT rt.return_no FROM tb_return AS rt WHERE rt.return_id = rvi.return_id LIMIT 1) AS return_no
					,(SELECT rt.all_total FROM tb_return AS rt WHERE rt.return_id = rvi.return_id LIMIT 1) AS all_total
					,(SELECT pr.qty_perunit FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS qty_perunit
				      	,(SELECT pr.item_name FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS item_name
					,(SELECT pr.pro_id FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS pro_id
				      	,(SELECT `label` FROM tb_product AS pr WHERE pr.pro_id = rvi.pro_id LIMIT 1) AS label
				     	,(SELECT `measure_name` FROM `tb_measure` AS ms WHERE ms.id=(SELECT measure_id FROM tb_product WHERE pro_id=rvi.`pro_id`)) AS measure_name
				 FROM `tb_return_vendor_item` AS rvi WHERE rvi.return_id =".$return_id;
			$rows=$db->getGlobalDb($sql);
			$result = array('poinfo'=>$rowinfo,'item'=>$rows);
			echo Zend_Json::encode($result);
			exit();
		}
	
	}
	public function returnItemInAction(){
		$post = $this->getRequest()->getPost();
		if($post){
			$return = new purchase_Model_DbTable_DbReturnItem();
			$return ->getReturnItemIn($post);
			if(!empty($post["SaveNew"])){
				Application_Form_FrmMessage::message("Your items has been recieved");
				$this->_redirect("purchase/return/return-in");
			}
			else{
				$this->_redirect("purchase/return");
			}
				
		}
		$get_form = new Application_Form_FrmReturnItem();
		$frm_return = $get_form->returnItemInFrm(null);
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
	
		//for add vendor
		$formStockAdd = $formpopup->popupVendor(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form_vendor = $formStockAdd;
	
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	}
	
	public function updateReturnItemAction(){
		try{
		$session_stock = new Zend_Session_Namespace('stock');
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db_global = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT * FROM tb_return WHERE return_id =".$id;
		$rs=$db_global->getGlobalDbRow($sql);
		if($rs["is_active"]==0){
			Application_Form_FrmMessage::message("Can not update return becuase recieved already");
			Application_Form_FrmMessage::redirectUrl("/purchase/return");
		}else{
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				if($data['Save']){
					$update_return = new purchase_Model_DbTable_DbReturnItem();
					$rs = $update_return ->updateReturnItem($data);
					if($rs==-1){
						Application_Form_FrmMessage::Sucessfull("Your product location is not exist", "/purchase/return");
					}
//  					else 
//  						Application_Form_FrmMessage::Sucessfull("Update Sucessfull", "/purchase/return");
 				}
			}
	// 		$purchase = new purchase_Model_DbTable_DbPurchaseOrder();
	// 		$rows = $purchase->purchaseInfo($id);
	// 		$db = new Application_Model_DbTable_DbGlobal();
			
			$returnModel = new purchase_Model_DbTable_DbSQLReturnItem();
			$row_info= $returnModel->returnInfo($id);
			
			$get_form = new Application_Form_FrmReturnItem();
			$session_stock = new Zend_Session_Namespace('stock');
			$frm_return = $get_form->returnItemForm($row_info);
			Application_Model_Decorator::removeAllDecorator($frm_return);
			$this->view->form_return = $frm_return;
			
			//get qty of return item
			$getReturnItem = $returnModel->getReturnItem($id);
			$this->view->returnItemDetail = $getReturnItem;
			//print_r($getReturnItem);exit();
			
			//get return item		
			
			$getOption = new Application_Model_GlobalClass();
			$locationRows = $getOption->getLocationOption();
			$this->view->locationOption = $locationRows;
			//print_r($locationRows);exit();
			
			$itemRows = $getOption->getProductOption();
			$this->view->productOption = $itemRows;
			//print_r($itemRows);exit();
			
			$formControl = new Application_Form_FrmAction(null);
			$formViewControl = $formControl->AllAction(null);
			Application_Model_Decorator::removeAllDecorator($formViewControl);
			$this->view->control = $formViewControl;
					
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
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
		
	}
	public function updateReturnItemInAction(){
		try{
			$session_stock = new Zend_Session_Namespace('stock');
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				if($data['Save']){
					$update_return = new purchase_Model_DbTable_DbReturnItem();
					$update_return ->updateReturnItemIn($data);
					$this->_redirect("/purchase/return/return-in");
					Application_Form_FrmMessage::message("Update Success full");
				}
			}
			// 		$purchase = new purchase_Model_DbTable_DbPurchaseOrder();
			// 		$rows = $purchase->purchaseInfo($id);
			// 		$db = new Application_Model_DbTable_DbGlobal();
	
			$returnModel = new purchase_Model_DbTable_DbSQLReturnItem();
			$row_info= $returnModel->returnVendorInfoIn($id);
	
			$get_form = new Application_Form_FrmReturnItem();
			$session_stock = new Zend_Session_Namespace('stock');
			$frm_return = $get_form->returnItemInFrm($row_info);
			Application_Model_Decorator::removeAllDecorator($frm_return);
			$this->view->form_return = $frm_return;
	
			//get qty of return item
			$getReturnItem = $returnModel->getReturnItemIn($id);
			$this->view->returnItemDetail = $getReturnItem;
			//print_r($getReturnItem);exit();
	
			//get return item
	
			$getOption = new Application_Model_GlobalClass();
			$locationRows = $getOption->getLocationOption();
			$this->view->locationOption = $locationRows;
			//print_r($locationRows);exit();
	
			$itemRows = $getOption->getProductOption();
			$this->view->productOption = $itemRows;
			//print_r($itemRows);exit();
	
			$formControl = new Application_Form_FrmAction(null);
			$formViewControl = $formControl->AllAction(null);
			Application_Model_Decorator::removeAllDecorator($formViewControl);
			$this->view->control = $formViewControl;
	
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
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	public function detailReturnItemAction() {
		if($this->getRequest()->getParam('id')) {
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
			
			$returnModel = new purchase_Model_DbTable_DbSQLReturnItem();
			$info_out=$returnModel->returnInfo($id);
			if(!empty($info_out)){
				$this->view->return_info = $info_out;
			}
			else{
				$this->_redirect("/purchase/return/index");
			}
			
			//get qty of itme 
 			$getReturnItem = $returnModel->getReturnItem($id);
			$this->view->returnItemDetail = $getReturnItem;
		}
	}
	public function detailReturnIteminAction() {//detail return to stock in
		if($this->getRequest()->getParam('id')) {
			$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
				
			$returnModel = new purchase_Model_DbTable_DbSQLReturnItem();
			$info=$returnModel->returnVendorInfoIn($id);
			if(!empty($info)){
				$this->view->return_info = $info;
			}
			else{
				$this->_redirect("/purchase/return/return-in");
			}
			//get qty of itme
			$getReturnItem = $returnModel->getReturnItemIn($id);
			$this->view->returnItemDetail = $getReturnItem;
		}
	}
	
}