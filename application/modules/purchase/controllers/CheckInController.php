<?php
class purchase_checkInController extends Zend_Controller_Action
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
    public function getPurchaseidAction(){
    	if($this->getRequest()->isPost()){
    		$db= new Application_Model_DbTable_DbGlobal();
    		$post=$this->getRequest()->getPost();
    		$invoice = $post['invoice_id'];
    		$sqlinfo ="SELECT * FROM `tb_purchase_order` WHERE order_id = $invoice LIMIT 1";
    		$rowinfo=$db->getGlobalDbRow($sqlinfo);
    		$sql = "SELECT pui.qty_order,pui.pro_id,pui.price,pui.sub_total
					,(SELECT pur.order FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as order_no
					,(SELECT pur.all_total FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as all_total
					,(SELECT pr.qty_perunit FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS qty_perunit
      				,(SELECT pr.item_name FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS item_name
					,(SELECT pr.pro_id FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS pro_id
      				,(SELECT `label` FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS label
     				,(SELECT `measure_name` FROM `tb_measure` AS ms WHERE ms.id=(SELECT measure_id FROM tb_product WHERE pro_id=pui.`pro_id`)) AS measure_name
      			FROM `tb_purchase_order_item` AS pui WHERE pui.order_id = ".$invoice;
    		$rows=$db->getGlobalDb($sql);
    		$result = array('poinfo'=>$rowinfo,'item'=>$rows);
    		echo Zend_Json::encode($result);
    		exit();
    	}
    
    }
	public function indexAction()
	{
		try{
	  	$db = new Application_Model_DbTable_DbGlobal();
	  	
	  	if($this->getRequest()->isPost()) {
	  		$data = $this->getRequest()->getPost();
	  		//print_r($invoice);exit();
	  		$recieve_order = new purchase_Model_DbTable_DbCheckInProduct();
	  		
	  		
	  		if(isset($data['SaveNew'])){
	  			try {
		  			$recieve_order->RecievedPurchaseOrder($data);
		  			Application_Form_FrmMessage::message("Purchase has been received!");
		  			Application_Form_FrmMessage::redirectUrl("/purchase/index/check-in");
	  			}catch (Exception $e){
	  				 $e->getMessage();
	  			}
	  			
	  		}
	  		else{
	  			$recieve_order -> RecievedPurchaseOrder($data);
	  			$this->_redirect("/purchase/index/index");
	  		}
	  	}
	  
	  	$user = $this->GetuserInfoAction();
	  	if($user["level"]!=1 AND $user["level"]!=2){
	  		$this->_redirect("purchase/index/index");
	  	}
	  	
	  	$db = new Application_Model_DbTable_DbGlobal();
	  	$frm_purchase = new Application_Form_FrmCheckIn();
	  	$form_add_purchase = $frm_purchase->productOrder();
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