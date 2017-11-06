<?php 
class Product_Form_FrmTransfer extends Zend_Form
{
	public function init()
    {

	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	public function add($data=null) {
		$db=new Product_Model_DbTable_DbTransfer();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$rs_loc = $db->getLocation(2);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		$tran_num->setValue($db->getTransferNo());
    	
    	$date =new Zend_Date();
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date->get('MM/dd/YYYY'));
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
		
		$rs_from_loc = $db_global -> getAllLocation();
		//print_r($rs_from_loc);
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		
    	
    	$opt = array(''=>$tr->translate("SELECT BRANCH"));
    	$to_loc = new Zend_Form_Element_Select("to_loc");
    	$to_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$to_loc->setMultiOptions($opt);
    	
    	$pro_name =new Zend_Form_Element_Select("pro_name");
    	$pro_name->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'addNew();'
    	));
    	$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product = $db_stock->getProductName();
    	if(!empty($row_product)){
    		foreach ($row_product as $rs){
    			$opt[$rs["id"]] = $rs["item_name"]." ".$rs["model"]." ".$rs["size"]." ".$rs["color"];
    		}
    	}
    	$pro_name->setMultiOptions($opt);
    	
    	$type =new Zend_Form_Element_Select("type");
    	$type->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'transferType()'
    	));
    	$opt= array(''=>$tr->translate("SELECT_TRANSFER_TYPE"),1=>$tr->translate("TRANSFER_IN"),2=>$tr->translate("TRANSFER_OUT"));
    	$type->setMultiOptions($opt);
    	
    	
    	$status =new Zend_Form_Element_Select("status");
    	$status->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	$opt= array(''=>$tr->translate("SELECT_STATUS"),1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	//set value when edit
    	if($data != null) {
    		$tran_num->setValue($data["tran_no"]);
    		$tran_date->setValue($data["date"]);
    		$remark->setValue($data["remark"]);
    		$to_loc->setValue($data["tran_location"]);
    		$status->setValue($data["status"]);
    		$type->setValue($data["type"]);
    	}
    	$this->addElements(array($status,$type,$pro_name,$tran_num,$tran_date,$remark,$from_loc,$to_loc));
    	return $this;
	}
	
	public function addRequest($data=null) {
		$db=new Product_Model_DbTable_DbTransfer();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
			
		$rs_loc = $db->getLocation();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		$tran_num->setValue($db->getRequestTransferNo($result["branch_id"]));
    	
    	$date =date("m/d/Y");
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date);
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
    	$rs_from_loc = $db_global -> getAllLocation();
		//print_r($rs_from_loc);
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
				'onChange'=>'getRequestNo()'
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		$from_loc->setValue($result["branch_id"]);
    	
    	$opt = array(''=>$tr->translate("SELECT BRANCH"));
    	$to_loc = new Zend_Form_Element_Select("to_loc");
    	$to_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$to_loc->setMultiOptions($opt);
    	
    	$pro_name =new Zend_Form_Element_Select("pro_name");
    	$pro_name->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'addNew();'
    	));
    	$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product=$db_stock->getProductName();
    	if(!empty($row_product)){
    		foreach ($row_product as $rs){
    			$opt[$rs["id"]] = $rs["item_name"];
    		}
    	}
    	$pro_name->setMultiOptions($opt);
    	
    	$type =new Zend_Form_Element_Select("type");
    	$type->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'transferType()'
    	));
    	$opt= array(''=>$tr->translate("SELECT_TRANSFER_TYPE"),1=>$tr->translate("TRANSFER_IN"),2=>$tr->translate("TRANSFER_OUT"));
    	$type->setMultiOptions($opt);
    	
    	
    	$status =new Zend_Form_Element_Select("status");
    	$status->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	$opt= array(''=>$tr->translate("SELECT_STATUS"),1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	//set value when edit
    	if($data != null) {
    		$tran_num->setValue($data["re_no"]);
    		$tran_date->setValue($data["re_date"]);
    		$remark->setValue($data["remark"]);
			$from_loc->setValue($data["cur_location"]);
    		$to_loc->setValue($data["tran_location"]);
    		$status->setValue($data["status"]);
    		//$type->setValue($data["type"]);
    	}
    	$this->addElements(array($status,$type,$pro_name,$tran_num,$tran_date,$remark,$from_loc,$to_loc));
    	return $this;
	}
	
	public function makeTransfers($data=null) {
		$db=new Product_Model_DbTable_DbTransfer();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$rs_loc = $db->getLocation(2);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$date =date("m/d/Y");
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		$tran_num->setValue($db->getTransferNo(1));
		
		$re_num = new Zend_Form_Element_Text('re_num');
		$re_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		//$re_num->setValue($db->getRequestTransferNo());
    	
    	$date =date("m/d/Y");
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date);
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
		
    	$re_date = new Zend_Form_Element_Text('re_date');
    	$re_date->setValue($date);
    	$re_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
    	$rs_from_loc = $db_global -> getAllLocation();
		//print_r($rs_from_loc);
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
				'onChange'=>'gettransferNo()'
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		$from_loc->setValue($result["branch_id"]);
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
		$from_loc->setMultiOptions($opt);
    	
    	$opt = array(''=>$tr->translate("SELECT BRANCH"));
    	$to_loc = new Zend_Form_Element_Select("to_loc");
    	$to_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$to_loc->setMultiOptions($opt);
    	
    	$pro_name =new Zend_Form_Element_Select("pro_name");
    	$pro_name->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'addNew();'
    	));
    	$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product=$db_stock->getProductName();
    	if(!empty($row_product)){
    		foreach ($row_product as $rs){
    			$opt[$rs["id"]] = $rs["item_name"]." ".$rs["model"]." ".$rs["size"]." ".$rs["color"];
    		}
    	}
    	$pro_name->setMultiOptions($opt);
    	
    	$type =new Zend_Form_Element_Select("type");
    	$type->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'transferType()'
    	));
    	$opt= array(''=>$tr->translate("SELECT_TRANSFER_TYPE"),1=>$tr->translate("TRANSFER_IN"),2=>$tr->translate("TRANSFER_OUT"));
    	$type->setMultiOptions($opt);
    	
    	
    	$status =new Zend_Form_Element_Select("status");
    	$status->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	$opt= array(''=>$tr->translate("SELECT_STATUS"),1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	//set value when edit
    	if($data != null) {
    		$re_num->setValue($data["re_no"]);
			$re_id = new Zend_Form_Element_Hidden("re_id");
			if(!empty(@$data["re_id"])){
				$re_id->setValue($data["re_id"]);
			}
			
			$this->addElement($re_id);
			if(@$data["date_tran"]!=""){
				$tran_date->setValue(date("m/d/Y",strtotime($data["date_tran"])));
			}else{
				$tran_date->setValue($date);
			}
			if(@$data["tran_no"]!=""){
				$tran_num->setValue($data["tran_no"]);
			}else{
    		$tran_num->setValue($db->getTransferNo($data["tran_location"]));
			}
			$re_date->setValue(date("m/d/Y",strtotime($data["re_date"])));
    		$remark->setValue($data["remark"]);
    		$to_loc->setValue($data["cur_location"]);
			$from_loc->setValue($data["tran_location"]);
    		$status->setValue($data["status"]);
    		//$type->setValue($data["type"]);
    	}
    	$this->addElements(array($re_date,$re_num,$status,$type,$pro_name,$tran_num,$tran_date,$remark,$from_loc,$to_loc));
    	return $this;
	}
	
	public function receiveTransfer($data=null,$type=1) {
		$db=new Product_Model_DbTable_DbTransfer();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$rs_loc = $db->getLocation(2);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		
		$date =date("m/d/Y");
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		$tran_num->setValue($db->getTransferNo(1));
		
		$re_num = new Zend_Form_Element_Text('re_num');
		$re_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		//$re_num->setValue($db->getRequestTransferNo());
    	
    	$date =date("m/d/Y");
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date);
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
		
    	$re_date = new Zend_Form_Element_Text('re_date');
    	$re_date->setValue($date);
    	$re_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
    	$rs_from_loc = $db_global -> getAllLocation();
		//print_r($rs_from_loc);
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
				'onChange'=>'gettransferNo()'
    	));
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_from_loc)){
    		foreach ($rs_from_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$from_loc->setMultiOptions($opt);
		$from_loc->setValue($result["branch_id"]);
		
		$opt = array(''=>$tr->translate("SELECT BRANCH"));
		if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
		$from_loc->setMultiOptions($opt);
    	
    	$opt = array(''=>$tr->translate("SELECT BRANCH"));
    	$to_loc = new Zend_Form_Element_Select("to_loc");
    	$to_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	if(!empty($rs_loc)){
    		foreach ($rs_loc as $rs){
    			$opt[$rs["id"]] = $rs["name"];
    		}
    	}
    	$to_loc->setMultiOptions($opt);
    	
    	$pro_name =new Zend_Form_Element_Select("pro_name");
    	$pro_name->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'addNew();'
    	));
    	$opt= array(''=>$tr->translate("SELECT PRODUCT"));
		$row_product=$db_stock->getProductName();
    	if(!empty($row_product)){
    		foreach ($row_product as $rs){
    			$opt[$rs["id"]] = $rs["item_name"]." ".$rs["model"]." ".$rs["size"]." ".$rs["color"];
    		}
    	}
    	$pro_name->setMultiOptions($opt);
    	
    	$type =new Zend_Form_Element_Select("type");
    	$type->setAttribs(array(
    			'class'=>'form-control select2me',
    			'onChange'=>'transferType()'
    	));
    	$opt= array(''=>$tr->translate("SELECT_TRANSFER_TYPE"),1=>$tr->translate("TRANSFER_IN"),2=>$tr->translate("TRANSFER_OUT"));
    	$type->setMultiOptions($opt);
    	
    	
    	$status =new Zend_Form_Element_Select("status");
    	$status->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	$opt= array(''=>$tr->translate("SELECT_STATUS"),1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	//set value when edit
    	if($data != null) {
    		$re_num->setValue($data["re_no"]);
			$re_id = new Zend_Form_Element_Hidden("re_id");
			if(!empty(@$data["re_id"])){
				$re_id->setValue($data["re_id"]);
			}
			
			$this->addElement($re_id);
			if(@$data["date_tran"]!=""){
				$tran_date->setValue(date("m/d/Y",strtotime($data["date_tran"])));
			}else{
				$tran_date->setValue($date);
			}
			if(@$data["tran_no"]!=""){
				$tran_num->setValue($data["tran_no"]);
			}else{
    		$tran_num->setValue($db->getTransferNo($data["tran_location"]));
			}
			$re_date->setValue(date("m/d/Y",strtotime($data["re_date"])));
    		$remark->setValue($data["remark"]);
    		$to_loc->setValue($data["cur_location"]);
			$from_loc->setValue($data["tran_location"]);
    		$status->setValue($data["status"]);
    		//$type->setValue($data["type"]);
    	}
    	$this->addElements(array($re_date,$re_num,$status,$type,$pro_name,$tran_num,$tran_date,$remark,$from_loc,$to_loc));
    	return $this;
	}
	function frmFilter(){
		$db=new Product_Model_DbTable_DbTransfer();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$rs_loc = $db_global->getAllLocation();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$tran_num = new Zend_Form_Element_Text('avd_search');
		$tran_num->setAttribs(array('class'=>'form-control'));
		$tran_num->setValue($request->getParam("avd_search"));
		 
		$date = date("m/d/Y");
		$start_date = new Zend_Form_Element_Text('start_date');
		$start_date->setValue($date);
		$start_date->setAttribs(array('class'=>'form-control date-picker'));
		if($request->getParam("start_date") !=""){
			$date = $request->getParam("start_date");
		}else{
			$date = date("m/d/Y");
		}
		$start_date->setValue($date);
		
		$end_date = new Zend_Form_Element_Text('end_date');
		$end_date->setValue($date);
		$end_date->setAttribs(array('class'=>'form-control date-picker'));
		if($request->getParam("end_date") !=""){
			$date = $request->getParam("end_date");
		}else{
			$date = date("m/d/Y");
		}
		$end_date->setValue($date);
		
		$type =new Zend_Form_Element_Select("type");
		$type->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'transferType()'
		));
		$opt= array(''=>$tr->translate("SELECT_TRANSFER_TYPE"),1=>$tr->translate("TRANSFER_IN"),2=>$tr->translate("TRANSFER_OUT"));
		$type->setMultiOptions($opt);
		$type->setValue($request->getParam("type"));
		 
		$status =new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$opt= array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$opt = array('-1'=>$tr->translate("SELECT BRANCH"));
		$to_loc = new Zend_Form_Element_Select("branch");
		$to_loc->setAttribs(array(
				'class'=>'form-control select2me',
		));
		if(!empty($rs_loc)){
			foreach ($rs_loc as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$to_loc->setMultiOptions($opt);
		$to_loc->setValue($request->getParam("branch"));
		
		$opt= array('-1'=>$tr->translate("SELECT_STATUS"),1=>$tr->translate("Wait check"),2=>$tr->translate("Reject"),3=>$tr->translate("Checked"));
		$check_stat = new Zend_Form_Element_Select("check_stat");
		$check_stat->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$check_stat->setValue($request->getParam("check_stat"));
		$check_stat->setMultiOptions($opt);
		
		
		
		$this->addElements(array($status,$type,$tran_num,$start_date,$to_loc,$end_date,$check_stat));
		return $this;
	}
}