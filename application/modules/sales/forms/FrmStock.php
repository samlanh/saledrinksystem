<?php 
class Sales_Form_FrmStock extends Zend_Form
{
	public function init()
    {
    	
	}
	public function showSaleAgentForm($data=null, $stockID=null) {

		$db=new Application_Model_DbTable_DbGlobal();
		$db_sale = new Sales_Model_DbTable_DbSalesAgent();
		$codes = $db_sale->getSaleAgentCode(1);
		$date =new Zend_Date();
		$nameElement = new Zend_Form_Element_Text('name');
		$nameElement->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Agent Name'));
    	$this->addElement($nameElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$phoneElement->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Phone Number'));
    	$this->addElement($phoneElement);
    	
    	$emailElement = new Zend_Form_Element_Text('email');
    	$emailElement->setAttribs(array('class'=>'form-control','placeholder'=>'Enter Email Address'));
    	$this->addElement($emailElement);
    	
    	$addressElement = new Zend_Form_Element_Text('address');
    	$addressElement->setAttribs(array('placeholder'=>'Enter Current Address',"class"=>"form-control"));
    	$this->addElement($addressElement);
    	
    	$jobTitleElement = new Zend_Form_Element_Text('job_title');
    	$jobTitleElement->setAttribs(array('placeholder'=>'Enter Position',"class"=>"form-control"));
    	$this->addElement($jobTitleElement);
    	
		$descriptionElement = new Zend_Form_Element_Textarea('description');
		$descriptionElement->setAttribs(array('placeholder'=>'Descrtion Here...',"class"=>"form-control","rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$rowsStock = $db->getGlobalDb('SELECT id,name FROM tb_sublocation WHERE name!=""  ORDER BY id DESC ');
    	$optionsStock = array('1'=>'Default Location','-1'=>'Add New Location');
    	if(count($rowsStock) > 0) {
    		foreach($rowsStock as $readStock) $optionsStock[$readStock['id']]=$readStock['name'];
    	}
    	$mainStockElement = new Zend_Form_Element_Select('branch_id');
    	$mainStockElement->setAttribs(array('OnChange'=>'getSaleCode()','class'=>'form-control select2me'));
    	$mainStockElement->setMultiOptions($optionsStock);
    	$this->addElement($mainStockElement);
    	
    	$user_name = new Zend_Form_Element_Text('user_name');
    	$user_name->setAttribs(array('placeholder'=>'Enter User Name',"class"=>"form-control",'required'=>'required'));
    	$this->addElement($user_name);
    	
    	$password = new Zend_Form_Element_Password('password');
    	$password->setAttribs(array('placeholder'=>'Enter Password',"class"=>"form-control"));
    	$this->addElement($password);
    	
    	$pob= new Zend_Form_Element_Text('pob');
    	$pob->setAttribs(array('placeholder'=>'Enter Place of Birdth',"class"=>"form-control"));
    	$this->addElement($pob);
    	
    	$dob= new Zend_Form_Element_Text('dob');
    	$dob->setAttribs(array('placeholder'=>'Enter Position',"class"=>"form-control date-picker"));
    	$dob->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($dob);
    	
    	$photo = new Zend_Form_Element_File("photo");
    	$this->addElement($photo);
    	
    	$document = new Zend_Form_Element_File("document");
    	$this->addElement($document);
    	
    	$signature = new Zend_Form_Element_File("signature");
    	$this->addElement($signature);
    	
    	$bank_acc = new Zend_Form_Element_Text("bank_acc");
    	$bank_acc->setAttribs(array('placeholder'=>'Enter Bank Account',"class"=>"form-control"));
    	$this->addElement($bank_acc);
    	
    	$refer_name = new Zend_Form_Element_Text("refer_name");
    	$refer_name->setAttribs(array('placeholder'=>'Enter Reference Name',"class"=>"form-control"));
    	$this->addElement($refer_name);
    	
    	$refer_phone = new Zend_Form_Element_Text("refer_phone");
    	$refer_phone->setAttribs(array('placeholder'=>'Enter Reference Phone',"class"=>"form-control"));
    	$this->addElement($refer_phone);
    	
    	$refer_addres = new Zend_Form_Element_Textarea("refer_address");
    	$refer_addres->setAttribs(array('placeholder'=>'Enter Reference Address',"class"=>"form-control","style"=>"height:40px"));
    	$this->addElement($refer_addres);
    	
    	$satrt_working_date = new Zend_Form_Element_Text("start_working_date");
    	$satrt_working_date->setAttribs(array('placeholder'=>'Enter Bank Account',"class"=>"form-control date-picker"));
    	$satrt_working_date->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($satrt_working_date);
    	
    	$row_user_type = $db->getGlobalDb('SELECT u.`user_type_id`,u.`user_type`,u.`parent_id` FROM `tb_acl_user_type` AS u WHERE u.`status`=1');
    	$option_user = array('-1'=>'Select User Type');
    	if(count($row_user_type) > 0) {
    		foreach($row_user_type as $rs) $option_user[$rs['user_type_id']]=$rs['user_type'];
    	}
    	$user_type = new Zend_Form_Element_Select('user_type');
    	$user_type->setAttribs(array('class'=>'form-control select2me'));
    	$user_type->setMultiOptions($option_user);
    	$this->addElement($user_type);
    	
    	$row_manger = $db->getGlobalDb('SELECT u.`user_id`,u.`fullname` FROM `tb_acl_user` AS u,`tb_acl_user_type` AS ut WHERE u.`status`=1 AND u.`user_type_id`=ut.`user_type_id` AND u.`user_type_id`=5');
    	$option_user = array('-1'=>'Select User Type');
    	if(count($row_manger) > 0) {
    		foreach($row_manger as $rs) $option_user[$rs['user_id']]=$rs['fullname'];
    	}
    	$manage_by = new Zend_Form_Element_Select('manage_by');
    	$manage_by->setAttribs(array('class'=>'form-control select2me'));
    	$manage_by->setMultiOptions($option_user);
    	$this->addElement($manage_by);
    	
    	$code = new Zend_Form_Element_Text("code");
    	$code->setAttribs(array("class"=>"form-control"));
    	$code->setValue($codes);
    	$this->addElement($code);
    	
    	$old_photo = new Zend_Form_Element_Hidden("old_photo");
    	$this->addElement($old_photo);
    	
    	$old_document = new Zend_Form_Element_Hidden("old_document");
    	$this->addElement($old_document);
    	
    	$old_signature = new Zend_Form_Element_Hidden("old_signature");
    	$this->addElement($old_signature);
    	
    	$user_id = new Zend_Form_Element_Hidden("user_id");
    	$this->addElement($user_id);
		
		$row_status = $db->getGlobalDb('SELECT v.key_code,v.name_kh FROM tb_view as v WHERE v.type=5 AND v.status=1');
     	$option_status = array();
     	if(count($row_status) > 0) {
     		foreach($row_status as $rs) $option_status[$rs['key_code']]=$rs['name_kh'];
     	}
		$status=new Zend_Form_Element_Select("status");
		$status->setAttribs(array('class'=>'form-control select2me'));
		$status->setMultiOptions($option_status);
		$this->addElement($status);
		
//     	$sex = $db->getGlobalDb('SELECT u.`user_id`,u.`fullname` FROM `tb_acl_user` AS u,`tb_acl_user_type` AS ut WHERE u.`status`=1 AND u.`user_type_id`=ut.`user_type_id` AND u.`user_type_id`=5');
//     	$option_user = array('-1'=>'Select User Type');
//     	if(count($row_manger) > 0) {
//     		foreach($row_manger as $rs) $option_user[$rs['user_id']]=$rs['fullname'];
//     	}
//     	$manage_by = new Zend_Form_Element_Select('manage_by');
//     	$manage_by->setAttribs(array('class'=>'form-control select2me'));
//     	$manage_by->setMultiOptions($option_user);
//     	$this->addElement($manage_by);
    	
    	//set value when edit
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    	    $this->addElement($idElement);
    	    $idElement->setValue($data['id']);
    		$nameElement->setValue($data['name']);
    		$phoneElement->setValue($data['phone']);
    		$emailElement->setValue($data['email']);
    		$addressElement->setValue($data['address']);
    		$jobTitleElement->setValue($data['job_title']);
    		$mainStockElement->setValue($data["branch_id"]);
    		$descriptionElement->setValue($data['description']);
    		$code->setValue($data["code"]);
    		$pob->setValue($data["pob"]);
    		$dob->setValue($data["dob"]);
    		$user_name->setValue($data["user_name"]);
    		//$password->setAttribs(array('class'=>'form-control','readOnly'=>true));
			if($data["password"]!="d41d8cd98f00b204e9800998ecf8427e"){
				$placeholder ="*****";
			}else{
				$placeholder="";
			}
    		$password->setAttribs(array('placeholder'=>$placeholder,'class'=>'form-control','readOnly'=>true));
    		$user_type->setValue($data["user_type"]);
    		$manage_by->setValue($data["manage_by"]);
    		$refer_name->setValue($data["refer_name"]);
    		$refer_phone->setValue($data["refer_phone"]);
    		$refer_addres->setValue($data["refer_add"]);
    		$bank_acc->setValue($data["bank_acc"]);
    		$old_photo->setValue($data["photo"]);
    		$old_document->setValue($data["document"]);
    		$old_signature->setValue($data["signature"]);
    		$user_id->setValue($data["acl_user"]);
			$status->setValue($data["status"]);
    	}
    	return $this;
	}
}