<?php

class Application_Form_FrmReturnItem extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    
    public function returnItemForm($data=null) {//for stock out
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" ORDER BY vendor_id DESC');
    	$customerValue = $request->getParam('vendor_id');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_id=new Zend_Form_Element_Select('v_name');
    	$vendor_id ->setAttribs(array(
    			'class'=>'validate[required]',
    			'Onchange'=>'getVendorInfo()'));
    	$vendor_id->setMultiOptions($options);
    	//$vendor_id->setValue($customerValue);
    	$this->addElement($vendor_id);
    	
//     	$contactElement = new Zend_Form_Element_Text('contact');
//     	$contactElement->setAttribs(array('placeholder' => 'Enter Contact Name'));
//     	$this->addElement($contactElement);
    	
//     	$phoneElement = new Zend_Form_Element_Text('txt_phone');
//     	$phoneElement->setAttribs(array('placeholder' => 'Enter Phone Number'));
//     	$this->addElement($phoneElement);
    	
//     	$addressElement= new Zend_Form_Element_Textarea("vendor_address");
//     	$this->addElement($addressElement);
    	
    	$returnElement = new Zend_Form_Element_Text('retun_order');
    	$this->addElement($returnElement);
    	
//     	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
//     	if($rowspayment) {
//     		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
//     	}
//     	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
//     	$paymentmethodElement->setAttribs(array('class'=>'validate[required] demo-code-language',));
//     	$paymentmethodElement->setMultiOptions($options_cg);
//     	$this->addElement($paymentmethodElement);
    	 
//     	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
//     	if($rowsPayment) {
//     		foreach($rowsPayment as $readPayment) $options_cur[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
//     	}
//     	$currencyElement = new Zend_Form_Element_Select('currency');
//     	$currencyElement->setAttribs(array('class'=>'validate[required] demo-code-language',));
//     	$currencyElement->setMultiOptions($options_cur);
//     	$this->addElement($currencyElement);

    	$rs=$db->getGlobalDb('SELECT DISTINCT Name,LocationId FROM tb_sublocation WHERE Name!="" AND status=1 ');
    	$options=array($tr->translate('Please_Select'));
    	$locationValue = $request->getParam('LocationId');
    	foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$location_id=new Zend_Form_Element_Select('LocationId');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'LocationId',
    			'class'=>'validate[required]'
    	));
    	$location_id->setValue($locationValue);
    	$this->addElement($location_id);
    	
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($allTotalElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'validate[custom[number]]','onBlur'=>'doRemain()'));
    	$this->addElement($paidElement);
    	
    	$balanceElement = new Zend_Form_Element_Text('balance');
    	$balanceElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($balanceElement);
    	
    	
    	$remarkaddElement = new Zend_Form_Element_Textarea('return_remark');
    	$this->addElement($remarkaddElement);
    	 
    	$returnDateElement = new Zend_Form_Element_Text('return_date');
    	$returnDateElement->setAttribs(array('class'=>'validate[required,past[NOW]]',));
    	$date = new Zend_Date();
    	$returnDateElement->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($returnDateElement);
    
    	Application_Form_DateTimePicker::addDateField(array('return_date'));
    	//set value when edit
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement->setValue($data['return_id']);

    		$vendor_id->setValue($data['vendor_id']);
    		$location_id->setValue($data["LocationId"]);
    		$old_location = new Zend_Form_Element_Text("old_location");
    		$this->addElement($old_location);
    		$old_location->setValue($data["LocationId"]);
//     		$contactElement->setValue($data['contact_name']);
//     		$phoneElement->setValue($data['phone']);
//     		$addressElement->setValue($data['add_name']);
    		$returnElement->setValue($data['return_no']);
    		$returnElement->setAttribs(array('readonly'=>'readonly'));
    		$remarkaddElement->setValue($data['remark']);
    		//$currencyElement->setValue($data['currency_id']);
    		//$paymentmethodElement->setValue($data['payment_method']);
    		$returnDateElement->setValue($data['date_return']);
    		$allTotalElement->setValue($data['all_total']);
    		$paidElement->setValue($data['paid']);
    		$balanceElement->setValue($data['balance']);
    		
    		
    		
//     		$returnDateElement->setValue($data['return_date']);
//     		$statusElement->setValue($data['status']);
    	}
    	return $this;
    }
    
    public function returnInItemForm($data=null) {//for stock out
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" ORDER BY vendor_id DESC');
    	$customerValue = $request->getParam('vendor_id');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_id=new Zend_Form_Element_Select('v_name');
    	$vendor_id ->setAttribs(array(
    			'class'=>'validate[required]',
    			'Onchange'=>'getVendorInfo()'));
    	$vendor_id->setMultiOptions($options);
    	//$vendor_id->setValue($customerValue);
    	$this->addElement($vendor_id);
    	 
    	//     	$contactElement = new Zend_Form_Element_Text('contact');
    	//     	$contactElement->setAttribs(array('placeholder' => 'Enter Contact Name'));
    	//     	$this->addElement($contactElement);
    	 
    	//     	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	//     	$phoneElement->setAttribs(array('placeholder' => 'Enter Phone Number'));
    	//     	$this->addElement($phoneElement);
    	 
    	//     	$addressElement= new Zend_Form_Element_Textarea("vendor_address");
    	//     	$this->addElement($addressElement);
    	 
    	$returnElement = new Zend_Form_Element_Text('retun_order');
    	$this->addElement($returnElement);
    	 
    	//     	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	//     	if($rowspayment) {
    	//     		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	//     	}
    	//     	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	//     	$paymentmethodElement->setAttribs(array('class'=>'validate[required] demo-code-language',));
    	//     	$paymentmethodElement->setMultiOptions($options_cg);
    	//     	$this->addElement($paymentmethodElement);
    
    	//     	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
    	//     	if($rowsPayment) {
    	//     		foreach($rowsPayment as $readPayment) $options_cur[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	//     	}
    	//     	$currencyElement = new Zend_Form_Element_Select('currency');
    	//     	$currencyElement->setAttribs(array('class'=>'validate[required] demo-code-language',));
    	//     	$currencyElement->setMultiOptions($options_cur);
    	//     	$this->addElement($currencyElement);
    
    	$rs=$db->getGlobalDb('SELECT DISTINCT Name,LocationId FROM tb_sublocation WHERE Name!="" AND status=1 ');
    	$options=array($tr->translate('Please_Select'));
    	$locationValue = $request->getParam('LocationId');
    	foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$location_id=new Zend_Form_Element_Select('LocationId');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'LocationId',
    			'class'=>'validate[required]'
    	));
    	$location_id->setValue($locationValue);
    	$this->addElement($location_id);
    	 
    	 
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($allTotalElement);
    	 
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'validate[custom[number]]','onBlur'=>'doRemain()'));
    	$this->addElement($paidElement);
    	 
    	$balanceElement = new Zend_Form_Element_Text('balance');
    	$balanceElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($balanceElement);
    	 
    	 
    	$remarkaddElement = new Zend_Form_Element_Textarea('return_remark');
    	$this->addElement($remarkaddElement);
    
    	$returnDateElement = new Zend_Form_Element_Text('return_date');
    	$returnDateElement->setAttribs(array('class'=>'validate[required,past[NOW]]',));
    	$date = new Zend_Date();
    	$returnDateElement->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($returnDateElement);
    
    	Application_Form_DateTimePicker::addDateField(array('return_date'));
    	//set value when edit
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement->setValue($data['return_id']);
    
    		$vendor_id->setValue($data['vendor_id']);
    		//$contactElement->setValue($data['contact_name']);
    		//$phoneElement->setValue($data['phone']);
    		//$addressElement->setValue($data['add_name']);
    		$returnElement->setValue($data['return_no']);
    		$returnElement->setAttribs(array('readonly'=>'readonly'));
    		$remarkaddElement->setValue($data['remark']);
    		//$currencyElement->setValue($data['currency_id']);
    		//$paymentmethodElement->setValue($data['payment_method']);
    		$returnDateElement->setValue($data['date_return']);
    		$allTotalElement->setValue($data['all_total']);
    		$paidElement->setValue($data['paid']);
    		$balanceElement->setValue($data['balance']);
    
    
    
    		//     		$returnDateElement->setValue($data['return_date']);
    		//     		$statusElement->setValue($data['status']);
    	}
    	return $this;
    }
    public function returnItemInFrm($data=null) {//for stock vendor stock in
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" ORDER BY vendor_id DESC');
    	//$customerValue = $request->getParam('vendor_id');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_id=new Zend_Form_Element_Select('v_name');
    	$vendor_id ->setAttribs(array(
    			'class'=>'validate[required]',
    			'Onchange'=>'getVendorInfo()'));
    	$vendor_id->setMultiOptions($options);
    	//$vendor_id->setValue($customerValue);
    	$this->addElement($vendor_id);
    	
    	
    	$rss=$db->getGlobalDb('SELECT return_id, return_no FROM tb_return WHERE return_no!="" AND is_active=1 ORDER BY return_id DESC');
    	//$customerValues = $request->getParam('return_id');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if(!empty($rss)) foreach($rss as $read) $options[$read['return_id']]=$read['return_no'];
    	$refer_id=new Zend_Form_Element_Select('return_reference');
    	$refer_id ->setAttribs(array(
    			'class'=>'validate[required]',
    			'Onchange'=>'getreturnorder()'
    			));
    	$refer_id->setMultiOptions($options);
    	//$vendor_id->setValue($customerValue);
    	$this->addElement($refer_id);
    	 
    	 
    	$returnElement = new Zend_Form_Element_Text('retun_order');
    	$returnElement->setAttribs(array('placeholder' =>'Optional'));
    	$this->addElement($returnElement);
    
    	$rs=$db->getGlobalDb('SELECT DISTINCT Name,LocationId FROM tb_sublocation WHERE Name!="" AND status=1 ');
    	//$options=array(''=>'Please Select Location');
    	$options="";
    	$locationValue = $request->getParam('LocationId');
    	foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$location_id=new Zend_Form_Element_Select('LocationId');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'LocationId',
    			'class'=>'validate[required]'
    	));
    	$location_id->setValue($locationValue);
    	$this->addElement($location_id);
    	 
    	 
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($allTotalElement);
    	 
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'validate[custom[number]]','onBlur'=>'doRemain()'));
    	$this->addElement($paidElement);
    	 
    	$balanceElement = new Zend_Form_Element_Text('balance');
    	$balanceElement->setAttribs(array('readonly'=>'readonly'));
    	$this->addElement($balanceElement);
    	 
    	 
    	$remarkaddElement = new Zend_Form_Element_Textarea('return_remark');
    	$this->addElement($remarkaddElement);
    	
    	$returnDateElement = new Zend_Form_Element_Text('return_date');
    	$returnDateElement->setAttribs(array('class'=>'validate[required,past[NOW]]',));
    	$date = new Zend_Date();
    	$returnDateElement->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($returnDateElement);
    	
    	$returninDateElement = new Zend_Form_Element_Text('returnin_date');
    	$returninDateElement->setAttribs(array('class'=>'validate[required,past[NOW]]',));
    	$date = new Zend_Date();
    	$returninDateElement->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($returninDateElement);
    
    	Application_Form_DateTimePicker::addDateField(array('return_date'));
    	//set value when edit
    	if($data != null) {
    		
    		$result=$db->getGlobalDb('SELECT return_id, return_no FROM tb_return WHERE return_no!="" ORDER BY return_id DESC');
    		//$customerValues = $request->getParam('return_id');
    		$options=array(''=>$tr->translate('Please_Select'));
    		if(!empty($result)) foreach($result as $read) $options[$read['return_id']]=$read['return_no'];
    		$refers_id=new Zend_Form_Element_Select('return_references');
    		$refers_id ->setAttribs(array(
    				'class'=>'validate[required]',
    				'Onchange'=>'getreturnorder()'
    		));
    		$refers_id->setMultiOptions($options);
    		//$vendor_id->setValue($customerValue);
    		$this->addElement($refers_id);
    		
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement->setValue($data['returnin_id']);
    		
    		$old_location = new Zend_Form_Element_Text("old_location");
    		$this->addElement($old_location);
    		$old_location->setValue($data["location_id"]);
    		
    		$refers_id->setValue($data["returnout_id"]);
    		$refers_id->setAttribs(array('disabled' => 'disabled'));
    		
    		$location_id->setValue($data["location_id"]);
    		
    		$returnElement->setValue($data['returnin_no']);
    		
     		$vendor_id->setValue($data['vendor_id']);
     		
    		$returninDateElement->setValue($data['date_return_in']);
    		
    		$allTotalElement->setValue($data['all_total']);
    	}
    	return $this;
    }
    
    public function CustomerReturnItem($data=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'class'=>'validate[required]',
    	));
    	$locationID->setMultiOptions($options);
    	$this->addElement($locationID);
    	
    	$rs=$db->getGlobalDb('SELECT customer_id, cust_name FROM tb_customer WHERE cust_name!=""');
    	$options="";
    	$options=array(''=>$tr->translate("Please_Select"),'-1'=>$tr->translate('ADD_CUSTOMER'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	$customerID=new Zend_Form_Element_Select('customer_id');
    	$customerID ->setAttribs(array( 'id'=>'customer_id',
    			'Onchange'=>'getCustomerInfo()',
    			'class'=>'validate[required]'
    			//	validate[required]
    	));
    	$customerID ->setMultiOptions($options);
    	$this->addElement($customerID);
    	
    	
    	$returnElement= new Zend_Form_Element_Text("return_no");
    	$returnElement->setAttribs(array('placeholder' =>'Optional'));
    	$this->addElement($returnElement);
    	
    	$InvoiceElement= new Zend_Form_Element_Text("invoice_no");
    	$InvoiceElement->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($InvoiceElement);
    	
    	
    	$return_date = new Zend_Form_Element_Text("return_date");
    	$return_date ->setAttribs(array('class'=>'validate[required]'));
    	$date = new Zend_Date();
    	$return_date->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($return_date);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
        $paidElement->setAttribs(array('onblur'=>'doRemain()',));
    	$this->addElement($paidElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark_return');
    	$this->addElement($descriptionElement);
    	
    	$remainlElement = new Zend_Form_Element_Text('balance');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	
    	$all_totalElement = new Zend_Form_Element_Text('all_total');
    	$all_totalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($all_totalElement);
    	
    	
    	Application_Form_DateTimePicker::addDateField(array('return_date'));
    	
    	if($data!=null){
    		$elementId = new Zend_Form_Element_Text('id');
    		$elementId->setValue($data["return_id"]);
    		$this->addElement($elementId);
    		$customerID->setValue($data["customer_id"]);
    		$locationID->setValue($data["LocationId"]);
    		$returnElement->setValue($data["return_no"]);
    		$InvoiceElement->setValue($data["invoice_no"]);
    		$return_date->setValue($data["date_return"]);
    		$all_totalElement->setValue($data["all_total"]);
    	}
    	return $this;
    }
    public function ReturnItemToCustomer($data=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' AND status=1 ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'class'=>'validate[required]',
    	));
    	$locationID->setMultiOptions($options);
    	$this->addElement($locationID);
    	 
    	$rs=$db->getGlobalDb('SELECT return_id, return_no FROM tb_return_customer_in WHERE return_no!="" AND status = 1');
    	$options=array(""=>$tr->translate("Please_Select"));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['return_id']]=$read['return_no'];
    	
    	$returnID=new Zend_Form_Element_Select('return_id');
    	$returnID ->setAttribs(array( 'id'=>'return_id','class'=>'validate[required]',
    			"Onchange"=>"getreturnorder()"));
    	$returnID ->setMultiOptions($options);
    	$this->addElement($returnID);
    	 
    	$InvoiceElement= new Zend_Form_Element_Text("invoice_no");
    	$InvoiceElement->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($InvoiceElement);
    	 
    	 
    	$return_date_in = new Zend_Form_Element_Text("return_date_in");
    	$return_date_in ->setAttribs(array('class'=>'validate[required]'));
    	$date = new Zend_Date();
    	$return_date_in->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($return_date_in);
    	
    	$return_out_date = new Zend_Form_Element_Text("return_out_date");
    	$return_out_date->setAttribs(array('class'=>'validate[required]'));
    	$date = new Zend_Date();
    	$return_out_date->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($return_out_date);
    	
    	
    	
    	$rs=$db->getGlobalDb('SELECT c.`customer_id`,c.`cust_name`,c.`type_price` FROM tb_customer AS c WHERE cust_name!=""');
    	$options=array(""=>$tr->translate("Please_Select"));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	 
    	$customer = new Zend_Form_Element_Select("customer_id");
    	$customer ->setAttribs(array('class'=>'validate[required]',
    			//"Onchange"=>"getreturnorder()"
    			));
    	$customer ->setMultiOptions($options);
    	$this->addElement($customer);
    	 
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('onblur'=>'doRemain()',));
    	$this->addElement($paidElement);
    	 
    	$descriptionElement = new Zend_Form_Element_Textarea('remark_return');
    	$this->addElement($descriptionElement);
    	 
    	$remainlElement = new Zend_Form_Element_Text('balance');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	 
    	$all_totalElement = new Zend_Form_Element_Text('all_total');
    	$all_totalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($all_totalElement);
    	 
    	 
    	Application_Form_DateTimePicker::addDateField(array('return_out_date'));
    	
    	if($data!=null){
    		$rs=$db->getGlobalDb('SELECT return_id, return_no FROM tb_return_customer_in WHERE return_no!=""');
    		$options=array(""=>$tr->translate("Please_Select"));
    		if(!empty($rs)) foreach($rs as $read) $options[$read['return_id']]=$read['return_no'];
    		 
    		$returnin=new Zend_Form_Element_Select('returnin_id');
    		$returnin ->setAttribs(array( 'id'=>'returnin_id','class'=>'validate[required]',
    				"Onchange"=>"getreturnorder()"));
    		$returnin ->setMultiOptions($options);
    		$this->addElement($returnin);
    		
    		$id = new Zend_Form_Element_Text("id");
    		$this->addElement($id);
    		$id->setValue($data["returnout_id"]);
    		$locationID->setValue($data["location_id"]);
    		$returnin->setValue($data['return_id']);
    		$InvoiceElement->setValue($data["returnout_no"]);
    		$return_date_in->setValue($data["date_return_in"]);
    		$return_out_date->setValue($data["date_return_out"]);
    		$customer->setValue($data["customer_id"]);
    		$all_totalElement->setValue($data["all_total"]);
    	}
    	return $this;
    }
    	
}

