<?php 
class Product_Form_FrmReceive extends Zend_Form
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
		$rs_loc = $db->getLocation(1);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		//$tran_num->setValue($db->getTransferNo());
    	
    	$date =new Zend_Date();
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date->get('MM/dd/YYYY'));
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	
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
		$rs_loc = $db->getLocation(1);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		$tran_num->setValue($db->getRequestTransferNo());
    	
    	$date =date("m/d/Y");
    	$tran_date = new Zend_Form_Element_Text('tran_date');
    	$tran_date->setValue($date);
    	$tran_date->setAttribs(array('class'=>'form-control date-picker', 'required'=>'required',));
    	
    	$remark = new Zend_Form_Element_Textarea("remark");
    	$remark->setAttribs(array('class'=>'form-control','style'=>'width: 100%;height:35px'));
    	
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
    	));
    	
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
    		$tran_num->setValue($data["tran_no"]);
    		$tran_date->setValue($data["re_date"]);
    		$remark->setValue($data["remark"]);
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
		
		$date =date("m/d/Y");
		
		$receive_num = new Zend_Form_Element_Text('receive_num');
		$receive_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		
		$this->addElement($receive_num);
	
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control', 'required'=>'required','readOnly'=>true));
		//$tran_num->setValue($db->getTransferNo());
		
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
    	
    	$from_loc = new Zend_Form_Element_Select("from_loc");
    	$from_loc->setAttribs(array(
    			'class'=>'form-control select2me',
				'readOnly'=>'readOnly',
    	));
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
				'readOnly'=>true,
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
    			'onChange'=>'addNew();',
				'readOnly'=>true,
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
    			'onChange'=>'transferType()',
				
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
			$tran_num->setValue($data["tran_no"]);
			$re_id = new Zend_Form_Element_Hidden("re_id");
			$re_id->setValue($data["req_id"]);
			$this->addElement($re_id);
			if(@$data["date_tran"]!=""){
				$tran_date->setValue(date("m/d/Y",strtotime(@$data["date_tran"])));
			}else{
				$tran_date->setValue($date);
			}
			if(@$data["receive_no"]!=""){
				$receive_num->setValue(@$data["receive_no"]);
			}else{
				$receive_num->setValue($db->getReceiveNo($data["tran_location"]));
			}
			$re_date->setValue($data["re_date"]);
    		$remark->setValue($data["remark"]);
    		$to_loc->setValue($data["tran_location"]);
			$from_loc->setValue($data["cur_location"]);
    		$status->setValue(1);
    		//$type->setValue($data["type"]);
    	}
    	$this->addElements(array($re_date,$re_num,$status,$type,$pro_name,$tran_num,$tran_date,$remark,$from_loc,$to_loc));
    	return $this;
	}
	function frmFilter(){
		$db=new Product_Model_DbTable_DbTransfer();
		$db_stock = new Product_Model_DbTable_DbAdjustStock();
		$rs_loc = $db->getLocation(1);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$tran_num = new Zend_Form_Element_Text('tran_num');
		$tran_num->setAttribs(array('class'=>'form-control'));
		$tran_num->setValue($request->getParam("tran_num"));
		 
		$date =new Zend_Date();
		$tran_date = new Zend_Form_Element_Text('tran_date');
		$tran_date->setValue($date->get('MM/dd/YYYY'));
		$tran_date->setAttribs(array('class'=>'form-control date-picker'));
		$tran_date->setValue($request->getParam("tran_date"));
		
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
		$to_loc->setValue($request->getParam("to_loc"));
		
		$this->addElements(array($status,$type,$tran_num,$tran_date,$to_loc));
		return $this;
	}
}