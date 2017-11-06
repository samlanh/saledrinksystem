<?php
class Product_AdjustController extends Zend_Controller_Action
{
	const REDIRECT_URL_ADD ='/product/adjust/add';
	const REDIRECT_URL_ADD_CLOSE ='/product/index/';
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	
    } 
   	//view transfer index
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
   	public function indexAction()
   	{
   		$data = $this->getRequest()->getPost();
   		$list = new Application_Form_Frmlist();
   		$db = new Product_Model_DbTable_DbAdjustStock();
		$date =new Zend_Date();
   		if($this->getRequest()->isPost()){   
    		$data = $this->getRequest()->getPost();
    	}else{
			$data = array(
    			'ad_search'	=>	'',
    			'start_date'	=>	$date->get('MM/d/Y'),
				'end_date'	=>	$date->get('MM/d/Y')
    		);
		}
   
   		$rows=$db->getAllAdjustStock($data);
   		$columns=array("ITEM_CODE","PRO_NAME","QTY_BEFORE","QTY_ADJUST","DFFER_QTY","MEASURE","LOCATION","BY_USER","DATE");
   		
   		$this->view->list=$list->getCheckList(0, $columns, $rows);
   		$frm = new Product_Form_FrmAdjust();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->filter();
   		
   	}
   	//26-8-13 add adjust stock //done 27-8-813
   	
   	//26-8-13 add adjust stock //done 27-8-813
    public function addAction()
    {   
    	if($this->getRequest()->isPost()){   
    		$post=$this->getRequest()->getPost();
    		$db_adjust= new Product_Model_DbTable_DbAdjustStock();
    		$db_result = $db_adjust->add($post);
    		if(isset($post["save_close"])){
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL_ADD_CLOSE);
    		}else{
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}
    		
    	}

    	//for add location
    	
    	$frm = new Product_Form_FrmAdjust();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->formFilter = $frm->add();
    	
	}
	public function priceAction(){
		$formFilter = new Product_Form_FrmItemPrice();
		$frmsearch=$formFilter->searchPrice(null);
		Application_Model_Decorator::removeAllDecorator($frmsearch);
		$this->view->formFilter = $frmsearch;
		
		$list = new Application_Form_Frmlist();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$request = $this->getRequest();
		$id = $request->getParam("id", NULL);
		
		$sql = "SELECT p.pro_id, p.item_name,pt.price_type_name,pr.price,p.is_avaliable AS public
		FROM tb_product As p,tb_price_type as pt,tb_product_price as pr
		WHERE pr.product_id = p.pro_id AND pt.type_id=pr.type_price ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			if($post['p_name'] !=''){
				$sql .= " AND (p.item_name LIKE '%".trim($post['p_name'])."%'";
				$sql .= " OR p.p_code LIKE '%".trim($post['p_name'])."%')";
			}
			if($post['type_id'] !=''){
				$sql .= " AND pt.type_id =".trim($post['type_id']);
			}
			if($post['p_price'] !=''){
				$sql .= " AND pr.price =".trim($post['p_price']);
			}
			if($post['status'] !=''){
				$sql .= " AND p.is_active =".trim($post['status']);
			}
		}
		//echo $sql;exit();
		$sql .= " GROUP BY p.pro_id,pt.type_id";
		//echo $sql;
		$rows=$db->getGlobalDb($sql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getpublic($rows, BASE_URL, true);
		
		$columns=array("ITEM_NAME_CAP","TYPE_PRICE","PRICE","ACTIVE");
		$link=array(
				'module'=>'product','controller'=>'adjust-stock','action'=>'update-item-price',
		);
		$urlEdit = BASE_URL ."/product/adjust-stock/update-item-price";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function typePriceAction(){
		$formFilter = new Product_Form_FrmItemPrice();
		$frmsearch=$formFilter->searchPriceType(null);
		Application_Model_Decorator::removeAllDecorator($frmsearch);
		$this->view->formFilter = $frmsearch;
	
		$list = new Application_Form_Frmlist();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$request = $this->getRequest();
		$id = $request->getParam("id", NULL);
	
		$sql = "SELECT id,name,pt.desc,status
		FROM tb_price_type AS pt
		WHERE name !='' ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			if($post['price_type'] !=''){
				$sql .= " AND name LIKE '%".trim($post['price_type'])."%'";
			}
			if($post['status'] !=''){
				$sql .= " AND public =".trim($post['status']);
			}
		}
// 		//echo $sql;exit();
// 		$sql .= " GROUP BY p.pro_id,pt.type_id";
		//echo $sql;
		$rows=$db->getGlobalDb($sql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getpublic($rows, BASE_URL, true);
	
		$columns=array("TYPE_PRICE","DESC_CAP","ACTIVE","DEACTIVE");
		$link=array(
				'module'=>'product','controller'=>'adjust-stock','action'=>'add-type-price',
		);
		$urlEdit = BASE_URL ."/product/adjust-stock/add-type-price";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	
	public function addTypePriceAction(){		
		$dbprice= new Product_Model_DbTable_DbPrice();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$dbprice->addPriceType($data);
			Application_Form_FrmMessage::message("Price type has been saved! ");
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$frm= new Product_Form_FrmItemPrice(null);
		if($id){
			$row=$dbprice->getTypePrice($id);	
			$frmpricetype =$frm->AddClassPrice($row);
			//Application_Form_FrmMessage::message("Price type has been saved! ");
			$action = BASE_URL . "/product/adjust-stock/update-type-price";
		}
		else{
			
			$frmpricetype =$frm->AddClassPrice(null);
			$action = BASE_URL . "/product/adjust-stock/add-type-price";
		}
		
		Application_Model_Decorator::removeAllDecorator($frmpricetype);
		$method = "post";
		$url_cancel = BASE_URL . "/product/adjust-stock/type-price";
		$frmgenerate = new Application_Form_FrmGlobal();
		$this->view->formVendor = $frmgenerate->getForm1($action,$method,$url_cancel,$frmpricetype,'TYPE_PRICE');
	}
	
	public function addItemPriceAction()
	{
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost())
		{
			try{
				$post=$this->getRequest()->getPost();
				$dbprice= new Product_Model_DbTable_DbPrice();
				$dbprice->setPriceItem($post);
				if($post['save']=="Save New")
				{
					Application_Form_FrmMessage::message("Product has been set price success!");
				}
				else{
					$this->_redirect("/product/adjust-stock/price");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message("You Set Prdocut Price Is failed!");
			}
			//$this->_redirect("product/index/index");
		}
		$frm = new Product_Form_FrmItemPrice();
		$add_price =$frm->AddItemPrice();
		Application_Model_Decorator::removeAllDecorator($add_price);
		$this->view->frm_addprice = $add_price;
	
		$getOption = new Application_Model_GlobalClass();
		$price_type = $getOption->getTypePriceOption();
		$this->view->price_option = $price_type;
	
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formprice = $formpopup->AddClassPrice(null);
		Application_Model_Decorator::removeAllDecorator($formprice);
		$this->view->frm_price = $formprice;
	}
	
	
	public function updateItemPriceAction()
	{
		$db = new Application_Model_DbTable_DbGlobal();
		$id = $this->getRequest()->getParam("id");
		$dbprice= new Product_Model_DbTable_DbPrice();
		
		if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			//print_r($post);exit();
			try{
				$post=$this->getRequest()->getPost();
				$dbprice= new Product_Model_DbTable_DbPrice();
				$dbprice->updateItemPrice($post);
 				$this->_redirect("/product/adjust-stock/price");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Product Price update failed !");
			}
		}
		$rows = $dbprice->getPriceByItem($id);
		$frm = new Product_Form_FrmItemPrice();
		$add_price =$frm->AddItemPrice($rows);
		Application_Model_Decorator::removeAllDecorator($add_price);
		$this->view->frm_addprice = $add_price;
		
		$this->view->pricetype_option = $rows;
	
		$getOption = new Application_Model_GlobalClass();
		$price_type = $getOption->getTypePriceOption();
		$this->view->price_option = $price_type;
	
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formprice = $formpopup->AddClassPrice(null);
		Application_Model_Decorator::removeAllDecorator($formprice);
		$this->view->frm_price = $formprice;
	}
	/// Ajax Section
	public function getProductAction(){
		if($this->getRequest()->isPost()) {
			$db = new Product_Model_DbTable_DbAdjustStock();
			$data = $this->getRequest()->getPost();
			$rs = $db->getProductById($data["id"]);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	//add class of price
	public function addNewPriceTypeAction(){
		if($this->getRequest()->isPost()) {
			$dbprice= new Product_Model_DbTable_DbPrice();
			$data = $this->getRequest()->getPost();
			$type_id = $dbprice->addPriceType($data);
			$rs = array("type_id"=>$type_id);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	public function updateTypePriceAction()//for update class of price name
	{
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost())
		{
			try{
				$post=$this->getRequest()->getPost();
				$dbprice= new Product_Model_DbTable_DbPrice();
				$dbprice->updatePricetype($post);
				$this->_redirect("product/adjust-stock/type-price");
			}catch (Exception $e){
	
			}
			
		}
	}
		
	// not yet done
	public function transferStockAction()
	{
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$transfer_stock = new Product_Model_DbTable_DbAdjustStock();
			$db_result = $transfer_stock->TransferStockTransaction($post);
			if($post["transfer"]=="Transfer New"){
				Application_Form_FrmMessage::Sucessfull("Transfer stock has been successed !", '/product/adjust-stock/transfer-stock');
			}
			else{
				$this->_redirect('/product/adjust-stock/index');
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		 
		$frm = new Product_Form_FrmTransfer();
		 $frm_transfer=$frm->transferItem(null);
		 Application_Model_Decorator::removeAllDecorator($frm_transfer);
		 $this->view->form_transfer = $frm_transfer;
		 
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$toLocationRows = $getOption->tolocationOption();
		$this->view->tolocationOption = $toLocationRows;
		
		$itemRows = $getOption->selectProductOption();
		$this->view->productOption = $itemRows;
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
		
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	}
	public function transferUpdateAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$transfer = new Product_Model_DbTable_DbUpdateTransfer();
		$exist=$transfer->transferExist($id);
		if($exist==""){
			//redirect if no transfer this id
			$this->_redirect("product/adjust-stock/index");
		}
		if($this->getRequest()->getPost()){
			$post = $this->getRequest()->getPost();
			$update = new Product_Model_DbTable_DbUpdateTransfer();
			$update->updateTransferStockTransaction($post);
			$this->_redirect("product/adjust-stock/index");
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$productinfo = new Product_Model_DbTable_DbProduct();
		$rows = $productinfo->getTransferInfo($id);

		$row_item = $productinfo->getTransferItem($id);
		$this->view->transfer_item = $row_item;
		
		$frm = new Product_Form_FrmTransfer();
		$frm_transfer=$frm->transferItem($rows);
		Application_Model_Decorator::removeAllDecorator($frm_transfer);
		$this->view->form_transfer = $frm_transfer;
			
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$toLocationRows = $getOption->tolocationOption();
		$this->view->tolocationOption = $toLocationRows;
		
		$itemRows = $getOption->selectProductOption();
		$this->view->productOption = $itemRows;
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
		
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
		
	}
	
	public function viewTransferAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$transfer = new Product_Model_DbTable_DbUpdateTransfer();
		$exist=$transfer->transferExist($id);
		$user = $this->GetuserInfoAction();
		if($exist==""){
			//redirect if no transfer this id
			$this->_redirect("product/adjust-stock/index");
		}
		else{
			$user_exist=$transfer->transferUserExist($id,$user["location_id"]);
			if($user_exist==""){
				//redirect if no transfer this id
				$this->_redirect("product/adjust-stock/index");
			}
			
		}
		if($this->getRequest()->getPost()){
// 			$post= $this->getRequest()->getPost();
// 			print_r($post);exit();
			Application_Form_FrmMessage::message("You Haven't Permission To Change !");
			Application_Form_FrmMessage::redirectUrl("/product/adjust-stock/index");
// 			$post = $this->getRequest()->getPost();
// 			$update = new Product_Model_DbTable_DbUpdateTransfer();
// 			$update->updateTransferStockTransaction($post);
// 			$this->_redirect("product/adjust-stock/index");
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$productinfo = new Product_Model_DbTable_DbProduct();
		$rows = $productinfo->getTransferInfo($id);
	
		$row_item = $productinfo->getTransferItem($id);
		$this->view->transfer_item = $row_item;
	
		$frm = new Product_Form_FrmTransfer();
		$frm_transfer=$frm->transferItem($rows);
		Application_Model_Decorator::removeAllDecorator($frm_transfer);
		$this->view->form_transfer = $frm_transfer;
			
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption();
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$toLocationRows = $getOption->tolocationOption();
		$this->view->tolocationOption = $toLocationRows;
	
		$itemRows = $getOption->getProductOption();
		$this->view->productOption = $itemRows;
	
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form = $formproduct;
	
		//for add location
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
	
	}
	
	// not yet done
	public function adjustPriceAction()
	{
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost())
		{	
			$post=$this->getRequest()->getPost();
			if($post['save']!=="")
			{
				$adjust_price = new Product_Model_DbTable_DbAdjustStock();
				$db_result = $adjust_price->adjustPricing($post);
			}
			$this->_redirect("product/index/index");
		}	
		$session_stock=new Zend_Session_Namespace('stock');
		///view on select location form table
		$getOption = new Application_Model_GlobalClass();
		$locationRows = $getOption->getLocationOption($session_stock->stockID);
		$this->view->locationOption = $locationRows;
		///view on select location form table
		$itemRows = $getOption->getProductOption($session_stock->stockID);
		$this->view->productOption = $itemRows;
		
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formproduct = $formpopup->popuProduct(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formproduct);
		$this->view->form_product = $formproduct;
			
	}
	public function getCurrentQuantityAction(){
		
		$post=$this->getRequest()->getPost();
 		$get_item = new Product_Model_DbTable_DbAdjustStock();
 		$result = $get_item->getCurrentItem($post);    
 		 if(!$result){
 		 	$result = array('qty'=>0);
 		 }		
		echo Zend_Json::encode($result);
		exit();
	}
}