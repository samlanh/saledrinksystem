<?php

class Application_Form_FrmCheckIn extends Zend_Form
{
    public function init()
    {
   
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function productOrder($data=null)
    {
    	//Application_Form_FrmLanguages::getCurrentlanguage();
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" ORDER BY vendor_id DESC');
    	$customerValue = $request->getParam('vendor_id');
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('Add_New_Vendor'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_id=new Zend_Form_Element_Select('v_name');
    	$vendor_id ->setAttribs(array(
    			'class' => 'validate[required]',
    			'Onchange'=>'getCustomerInfo()',
    			'readonly'=>'readonly'
    			));
    	$vendor_id->setMultiOptions($options);
    	//$customerID->setattribs(array(id'=>'customer_id','onchange'=>'this.form.submit()',));
    	//$vendor_id->setValue($customerValue);
    	$this->addElement($vendor_id);
    	
    	
    	
    	
//     	$rowsCategory= $db->getGlobalDb('SELECT CategoryId, Name FROM tb_category ORDER BY CategoryId DESC ');
//     	$options = "";
//     	if($result["level"]==1 OR $result["level"]==2){
//     		$options = array("1"=>"Defaul Catogory","-1"=>"Add New Category");
//     	}
//     	if($rowsCategory) {
//     		foreach($rowsCategory as $readCategory) $options[$readCategory['CategoryId']]=$readCategory['Name'];
//     	}
//     	$categoryElement = new Zend_Form_Element_Select('category');
//     	$categoryElement->setAttribs(array('class' => 'validate[required] demo-code-language',"Onchange"=>"showPopupCategory()"));
//     	$categoryElement->setMultiOptions($options);
//     	$this->addElement($categoryElement);
    	
    	
    	
		$order_no=$db->getGlobalDb("SELECT `order`, order_id from tb_purchase_order where `status` = 3 or `status`=2"); 
		$opt_order_no = array(""=>$tr->translate("SELECT_INVOICE"));
		if(!empty($order_no))
			foreach ($order_no as $result){
			$opt_order_no[$result["order_id"]] = $result["order"];
		}
    	$roder_element= new Zend_Form_Element_Select("order_no");
    	$roder_element->setMultiOptions($opt_order_no);
    	$roder_element->setAttribs(array('class' => 'validate[required]'
    			,"Onchange"=>"getPurchaseOrder();"));
    	$this->addElement($roder_element);
    	
    	$order_num = new Zend_Form_Element_hidden("order_num");
    	$this->addElement($order_num);
    	
    	$invoice_no = new Zend_Form_Element_Text("invoice_no");
    	$this->addElement($invoice_no);
    	$invoice_no->setAttribs(array('placeholder' => 'Optional'));
    	
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contact Name'));
    	$this->addElement($contactElement);
    	
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Enter Order'));
    	$this->addElement($orderElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Phone Number'));
    	$this->addElement($phoneElement);
    	
    	
//     	$option_loc = 'SELECT LocationId, Name FROM tb_sublocation WHERE Name!="" ';
//     	if($result['level']!=1 || $result['level']!=2){//if this isn't super user or inventory manager
//     		$option_loc .= " AND LocationId = ".$result['location_id'];//select only this user location
//     	}
    	
//     	$option_loc .= ' ORDER BY LocationId DESC';
//     	$rs=$db->getGlobalDb($option_loc);
//     	$productValue = $request->getParam('LocationId');
//     	$option =array();
//     	if($result['level']==1 || $result['level']==2){//if this is super user
//    			$option =array("1"=>"Defaul Location","-1"=>"Add New Location");
//     	}
    	
//     	if(!empty($rs)) foreach($rs as $read) $option[$read['LocationId']]=$read['Name'];
//     	$locationID=new Zend_Form_Element_Select('LocationId');
//     	$locationID->setMultiOptions($option);
//     	$locationID->setattribs(array('id'=>'LocationId',
//     			'Onchange'=>'AddLocation()',
//     			'class'=>'demo-code-language'
//     			));
//     	$locationID->setValue($productValue);
//     	$this->addElement($locationID);  
    	
    	$user= $this->GetuserInfo();
    	$options=array("1"=>$tr->translate("Please_Select"));
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"));
    	}
    	else{
    		$sql.=" AND LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID ->setAttribs(array('class'=>'validate[required]'));
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array(
    			'id'=>'LocationId',
    			'Onchange'=>'AddLocation()',));
    	//$locationID->setValue($productValue);
    	$this->addElement($locationID);
    	    	
    	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowspayment) {
    		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($options_cg);
    	$this->addElement($paymentmethodElement);
    	
    	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_cur[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	}	 
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setAttribs(array('class'=>'demo-code-language'));
    	$currencyElement->setMultiOptions($options_cur);
    	$this->addElement($currencyElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($descriptionElement);
    	
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add'); 
    	$this->addElement($remarkaddElement);
    	
    	$vendoraddElement = new Zend_Form_Element_Textarea('vendor_address'); 
    	$vendoraddElement->setAttribs(array('placeholder' => 'Enter Vendor Address'));
    	$this->addElement($vendoraddElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($allTotalElement);
    	
    	$order_id = new Zend_Form_Element_Text('order_id');
    	$order_id->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($order_id);
    	
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discountTypeElement);    	
    	
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px',));
    	$this->addElement($discountRealElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	
    	$statusElement = new Zend_Form_Element_Select('status');
    	$opt_status = array(''=>'Please Select status',2=>'Open',3=>'In Progress',4=>'Paid',4=>'Recieved',-1=>'Cancel');
    	$statusElement->setMultiOptions($opt_status);
//     	$statusElement->setAttribs(array('1'=>'Active',));
//     	$statusElement->setValue("Open");
    	$this->addElement($statusElement);    	

    	$date_inElement = new Zend_Form_Element_Text('date_in');
    	$date =new Zend_Date();
    	$date_inElement ->setAttribs(array('class'=>'validate[required]'));
    	$date_inElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($date_inElement);
    	
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement ->setAttribs(array('class'=>'validate[required]','placeholder' => 'Click to Choose Date'));
    	$dateOrderElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($dateOrderElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	 
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);    	
    	
    	 
//     	$branchElement = new Zend_Form_Element_Text('branch');
//     	$branchElement->setAttribs(array('class'=>'validate[required]',));
//     	$this->addElement($branchElement);
    	 
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
//     	$dateElement = new Zend_Form_Element_Text('date');
//     	$this->addElement($dateElement);
    	
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	
    	$paid_date = new Zend_Form_Element_Text("paid_date_payment");
    	$paid_date ->setAttribs(array('class'=>'validate[required]'));
    	$paid_date->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($paid_date);
    	 
    	
//     	$receive_dateElement = new Zend_Form_Element_Text('recive_date');
//     	$this->addElement($receive_dateElement);
    	    	 
//     	$reqDateElement = new Zend_Form_Element_Text('rs-date');
//     	$this->addElement($reqDateElement);
    	 
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    	 
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$totalAmountElement = new Zend_Form_Element_Text('totalAmoun');
    	$totalAmountElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($totalAmountElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'custom[number]','onChange'=>'doRemain()'));
    	$this->addElement($paidElement);
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','date_in','offer_date','receiv_date','paid_date_payment'));
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			
    			$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    			$this->addElement($oldlocationIdElement);
    			
    			$idElement ->setValue($data["order_id"]);
    			$date_inElement->setValue($data["date_in"]);
    			$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    			$this->addElement($oldStatusElement);
    			$vendor_id->setValue($data["vendor_id"]);
    			$contactElement->setValue($data['contact_name']);
    			$phoneElement->setValue($data['phone']);
    			$remarkaddElement->setValue($data['add_name']);
    			
    			 if($data["status"]==1){
    			 	$statusElement->setValue("Quote");
    			 }
    			 elseif($data["status"]==2){
    			 	$statusElement->setValue("Open");
    			 }
    			 elseif($data["status"]==3){
    			 	$statusElement->setValue("In Progress");
    			 }
    			 elseif($data["status"]==4){
    			 	$statusElement->setValue("Paid");
    			 }
    			 else{
    			 	$statusElement->setValue("Cancel");
    			 }
    			//$idElement->setValue($data['id']);
    			
    			$oldStatusElement->setValue($data['status']);
    			$locationID->setvalue($data['LocationId']);
    			$oldlocationIdElement->setvalue($data['LocationId']);
    			$dateOrderElement->setValue($data["date_order"]);
    			$roder_element->setValue($data['order']);
    			$roder_element->setAttribs(array('readonly'=>'readonly'));
    			$paymentmethodElement->setValue($data['payment_method']);
    			$currencyElement->setValue($data['currency_id']);
    			$remarkElement->setValue($data["remark"]);
    			$paidElement->setValue($data['paid']);
    			$remainlElement->setvalue($data['balance']);
    			$allTotalElement->setValue($data['all_total']);
    			$discountValueElement->setValue($data['discount_value']);
    			$netTotalElement->setValue($data['net_total']);    			
    		
    		} else {$discountTypeElement->setValue(1);
    		}
     	return $this;
    
    
    }
    
    public function SalesOrder($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	
    	$rs=$db->getGlobalDb('SELECT customer_id, cust_name FROM tb_customer WHERE cust_name!="" AND is_active=1 ');
    	$customerValue = $request->getParam('users');
    	$options="";
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('ADD_CUSTOMER_ORDER'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	$customerID=new Zend_Form_Element_Select('customer_id');
    	$customerID ->setAttribs(array( 'id'=>'customer_id',
    			'Onchange'=>'getCustomerInfo()',
    			'class'=>'validate[required]'
    		//	validate[required]
    			));
    	$customerID ->setMultiOptions($options);
    	//$customerID->setattribs(array(id'=>'customer_id','onchange'=>'this.form.submit()',));
    	$customerID->setValue($customerValue);
    	$this->addElement($customerID);
    	
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($orderElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Contact Number'));
    	$this->addElement($phoneElement);
    	
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' AND status = 1 ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("ADD_NEW_LOCATION"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'Onchange'=>'AddLocation()',
    			'class'=>'validate[required]',
    			//validate[required]
    	 ));
    	$locationID->setMultiOptions($options);
    	
    	$locationID->setValue($productValue);
    	$this->addElement($locationID);  
    	    	
    	$rowsmethodpay= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowsmethodpay) {
    		foreach($rowsmethodpay as $readCategory) $option_method[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($option_method);
    	$this->addElement($paymentmethodElement);
    	
    	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_curr[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	}	 
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setMultiOptions($options_curr);
    	$this->addElement($currencyElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($descriptionElement);
    	
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add');
    	$this->addElement($remarkaddElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($allTotalElement);
    	
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discountTypeElement);    	
    	
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px',));
    	$this->addElement($discountRealElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$paidRealElement = new Zend_Form_Element_Text('paid');
    	$paidRealElement->setAttribs(array('class'=>'input100px','onChange'=>'Total1()',));
    	$this->addElement($paidRealElement);
    	
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	
    	$statusElement = new Zend_Form_Element_Select('status');
    	//$statusElement->setAttribs(array('readonly'=>'readonly',));
    	//$statusElement->setValue("Open");
    	$opt = array(""=>$tr->translate('Please_Select'),1=>$tr->translate("Qoute"),2=>$tr->translate("Open"),3=>$tr->translate("In Progress"),5=>$tr->translate("Paid"),6=>$tr->translate("Cancell"));
    	$statusElement->setMultiOptions($opt);
    	$this->addElement($statusElement);
    	
    	$statusquoteElement = new Zend_Form_Element_Text('quote');
    	$statusquoteElement->setAttribs(array('readonly'=>'readonly',));
    	$statusquoteElement->setValue("Quote");
    	$this->addElement($statusquoteElement);
    	
    	$date = new Zend_Date();
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement->setAttribs(array('placeholder' => 'Click To Choose Date','class'=>'validate[required]'));
    	$dateOrderElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($dateOrderElement);
    	 
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contace Name'));
    	$this->addElement($contactElement);
    	
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	 
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    	 
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	 
//     	$salesElement = new Zend_Form_Element_Text('sales_ref');
//     	$this->addElement($salesElement);
    	
//     	$rowsagent= $db->getGlobalDb('SELECT agent_id,name FROM tb_sale_agent ORDER BY agent_id DESC');
//     	$option_agent=array(''=>'Please Select','-1'=>'Add New Sale Agent');
//     	if($rowsagent) {
//     		foreach($rowsagent as $read_agent) $option_agent[$read_agent['agent_id']]=$read_agent['name'];
//     	}
//     	$sales_agentElement = new Zend_Form_Element_Select('sales_ref');
//     	$sales_agentElement->setMultiOptions($option_agent);
//     	$sales_agentElement->setAttribs(array('Onchange'=>'showAgentPopup()'));
//     	$this->addElement($sales_agentElement);  

    	$option="";
    	$sql = "SELECT agent_id,name FROM tb_sale_agent WHERE name!='' ";
    	$option=array(""=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Sale_Agent"));
    	if($user["level"]==1 OR $user["level"]== 2){
    		//$option=array(""=>"Please Select","-1"=>"Add New Sale Agent");
    	}
    	else{
    		//$option=array(""=>"Please Select");
    		//$sql.=" AND agent_id =".$user["location_id"];
    	}
    	$sql.=" ORDER BY agent_id DESC";
    	$rs=$db->getGlobalDb($sql);
    	$agent_value = $request->getParam('sales_ref');
    	if(!empty($rs)) foreach($rs as $read) $option[$read['agent_id']]=$read['name'];
    	$sales_agentId = new Zend_Form_Element_Select('sales_ref');
    	$sales_agentId->setAttribs(array(
    			'class'=>'validate[required]',
    			'id'=>'sales_ref' ,
    			'Onchange'=>'showAgentPopup()'));
        $sales_agentId->setMultiOptions($option);
    	$sales_agentId->setValue($agent_value);
    	$this->addElement($sales_agentId);
    	
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    	 
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    	 
    	 
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    	 
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement -> setAttribs(array('onChange'=>'doRemain()'));
    	$this->addElement($paidElement);
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','offer_date','paid_date'));
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement ->setValue($data["order_id"]);
    			
    			$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    			$this->addElement($oldlocationIdElement);
    		
    			$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    			$this->addElement($oldStatusElement);
    			$customerID->setValue($data["customer_id"]);
    			$contactElement->setValue($data['contact_name']);
    			$phoneElement->setValue($data['phone']);
    			    			
    			$remarkaddElement->setValue($data['add_name']);
    			 if($data["status"]==1){
    			 	$statusElement->setValue("Quote");
    			 }
    			 elseif($data["status"]==2){
    			 	$statusElement->setValue("Open");
    			 }
    			 elseif($data["status"]==3){
    			 	$statusElement->setValue("In Progress");
    			 }
    			 elseif($data["status"]==4){
    			 	$statusElement->setValue("Paid");
    			 }
    			 else{
    			 	$statusElement->setValue("Cancel");
    			 }
    			//$idElement->setValue($data['id']);
    			
    			$oldStatusElement->setValue($data['status']);
    			$sales_agentId->setValue($data['sales_ref']);
    			$locationID->setvalue($data['LocationId']);
    			$oldlocationIdElement->setvalue($data['LocationId']);
    			$dateOrderElement->setValue($data["date_order"]);
    			$orderElement->setAttribs(array("readonly"=>"readonly"));
    			$orderElement->setValue($data['order']);
    			$paymentmethodElement->setValue($data['payment_method']);
    			$currencyElement->setValue($data['currency_id']);
    			$remarkElement->setValue($data["remark"]);
    			$paidElement->setValue($data['paid']);
    			$remainlElement->setvalue($data['balance']);
    			$allTotalElement->setValue($data['all_total']);
    			$discountValueElement->setValue($data['discount_value']);
    			$netTotalElement->setValue($data['net_total']);  
    		
    		} else {$discountTypeElement->setValue(1);
    		}
     	return $this;
    
    }
    // ***********************************  Purchase Form *****************************************
    
    public function PurchaseOrder($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	 
    	$rs=$db->getGlobalDb('SELECT customer_id, cust_name FROM tb_customer WHERE cust_name!="" AND is_active=1 ');
    	$customerValue = $request->getParam('users');
    	$options="";
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('ADD_CUSTOMER_ORDER'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	$customerID=new Zend_Form_Element_Select('customer_id');
    	$customerID ->setAttribs(array( 'id'=>'customer_id',
    			'Onchange'=>'getCustomerInfo()',
    			'class'=>'validate[required]'
    			//	validate[required]
    	));
    	$customerID ->setMultiOptions($options);
    	//$customerID->setattribs(array(id'=>'customer_id','onchange'=>'this.form.submit()',));
    	$customerID->setValue($customerValue);
    	$this->addElement($customerID);
    	 
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($orderElement);
    	 
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Contact Number'));
    	$this->addElement($phoneElement);
    	 
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' AND status = 1 ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("ADD_NEW_LOCATION"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'Onchange'=>'AddLocation()',
    			'class'=>'validate[required]',
    			//validate[required]
    	));
    	$locationID->setMultiOptions($options);
    	 
    	$locationID->setValue($productValue);
    	$this->addElement($locationID);
    
    	$rowsmethodpay= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowsmethodpay) {
    		foreach($rowsmethodpay as $readCategory) $option_method[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($option_method);
    	$this->addElement($paymentmethodElement);
    	 
    	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_curr[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	}
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setMultiOptions($options_curr);
    	$this->addElement($currencyElement);
    	 
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($descriptionElement);
    	 
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add');
    	$this->addElement($remarkaddElement);
    	 
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($allTotalElement);
    	 
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discountTypeElement);
    	 
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	 
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	 
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px',));
    	$this->addElement($discountRealElement);
    	 
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	 
    	$paidRealElement = new Zend_Form_Element_Text('paid');
    	$paidRealElement->setAttribs(array('class'=>'input100px','onChange'=>'Total1()',));
    	$this->addElement($paidRealElement);
    	 
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	 
    	$statusElement = new Zend_Form_Element_Select('status');
    	//$statusElement->setAttribs(array('readonly'=>'readonly',));
    	//$statusElement->setValue("Open");
    	$opt = array(""=>$tr->translate('Please_Select'),1=>$tr->translate("Qoute"),2=>$tr->translate("Open"),3=>$tr->translate("In Progress"),5=>$tr->translate("Paid"),6=>$tr->translate("Cancell"));
    	$statusElement->setMultiOptions($opt);
    	$this->addElement($statusElement);
    	 
    	$statusquoteElement = new Zend_Form_Element_Text('quote');
    	$statusquoteElement->setAttribs(array('readonly'=>'readonly',));
    	$statusquoteElement->setValue("Quote");
    	$this->addElement($statusquoteElement);
    	 
    	$date = new Zend_Date();
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement->setAttribs(array('placeholder' => 'Click To Choose Date','class'=>'validate[required]'));
    	$dateOrderElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($dateOrderElement);
    
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contace Name'));
    	$this->addElement($contactElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	 
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    
    	//     	$salesElement = new Zend_Form_Element_Text('sales_ref');
    	//     	$this->addElement($salesElement);
    	 
    	//     	$rowsagent= $db->getGlobalDb('SELECT agent_id,name FROM tb_sale_agent ORDER BY agent_id DESC');
    	//     	$option_agent=array(''=>'Please Select','-1'=>'Add New Sale Agent');
    	//     	if($rowsagent) {
    	//     		foreach($rowsagent as $read_agent) $option_agent[$read_agent['agent_id']]=$read_agent['name'];
    	//     	}
    	//     	$sales_agentElement = new Zend_Form_Element_Select('sales_ref');
    	//     	$sales_agentElement->setMultiOptions($option_agent);
    	//     	$sales_agentElement->setAttribs(array('Onchange'=>'showAgentPopup()'));
    	//     	$this->addElement($sales_agentElement);
    
    	$option="";
    	$sql = "SELECT agent_id,name FROM tb_sale_agent WHERE name!='' ";
    	$option=array(""=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Sale_Agent"));
    	if($user["level"]==1 OR $user["level"]== 2){
    		//$option=array(""=>"Please Select","-1"=>"Add New Sale Agent");
    	}
    	else{
    		//$option=array(""=>"Please Select");
    		//$sql.=" AND agent_id =".$user["location_id"];
    	}
    	$sql.=" ORDER BY agent_id DESC";
    	$rs=$db->getGlobalDb($sql);
    	$agent_value = $request->getParam('sales_ref');
    	if(!empty($rs)) foreach($rs as $read) $option[$read['agent_id']]=$read['name'];
    	$sales_agentId = new Zend_Form_Element_Select('sales_ref');
    	$sales_agentId->setAttribs(array(
    			'class'=>'validate[required]',
    			'id'=>'sales_ref' ,
    			'Onchange'=>'showAgentPopup()'));
    	$sales_agentId->setMultiOptions($option);
    	$sales_agentId->setValue($agent_value);
    	$this->addElement($sales_agentId);
    	 
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    
    
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	 
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement -> setAttribs(array('onChange'=>'doRemain()'));
    	$this->addElement($paidElement);
    	 
    	Application_Form_DateTimePicker::addDateField(array('order_date','offer_date','paid_date'));
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement ->setValue($data["order_id"]);
    		 
    		$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    		$this->addElement($oldlocationIdElement);
    
    		$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    		$this->addElement($oldStatusElement);
    		$customerID->setValue($data["customer_id"]);
    		$contactElement->setValue($data['contact_name']);
    		$phoneElement->setValue($data['phone']);
    
    		$remarkaddElement->setValue($data['add_name']);
    		if($data["status"]==1){
    			$statusElement->setValue("Quote");
    		}
    		elseif($data["status"]==2){
    			$statusElement->setValue("Open");
    		}
    		elseif($data["status"]==3){
    			$statusElement->setValue("In Progress");
    		}
    		elseif($data["status"]==4){
    			$statusElement->setValue("Paid");
    		}
    		else{
    			$statusElement->setValue("Cancel");
    		}
    		//$idElement->setValue($data['id']);
    		 
    		$oldStatusElement->setValue($data['status']);
    		$sales_agentId->setValue($data['sales_ref']);
    		$locationID->setvalue($data['LocationId']);
    		$oldlocationIdElement->setvalue($data['LocationId']);
    		$dateOrderElement->setValue($data["date_order"]);
    		$orderElement->setAttribs(array("readonly"=>"readonly"));
    		$orderElement->setValue($data['order']);
    		$paymentmethodElement->setValue($data['payment_method']);
    		$currencyElement->setValue($data['currency_id']);
    		$remarkElement->setValue($data["remark"]);
    		$paidElement->setValue($data['paid']);
    		$remainlElement->setvalue($data['balance']);
    		$allTotalElement->setValue($data['all_total']);
    		$discountValueElement->setValue($data['discount_value']);
    		$netTotalElement->setValue($data['net_total']);
    
    	} else {$discountTypeElement->setValue(1);
    	}
    	return $this;
    
    }
	
}

