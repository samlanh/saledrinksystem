<?php
class Product_StudentController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	$formFilter = new Product_Form_FrmProductFilter();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        $list = new Application_Form_Frmlist();    	
        $db = new Application_Model_DbTable_DbGlobal();
        $user = $this->GetuserInfoAction();
        $_status = " p.is_active=1 ";
        if($user["level"] == 1 OR $user["level"] == 2){
        	$productSql = "SELECT p.pro_id, p.item_name,p.item_code, cg.Name As cate_name,b.Name, p.qty_onhand,p.qty_onorder,p.qty_onsold, p.is_avaliable
        	FROM tb_product as p
        	INNER JOIN tb_branch as b ON b.branch_id = p.brand_id
        	INNER JOIN tb_category as cg ON cg.CategoryId = p.cate_id
        	";
        }
        else{
        	$productSql = " SELECT p.pro_id, p.item_name, p.item_code, cg.Name As cate_name,b.Name, pl.qty,pl.qty_onorder,pl.qty_onsold, p.is_avaliable
							FROM tb_product AS p
							INNER JOIN tb_category AS cg ON cg.CategoryId = p.cate_id
        					INNER JOIN tb_branch as b ON b.branch_id = p.brand_id ";
        }
    	$str_condition = "INNER JOIN tb_prolocation  As pl ON p.pro_id = pl.pro_id WHERE LocationId " ; 
    	$productSql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);		
    	
	    if($this->getRequest()->isPost()){
	    		$post = $this->getRequest()->getPost();
	    	    if($post['p_code'] !=''){
					  $productSql .= " AND p.item_code LIKE '%".trim($post['item_code'])."%'";
	    	    }
	    	    if($post['p_name'] !=''){
	    	    	$productSql .= " AND p.item_name LIKE '%".trim($post['p_name'])."%'";
	    	    }
				if($post['category_id'] !='' AND $post['category_id'] !=0){
						$productSql .= " AND cg.CategoryId =".trim($post['category_id']);
				}
	   			if($post['branch_id'] !='' AND $post['branch_id'] !=0){
						$productSql .= " AND b.branch_id = ".trim($post['branch_id']);
				}
				if($post['status'] !=""){
					$productSql .= " AND p.is_avaliable=1 = ".trim($post['status']);
				}
    	}
    	$this->view->level = $user["level"];
    	
    	$productSql.=" ORDER BY p.item_name,b.branch_id ";
    	//echo $productSql;
    	$rows=$db->getGlobalDb($productSql);
    	
        $glClass = new Application_Model_GlobalClass();
//         $rows = $glClass->getStockType($rows, BASE_URL, true);

        $rows = $glClass->getImgActive($rows, BASE_URL, true);
        
    	$columns=array("ITEM_NAME_CAP","ITEM_CODE","CATEGORY_CAP","BRAND_CAP","QTY_HAND_CAP","QTY_ON_ORDER","QTY_ON_SOLD","STATUS_CAP");
    	$link=array(
    		'module'=>'product','controller'=>'index','action'=>'product-detail',
    	);
    	
    	//print_r($rs);exit();
		$urlEdit = BASE_URL ."/product/index/update";
    	$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
	}
	public function addAction()
	{
			if($this->getRequest()->isPost()){ 
				try{
					$add_product = new  Product_Model_DbTable_DbAddProduct();
					$post = $this->getRequest()->getPost();
					$add_product ->addProduct($post);//
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
					if(@$post['Save']!="")
					{
						Application_Form_FrmMessage::redirectUrl('/product/index/index');
					}
				  }catch (Exception $e){
				  	
				  	
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
			$formProduct = new Application_Form_FrmProduct(null);
			$formStockAdd = $formProduct->AddProductForm(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
			//control
			$formAddProdcut = new Application_Form_FrmAction(null);
			$FrmAdd = $formAddProdcut->AllAction(null);
			Application_Model_Decorator::removeAllDecorator($FrmAdd);
			$this->view->control = $FrmAdd;
			
			///view on select form
			$items = new Application_Model_GlobalClass();
			$locationRows = $items->getLocationOption();
			$this->view->locations = $locationRows;
			
			//for add location
			$formpopup = new Application_Form_FrmPopup(null);
			$formAdd = $formpopup->popuLocation(null);
			Application_Model_Decorator::removeAllDecorator($formAdd);
			$this->view->form_addstock = $formAdd;
			
			$form_branch = $formpopup->popupBranch(null);
			Application_Model_Decorator::removeAllDecorator($form_branch);
			$this->view->form_brand = $form_branch;
			
			$form_measure = $formpopup->popuMeasure(null);
			Application_Model_Decorator::removeAllDecorator($form_measure);
			$this->view->form_measure = $form_measure;
			
			$form_category = $formpopup->popupCategory(null);
			Application_Model_Decorator::removeAllDecorator($form_category);
			$this->view->form_category = $form_category;
			
			$db = new Application_Model_DbTable_DbGlobal();
			$row=$db->getSettingById(12);
			$this->view->setpricebybranch =$row['key_value'];
	}
	public function updateAction()
	{
		if($this->getRequest()->isPost())
		{
			try{
				$add_product = new  Product_Model_DbTable_DbAddProduct();
				$post = $this->getRequest()->getPost();
				if(@$post['Update'] OR @$post['Deactive'] OR @$post['Active'])
				{
					$rs=$add_product ->updateProduct($post);
					if(!empty($rs))$this->_redirect('/product/index/index');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::messageError("APPLICATON_FAILED", $e->getMessage());
			}
		}	
		//$session_stock=new Zend_Session_Namespace('stock');
		//if user type in wrong url
		$user = $this->GetuserInfoAction();
		if($user["level"]==4){
			//echo "ture codition";
			//$this->_redirect("product/index/index");
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$gb = 	new Application_Model_DbTable_DbGlobal();
		if($user["level"]!=1 AND $user["level"]!=2){
			$exist = $gb->productLocation($id, $user["location_id"]);
			if($exist==""){
				//$this->_redirect("product/index/index");
			}
		}
		else{
			$pro_exist = $gb->myProductExist($id);
				if(!($pro_exist)){
					//$this->_redirect("product/index/index");
				}
		}
		//get product info detail 23/8/13
		$productinfo = new Product_Model_DbTable_DbProduct();
		$rows = $productinfo->getProductInfo($id);
		print_r($rows);
		
		$formStock = new Application_Form_FrmProduct();
		$formStockEdit = $formStock->AddProductForm($rows);
		Application_Model_Decorator::removeAllDecorator($formStockEdit);// omit default zend html tag
		$this->view->form = $formStockEdit;
		if($rows['photo']==""){
			$rows['photo']="no-img.gif";
		}
		$this->view->photo = $rows['photo'];
		$this->view->status = $rows['is_avaliable'];
	
		//get product move history 23/8/13
		$rows= $productinfo->moveproduct($id);
		$list = new Application_Form_Frmlist();
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getTransactionType($rows, BASE_URL, true);	
		$columns=array("TRANSACTION_TYPE_CAP","DATE_CAP","LOCATION_NAME_CAP",
						"QTY_CAP","QTY_BEFORE_CAP","QTY_AFTER_CAP","REMARK_CAP","BY_USER_CAP");
		$link=array('module'=>'product','controller'=>'index','action'=>'update',);
		$this->view->list_history=$list->getCheckList(1, $columns, $rows,
									array('Name'=>$link,'Name'=>$link),"","move","left",false,"move_history");
		
		//view sale order history
		$row_sale_history = $productinfo->getSaleHistory($id);
		$glClass = new Application_Model_GlobalClass();
		$row_sale_history = $glClass->getTypeHistory($row_sale_history, BASE_URL, true);
		$row_sale_history = $glClass->getStatusType($row_sale_history, BASE_URL, true);
		$columns1=array("TYPE_CAP","ORDER_ADD_CAP","CUSTOMER_CAP","ORDER_DATE_CAP",
						"ORDER_STATUS_CAP","TOTAL_PRICE_CAP","QTY_CAP","UNIT_PRICE_CAP","SUB_TOTAL_CAP");
		$link1=array(
				'module'=>'product','controller'=>'index','action'=>'update',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list_order_history=$list->getCheckList(1, $columns1, $row_sale_history, 
										array('item_name'=>$link1,'Name'=>$link1),"","items","left",false,"order_history");		
		
		$row_purchase_history = $productinfo->getPurchaseHistory($id);
		$glClass = new Application_Model_GlobalClass();
		$row_purchase_history = $glClass->getTypeHistory($row_purchase_history, BASE_URL, true);
		$row_purchase_history = $glClass->getStatusType($row_purchase_history, BASE_URL, true);
		$columns1=array("TYPE_CAP","ORDER_ADD_CAP","VENDOR_CAP","ORDER_DATE_CAP",
				"ORDER_STATUS_CAP","TOTAL_PRICE_CAP","QTY_CAP","UNIT_PRICE_CAP","SUB_TOTAL_CAP");
		$link_pur=array(
				'module'=>'product','controller'=>'index','action'=>'update',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list_purchase_history=$list->getCheckList(1, $columns1, $row_purchase_history,
				array('item_name'=>$link_pur,'Name'=>$link_pur),"","items","left",false,"purchase_history");
		
		//for product qty in stock 22/8/13
		if($user["level"]==1 OR $user["level"]==2){
			$rowproduct = $productinfo->getProductStock($id);
			$this->view->pro_qty = $rowproduct;
		}
// 		$rowproduct = $productinfo->getProductStock($id);
// 		$this->view->pro_qty = $rowproduct;
		
		//get controll button
		$formProduct1 = new Application_Form_FrmAction(null);
		$formStockAdd1 = $formProduct1->AllAction(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd1);
		$this->view->Event = $formStockAdd1;
		
		//get item of this lost
		$orderModel= new Product_Model_DbTable_DbProduct();	
		$orderDetail = $orderModel->getOrderItemDetailByID($id);
		$this->view->rowsOrder = $orderDetail;
		
		///view on select location form
		$items = new Application_Model_GlobalClass();
		//$itemRows = $items->getLocationSelected($session_stock->stockID);
		$itemRows = $items->getLocationOption($id);
		$this->view->itemsOption = $itemRows;	
		
		//for add location
		$formpopup = new Application_Form_FrmPopup(null);
		$formAdd = $formpopup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->form_addstock = $formAdd;
			
		$form_measure = $formpopup->popuMeasure(null);
		Application_Model_Decorator::removeAllDecorator($form_measure);
		$this->view->form_measure = $form_measure;
		
		$form_branch = $formpopup->popupBranch(null);
		Application_Model_Decorator::removeAllDecorator($form_branch);
		$this->view->form_brand = $form_branch;
			
		$form_category = $formpopup->popupCategory(null);
		Application_Model_Decorator::removeAllDecorator($form_category);
		$this->view->form_category = $form_category;
	}
	//view category 27-8-2013
	public function categoryAction()
	{
		$formFilter = new Product_Form_FrmProductFilter();
		$this->view->formFilter = $formFilter;
		$list = new Application_Form_Frmlist();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$request = $this->getRequest();
		$id = $request->getParam("id", NULL);
	
		$productSql = "SELECT c.CategoryId, c.Name, c.IsActive
		FROM tb_category as c WHERE Name!='' ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			$productName = $this->getRequest()->getParam('id',Null);
			if($post['g_name'] !=''){
				$productSql .= " AND c.Name LIKE '%".trim($post['g_name'])."%'";
			}
			if($post['category_id'] !=''){
				$productSql .= " AND c.CategoryId = ".trim($post['category_id']);
			}
			
		}
		$productSql .= " ORDER BY c.CategoryId DESC";
		$rows=$db->getGlobalDb($productSql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getActive($rows, BASE_URL, true);
	
		$columns=array("CATEGORY_NAME_CAP","STATUS_CAP");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'add-category',
		);
		$urlEdit = BASE_URL ."/product/index/add-category";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	//add category 8/22/13
	public function addCategoryAction(){
		$category = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data); exit();
			$addcategory = $category->addCategory($data);
			$this->_redirect('/product/index/category');
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($id){
			//view update if get id
			$rows=$category->getCategory($id);
			$frmcategory = new Application_Form_FrmInclude($rows);
			$frmcate =$frmcategory->category($rows);
			$action = BASE_URL . "/product/index/update-category";
		}
		else{
				
			$frmcategory= new Application_Form_FrmInclude(null);
			$frmcate =$frmcategory->category(null);
			$action = BASE_URL . "/product/index/add-category";
		}
	
		Application_Model_Decorator::removeAllDecorator($frmcate);
		$method = "post";
		$url_cancel = BASE_URL . "/product/index/category";
		$frm = new Application_Form_FrmGlobal();
		$this->view->formVendor = $frm->getForm1($action,$method,$url_cancel,$frmcate,'CATEGORY');
	}
	
	public function updateCategoryAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$category = array(
					"parent_id" => $data['ParentCategory'],
					"Name"		=> $data['CategoryName'],
					"IsActive"	=> $data['status'],
					"Timestamp"	=> new Zend_Date()
			);
			$db->updateRecord($category, $data['id'], "CategoryId", "tb_category");
		}
		$this->_redirect("product/Index/category");
	}
	public function brandAction()
	{
		$formFilter = new Product_Form_FrmProductFilter();
		$this->view->formFilter = $formFilter;
		$list = new Application_Form_Frmlist();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$request = $this->getRequest();
		$id = $request->getParam("id", NULL);
	
		$productSql = "SELECT branch_id ,Name,IsActive
		FROM tb_branch WHERE Name!='' ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			$productName = $this->getRequest()->getParam('id',Null);
			if($post['g_name'] !=''){
				$productSql .= " AND Name LIKE '%".trim($post['g_name'])."%'";
			}
			if($post['branch_id'] !='' AND $post['branch_id'] !=0){
				$productSql .= " AND branch_id = ".trim($post['branch_id']);
			}
				
		}
		$productSql .= " ORDER BY Name";
		$rows=$db->getGlobalDb($productSql);
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getActive($rows, BASE_URL, true);
	
		$columns=array("BRAND_CAP","STATUS_CAP");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'add-brand',
		);
		$urlEdit = BASE_URL ."/product/index/add-brand";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function updateBrandAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$_arr = array(
					"parent_id" => $data['Parentbrand'],
					"Name"		=> $data['brandName'],
					"IsActive"	=> $data['status'],
					"Timestamp"	=> new Zend_Date()
			);
			$db->updateRecord($_arr, $data['id'], "branch_id", "tb_branch");
		}
		$this->_redirect("product/Index/brand");
	}
	public function addBrandAction(){
		$category = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			$addcategory = $category->addBrand($data);
			Application_Form_FrmMessage::message("Brand Name Has Been Saved !");
			Application_Form_FrmMessage::redirectUrl('/product/index/brand');
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if($id){
			//view update if get id
			$rows=$category->getBrandName($id);
			$frmcategory = new Application_Form_FrmInclude($rows);
			$frmcate =$frmcategory->addBrand($rows);
			$action = BASE_URL . "/product/index/update-brand";
		}
		else{
	
			$frmcategory= new Application_Form_FrmInclude(null);
			$frmcate =$frmcategory->addBrand(null);
			$action = BASE_URL . "/product/index/add-brand";
		}
	
		Application_Model_Decorator::removeAllDecorator($frmcate);
		$method = "post";
		$url_cancel = BASE_URL . "/product/index/brand";
		$frm = new Application_Form_FrmGlobal();
		$this->view->formVendor = $frm->getForm1($action,$method,$url_cancel,$frmcate,'MENU_PRODUCT_INDEX_ADD_BRAND');
	}
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
					Application_Form_FrmMessage::message("Product Is Exist !Please Rename again");
				}
				else{
					//echo "<span style='font-weight: bold;'>$username</span> is available!";						
				}
			}
		else{
			echo "";
		}
			exit();
	}

	public function productDetailAction(){
		if($this->getRequest()->getParam('id')) {
				$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
				//if user type wrong url
				$user = $this->GetuserInfoAction();
				$gb = 	new Application_Model_DbTable_DbGlobal();
				if($user["level"]!=1 AND $user["level"]!=2){
					$exist = $gb->productLocation($id, $user["location_id"]);
					if($exist==""){
						$this->_redirect("product/index/index");
					}
				}
				else{
					$pro_exist = $gb->myProductExist($id);
					if(!($pro_exist)){
						$this->_redirect("product/index/index");
					}
				}
							
				$session_stock=new Zend_Session_Namespace('stock');
				$productinfo = new Product_Model_DbTable_DbProduct();
				//get product info detail
				$getpro_info_rows = $productinfo->getProductInfoDetail($id);
				if($getpro_info_rows['photo']==""){
					$getpro_info_rows['photo']="no-img.gif";
				}
				$this->view->photo=$getpro_info_rows['photo'];
				$this->view->form = $getpro_info_rows;
							
				//for view product loaction 22/8/13			
				$orderDetail = $productinfo->getOrderItemVeiw($id);			
				$this->view->lostItemDetail = $orderDetail;	
				
				//for product in stock 22/8/13		
				if($user["level"]==1 OR $user["level"]==2){	
					$rowproduct = $productinfo->getProductStock($id);
					$this->view->pro_qty = $rowproduct;	
				}	
	
				//get product move history 23/8/13
				$rows= $productinfo->moveproduct($id);
				$list = new Application_Form_Frmlist();
				$glClass = new Application_Model_GlobalClass();
				$rows = $glClass->getTransactionType($rows, BASE_URL, true);	
				$columns=array("TRANSACTION_TYPE_CAP","DATE_CAP","LOCATION_NAME_CAP",
								"QTY_CAP","QTY_BEFORE_CAP","QTY_AFTER_CAP","REMARK_CAP","BY_USER_CAP");
				$link=array('module'=>'product','controller'=>'index','action'=>'update',);
				$this->view->list_history=$list->getCheckList(1, $columns, $rows);
				
				//view sale order history
				$row_sale_history = $productinfo->getSaleHistory($id);
				$glClass = new Application_Model_GlobalClass();
				$row_sale_history = $glClass->getTypeHistory($row_sale_history, BASE_URL, true);
				$row_sale_history = $glClass->getStatusType($row_sale_history, BASE_URL, true);
				$columns1=array("TYPE_CAP","ORDER_ADD_CAP","CUSTOMER_CAP","ORDER_DATE_CAP",
						"ORDER_STATUS_CAP","TOTAL_PRICE_CAP","QTY_CAP","UNIT_PRICE_CAP","SUB_TOTAL_CAP");
				$link1=array(
						'module'=>'product','controller'=>'index','action'=>'update',
				);
				$list = new Application_Form_Frmlist();
				$this->view->list_order_history=$list->getCheckList(1, $columns1, $row_sale_history, 
												array('item_name'=>$link1,'Name'=>$link1),"","items","left",false,"move_history");	
				
				//for purchase history
				
				$row_purchase_history = $productinfo->getPurchaseHistory($id);
				$glClass = new Application_Model_GlobalClass();
				$row_purchase_history = $glClass->getTypeHistory($row_purchase_history, BASE_URL, true);
				$row_purchase_history = $glClass->getStatusType($row_purchase_history, BASE_URL, true);
				$columns1=array("TYPE_CAP","ORDER_ADD_CAP","VENDOR_CAP","ORDER_DATE_CAP",
						"ORDER_STATUS_CAP","TOTAL_PRICE_CAP","QTY_CAP","UNIT_PRICE_CAP","SUB_TOTAL_CAP");
				$link_pur=array(
						'module'=>'product','controller'=>'index','action'=>'update',
				);
				$list = new Application_Form_Frmlist();
				$this->view->list_purchase_history=$list->getCheckList(1, $columns1, $row_purchase_history,
						array('item_name'=>$link_pur,'Name'=>$link_pur),"","items","left",false,"purchase_history");
		}
	}
	/***************************************************Sub Branch	****************************************** * 
	 * view sub branch 
	 * add sub branch
	 * update sub branch 
	 * */
	//for index-sublocation
	public function indexLocationAction()
	{
		$db = new Application_Model_DbTable_DbGlobal();
		$formFilter = new Product_Form_FrmProductFilter();
		$frmsearch = $formFilter->searchLocation();
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$sql="select loc.LocationId, loc.Name, loc.contact, loc.phone,username, loc.status,remark 
		from tb_sublocation AS loc
		INNER JOIN rsv_acl_user as u ON
		u.user_id=loc.user_id
		WHERE Name!='' ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			if($post['location_name'] !=''){
				$sql .= " AND Name LIKE '%".$post['location_name']."%'";
			}
			if($post['contact_name'] !=''){
				$sql .= " AND contact LIKE '%".$post['contact_name']."%'";
			}
			if($post['address_name'] !=''){
				$sql .= " AND stock_add LIKE '%".$post['address_name']."%'";
			}
			if($post['phone'] !=''){
				$sql .= " AND phone LIKE '%".$post['phone']."%'";
			}
		}
		$sql.=" ORDER BY loc.LocationId DESC";
		$rows=$db->getGlobalDb($sql);		
		$columns=array("LOCATION_NAME","CON_NAME","PHONE_NUM","BY_USER","STATUS","REMARK");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'detail-stock',
		);
		$urlEdit = BASE_URL . "/product/index/update-stock";
		$glClass = new Application_Model_GlobalClass();
		$rows = $glClass->getImgStatus($rows, BASE_URL, true);
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addStockAction() {
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			if($data['submit_add_close']){
				$Model = new Product_Model_DbTable_DbAddLocation();
				$Model->saveSubStock($data);
				$this->_redirect('/product/index/index-location');
			}
			else{
				$this->_redirect('/product/index/index-location');
			}
			
		}
		$form = new Product_Form_FrmSubStock(null);
		$formAdd = $form->orderSubstockForm(null, $session_stock->stockID);
		Application_Model_Decorator::removeAllDecorator($formAdd);
		$this->view->formstock=$formAdd;
	}
	
	public function updateStockAction() {
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Application_Model_DbTable_DbGlobal();
		
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			if($data['submit_add_close']){
				$Model = new Product_Model_DbTable_DbAddLocation();
				$Model->updateSubStock($data);
				$this->_redirect('/product/index/index-location');				
			}
			else{
				$this->_redirect('/product/index/index-location');
			}
			
		}
		// show form with value
		$stockSql = "SELECT * FROM tb_sublocation WHERE LocationId=".$id;
		$row = $db->getGlobalDbRow($stockSql);
		$form = new Product_Form_FrmSubStock(null);
		$formlocation = $form->orderSubstockForm($row);
		Application_Model_Decorator::removeAllDecorator($formlocation);// omit default zend html tag
		$this->view->formstock =$formlocation;
	}
	/*add new product 
	 * 	 * 
	 * */
	public function addNewAction(){
		$post=$this->getRequest()->getPost();
		$add_new_product = new Product_Model_DbTable_DbAddProduct();
		$pid = $add_new_product->addNewItem($post);
		$result = array("pid"=>$pid);
		echo Zend_Json::encode($result);
		exit();
	}
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	public function addNewBrandAction(){
		$post=$this->getRequest()->getPost();
		$add_new_brand = new Product_Model_DbTable_DbAddLocation();
		$cate_id = $add_new_brand->addBrand($post);
		$result = array('branch_id'=>$cate_id);
		if(!$result){
			$result = array('branch_id'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	public function addNewCategoryAction(){
		$post=$this->getRequest()->getPost();
		$add_new_cate = new Product_Model_DbTable_DbAddLocation();
		$cate_id = $add_new_cate->addCategory($post);
		$result = array('cate_id'=>$cate_id);
		if(!$result){
			$result = array('cate_id'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
	public function checkCodeAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$item_code = $_data["pcode"];
			$db_table = new Product_Model_DbTable_DbAddLocation();
			$items_code = $db_table->getCodeItem($item_code);
			echo Zend_Json::encode($items_code);
			exit();
		}
	}
	public function productAlertAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost())
		{
			try{
				$post=$this->getRequest()->getPost();
				$dbprice= new Product_Model_DbTable_DbPrice();
				$dbprice->addMessageAlertItem($post);
				if($post['add_new']=="Save New")
				{
					Application_Form_FrmMessage::message("Product has been set price success!");
				}
				else{
					$this->_redirect("/product/index/itemalert");
				}
			}catch (Exception $e){
	
			}
			//$this->_redirect("product/index/index");
		}
			
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->items = $itemRows;
	
		$getOption = new Application_Model_GlobalClass();
		$price_type = $getOption->getTypePriceOption();
		$this->view->price_option = $price_type;
	
		//for add product;
		$formpopup = new Application_Form_FrmPopup(null);
		$formprice = $formpopup->AddClassPrice(null);
		Application_Model_Decorator::removeAllDecorator($formprice);
		$this->view->frm_price = $formprice;
	}
	public function itemalertAction(){
		$formFilter = new Product_Form_FrmProductFilter();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	
		$list = new Application_Form_Frmlist();
			
		$db = new Application_Model_DbTable_DbGlobal();
		$request = $this->getRequest();
		$id = $request->getParam("id", NULL);
	
		$sql = " SELECT p.pro_id, p.item_name, p.item_code,pm.min_qty,pm.message
		FROM tb_product AS p,tb_qty_setting AS pm
		WHERE p.pro_id = pm.pro_id ";
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			if($post['pro_id'] !='' AND $post['pro_id'] !=0){
				$sql .= " AND p.pro_id =".trim($post['pro_id']);
			}
			if($post['category_id'] !='' AND $post['category_id'] !=0){
				$sql .= " AND p.cate_id = ".trim($post['category_id']);
			}
		}
		$sql .= " ORDER BY p.pro_id";
		$rows=$db->getGlobalDb($sql);
		// 		$glClass = new Application_Model_GlobalClass();
		// 		$rows = $glClass->getpublic($rows, BASE_URL, true);
	
		$columns=array("ITEM_NAME_CAP","item_code","MIN_QTY_ALERT_CAP","MSM_ALERT_CAP");
		$link=array(
				'module'=>'product','controller'=>'index','action'=>'update-itemalert',
		);
		$urlEdit = BASE_URL ."/product/index/update-itemalert";
		$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link,'Name'=>$link), $urlEdit);
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function updateItemalertAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		$id = $this->getRequest()->getParam("id");
		$dbprice= new Product_Model_DbTable_DbPrice();
	
		if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			try{
				$post=$this->getRequest()->getPost();
				$post["id"]=$id;
				$dbprice= new Product_Model_DbTable_DbPrice();
				$dbprice->updateAlertItem($post);
				$this->_redirect("/product/index/itemalert");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("update alert message update failed !");
			}
		}
		$items = new Application_Model_GlobalClass();
		$itemRows = $items->getProductOption();
		$this->view->items = $itemRows;
		$rows = $dbprice->getAlertbyItem($id);
		$this->view->rowalert = $rows;
	}
	public function addmeasureAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$_db = new Product_Model_DbTable_DbAddLocation();
			$measure_id = $_db->addMeasure($post);
			$result = array('measure_id'=>$measure_id);
			if(!$result){
				$result = array('measure_id'=>1);
			}
			echo Zend_Json::encode($result);
			exit();
		}
	}
	function testAction(){
		
	}
		
	
}

