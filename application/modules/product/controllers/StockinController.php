<?php

class Product_StockinController extends Zend_Controller_Action
{

public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	$formFilter = new Product_Form_FrmProductFilter();
    	$this->view->formFilter = $formFilter;
    	//$cate = $formFilter->listCategory();
    	//$this->view->cate = $cate;
        $list = new Application_Form_Frmlist();
        
        $user_info = new Application_Model_DbTable_DbGetUserInfo();
        $result = $user_info->getUserInfo();
    	
        $db = new Application_Model_DbTable_DbGlobal();
//     	INNER JOIN tb_sublocation AS lo ON lo.LocationId = pl.LocationId WHERE 1 ";
		$productSql = "SELECT 
				p.pro_id 
				,p.item_name
				,p.item_code
				,(SELECT g.Name FROM tb_category AS g WHERE g.CategoryId = (SELECT cate_id FROM tb_product WHERE pro_id = pl.`pro_id` LIMIT 1)) AS Cate_name
				,(SELECT b.Name FROM tb_branch AS b WHERE b.branch_id = (SELECT brand_id FROM tb_product WHERE pro_id = pl.`pro_id` LIMIT 1 )) AS Branch
				,(SELECT lo.Name FROM tb_sublocation AS lo WHERE lo.LocationId = pl.LocationId LIMIT 1) AS LocationName
				,pl.qty
			FROM tb_prolocation AS pl,tb_product AS p WHERE pl.`pro_id`=p.pro_id ";
    	
    	$str_condition = " AND pl.LocationId" ;
    	$productSql .= $db->getAccessPermission($result["level"], $str_condition, $result["location_id"]);
    	
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$productName = $this->getRequest()->getParam('s_name');
    	    if($post['LocationId'] !=='' AND $post['LocationId'] !=0){
				$productSql .= " AND pl.LocationId = ".trim($post['LocationId']);
    	    }
    	    if($post['p_name'] !=''){
    	    	$productSql .= " AND p.item_name LIKE '%".trim($post['p_name'])."%'";
    	    	$productSql .= " OR p.item_code LIKE '%".trim($post['p_name'])."%'";
    	    }
			if($post['category_id']!=='' AND $post['category_id']!=0){
				//echo $post['category_id']; exit();
					$productSql .= " AND p.cate_id =".trim($post['category_id']);}
			
			if($post['branch_id']!=='' AND $post['branch_id']!=0){
						$productSql .= " AND p.brand_id =".trim($post['branch_id']);}
    	}//echo $productSql;exit();
    	$productSql.= " ORDER BY p.item_name,p.cate_id DESC";
    	$rows=$db->getGlobalDb($productSql);
    	$link=array(
    			'module'=>'product','controller'=>'index','action'=>'update',
    	);
    	$columns=array("ITEM_NAME_CAP","item_code","CATEGORY_CAP","BRAND_CAP","LOCATION_NAME_CAP","QTY_HAND_CAP");
    	$urlEdit = BASE_URL . "/distributor/index/update";
    	    	
		$urlEdit = BASE_URL . "/product/index/update";
    	$this->view->list=$list->getCheckList(1, $columns, $rows, array('item_name'=>$link), $urlEdit);
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	} 
	function add(){
		
	}
}