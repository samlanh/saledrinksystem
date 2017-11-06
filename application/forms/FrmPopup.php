<?php

class Application_Form_FrmPopup extends Zend_Form
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
    public function popuProduct($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$product_element= new Zend_Form_Element_Text("txt_name");
    	$product_element->setAttribs(array('placeholder' => 'Enter Product Name','class' => 'validate[required] form-control'));
    	$this->addElement($product_element);
    	
    	$code_element= new Zend_Form_Element_Text("item_code");
    	$code_element->setAttribs(array('placeholder' => 'Enter Product Code','class'=>'form-control'));
    	$this->addElement($code_element);
    	
    	$productname_element= new Zend_Form_Element_Text("product_name");
    	$productname_element->setAttribs(array('placeholder' => 'Enter Product Name','class' => 'validate[required] form-control'));
    	$this->addElement($productname_element);
    	
    	$price_element= new Zend_Form_Element_Text("price");
    	$price_element->setAttribs(array('placeholder' => 'Enter Product Price',"class"=>'form-control'));
    	$this->addElement($price_element);
    	
    	$item_price_element= new Zend_Form_Element_Text("item_price");
    	$item_price_element->setAttribs(array('placeholder' => 'Enter Product Price',"class"=>'form-control'));
    	$this->addElement($item_price_element);
    	
    	$remark_element= new Zend_Form_Element_Textarea("remark_order");
    	$remark_element->setAttribs(array('placeholder' =>'Product Description',"class"=>'form-control',"rows"=>3));
    	$this->addElement($remark_element);
    	
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_category WHERE name!="" AND status=1 ');
    	$options="";
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$cate_element=new Zend_Form_Element_Select('category_id');
    	$cate_element->setMultiOptions($options);
    	$cate_element->setAttribs(array(
    			'id'=>'category_id',
    			"class"=>'form-control select2me'
    	));
    	$this->addElement($cate_element);
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_brand WHERE name!="" AND status=1 ORDER BY id DESC ');
    	$options="";
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$branch_element=new Zend_Form_Element_Select('brand_id');
    	$branch_element->setMultiOptions($options);
    	$branch_element->setAttribs(array(
    			'id'=>'brand_id',
    			"class"=>'form-control select2me',
    	));
    	$this->addElement($branch_element);
    	
    	return $this;
    }
    public function popuLocation($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$stockname_element= new Zend_Form_Element_Text("StockName");
    	$stockname_element->setAttribs(array('placeholder' => 'Enter Stock Name','class' => 'validate[required] form-control'));
    	$this->addElement($stockname_element);
    	
    	$contact_element= new Zend_Form_Element_Text("ContactName");
    	$contact_element->setAttribs(array('placeholder' => 'Contact To','class' => 'validate[required] form-control'));
    	$this->addElement($contact_element);
    		
    	$phone_element= new Zend_Form_Element_Text("ContactNumber");
    	$phone_element->setAttribs(array('placeholder' => 'Contact Number ','class'=>'form-control'));
    	$this->addElement($phone_element);
    	
    	$address_element= new Zend_Form_Element_Text("location_add");
    	$address_element->setAttribs(array('placeholder'=>'Branch Location','class'=>'form-control'));
    	$this->addElement($address_element); 
    	
    	$description_element= new Zend_Form_Element_Textarea("description");
    	$description_element->setAttribs(array('placeholder'=>'Description Here...','class'=>'form-control','rows'=>3));
    	$this->addElement($description_element);
    	    	
    	return $this;
    }
    
    public function popupCustomer($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$customer_Element = new Zend_Form_Element_Text('txt_name');
    	$customer_Element ->setAttribs(array('placeholder' => 'Enter Customer Name','class' => 'validate[required]'));
    	$this->addElement($customer_Element); 

    	$contact_Element = new Zend_Form_Element_Text('txt_contact_name');
    	$contact_Element ->setAttribs(array('placeholder' => 'Enter Contact Name','class' => 'validate[required]'));
    	$this->addElement($contact_Element);
    	
    	$phone_Element = new Zend_Form_Element_Text('customer_phone');
    	$phone_Element ->setAttribs(array('placeholder' => 'Contact Number'));
    	$this->addElement($phone_Element);
    	
    	$address_Element = new Zend_Form_Element_Textarea('txt_address');
    	$address_Element ->setAttribs(array('placeholder' => 'Customer Address'));
    	$this->addElement($address_Element);
    	
    	$email_Element = new Zend_Form_Element_Text('txt_mail');
    	$email_Element ->setAttribs(array('placeholder' => 'Email Address'));
    	$this->addElement($email_Element);
    	
    	$options="";
    	$sql = " SELECT type_id, price_type_name FROM tb_price_type WHERE price_type_name!='' AND public=1 ";
    	$rs=$db->getGlobalDb($sql);
    	if(!empty($rs)) foreach($rs as $read) $options[$read['type_id']]=$read['price_type_name'];
    	$price_type = new Zend_Form_Element_Select('price_type');
    	$price_type ->setAttribs(array('class'=>'validate[required]'));
    	$price_type->setMultiOptions($options);
    	$price_type->setattribs(array('id'=>'price_type'));
    	$this->addElement($price_type);
    	
     	return $this;
    }
    public function popupVendor($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$vendor_Element = new Zend_Form_Element_Text('vendor_name');
    	$vendor_Element ->setAttribs(array('placeholder' => 'Enter Vendor Name','class' => 'form-control validate[required]'));
    	$this->addElement($vendor_Element); 
    	
    	$phone_Element = new Zend_Form_Element_Text('com_phone');
    	$phone_Element ->setAttribs(array('placeholder' => 'Contact Number','class'=>'form-control'));
    	$this->addElement($phone_Element);

    	$contact_Element = new Zend_Form_Element_Text('txt_contact_name');
    	$contact_Element ->setAttribs(array('placeholder' => 'Contact To','class' => 'form-control validate[required]'));
    	$this->addElement($contact_Element);
    	
    	$phone_Element = new Zend_Form_Element_Text('v_phone');
    	$phone_Element ->setAttribs(array('placeholder' => 'Contact Number','class'=>'form-control'));
    	$this->addElement($phone_Element);
    	
    	$address_Element = new Zend_Form_Element_Textarea('txt_address');
    	$address_Element ->setAttribs(array('placeholder' => 'Customer Address','rows'=>"3",'class'=>'form-control'));
    	$this->addElement($address_Element);

    	$email_Element = new Zend_Form_Element_Text('txt_mail');
    	$email_Element ->setAttribs(array('placeholder' => 'Email Address','class' => 'form-control validate[custom[email]]'));
    	$this->addElement($email_Element);
    	
    	$vendornote = new Zend_Form_Element_Text('vendor_note');
    	$vendornote ->setAttribs(array('placeholder' => 'Note','class' => 'form-control'));
    	$this->addElement($vendornote);
		return $this;
    }
    public function popupSaleAgent($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$agent_Element = new Zend_Form_Element_Text('agent_name');
    	$agent_Element ->setAttribs(array('placeholder' => 'Enter Agent Name','class' => 'validate[required]'));
    	$this->addElement($agent_Element);
    
    	$contact_Element = new Zend_Form_Element_Text('contact_phone');
    	$contact_Element ->setAttribs(array('placeholder' => 'Contact Phone','class' => 'validate[required]'));
    	$this->addElement($contact_Element);
    	 
    	$position_Element = new Zend_Form_Element_Text('positon');
    	$position_Element ->setAttribs(array('placeholder' => 'Enter Positon'));
    	$this->addElement($position_Element);
    	
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' ";
    	$user=$this->GetuserInfo();
    	if($user["level"]!=1 AND $user["level"]!= 2){
    		$sql.=" AND LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	//$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('brand_name');
    	$locationID ->setAttribs(array('class'=>'validate[required]'));
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array('id'=>'brand_name'));
    	$this->addElement($locationID);
    	
    	 
    	$address_Element = new Zend_Form_Element_Textarea('desc_agent');
    	$address_Element ->setAttribs(array('placeholder' => 'Description Here'));
    	$this->addElement($address_Element);
    
    	return $this;
    }
    public function popupBranch(){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$rowsbranch= $db->getGlobalDb('SELECT branch_id, Name FROM tb_branch WHERE Name!="" ORDER BY Name ');
    	$options = array(""=>"No Parent Branch");    	
    	if($rowsbranch) {
    		foreach($rowsbranch as $readbranch) $options[$readbranch['branch_id']]=$readbranch['Name'];
    	}
    	$branchElement = new Zend_Form_Element_Select('main_branch');
    	$branchElement->setMultiOptions($options);
    	$this->addElement($branchElement);
    	
    	$brand_Element = new Zend_Form_Element_Text('branch_name');
    	$brand_Element ->setAttribs(array('placeholder' => 'Enter Branch Name','class' => 'validate[required]'));
    	$this->addElement($brand_Element);
    	
    	return $this;
    }
    public function popupCategory(){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$rowscate= $db->getGlobalDb('SELECT id, name FROM tb_category WHERE name!="" ORDER BY id DESC ');
    	$options = array(""=>"No Parent Category");
    	if($rowscate) {
    		foreach($rowscate as $readcate) $options[$readcate['id']]=$readcate['name'];
    	}
    	$catelsElement = new Zend_Form_Element_Select('main_category');
    	$catelsElement->setMultiOptions($options);
    	$this->addElement($catelsElement);
    	 
    	$category_Element = new Zend_Form_Element_Text('cate_name');
    	$category_Element ->setAttribs(array('placeholder' => 'Enter Category Name','class' => 'validate[required]'));
    	$this->addElement($category_Element);
    	 
    	return $this;
    }
	public function AddClassPrice($data=null) {
			
	// 		$db=new Application_Model_DbTable_DbGlobal();
			$priceElement = new Zend_Form_Element_Text('price_name');
			$priceElement->setAttribs(array('class'=>'validate[required]',));
			$this->addElement($priceElement);
			
			$price_descElement = new Zend_Form_Element_Text('price_decs');
			$this->addElement($price_descElement);
			
			$optionsStatus=array(1=>"Active",2=>'Deactive');
			$statusElement = new Zend_Form_Element_Select('status');
			$statusElement->setMultiOptions($optionsStatus);
			$this->addElement($statusElement);
			
			 
			if($data != null) {
				
			}
			return $this;
		}
		public function popuMeasure($data=null) {
				
			$measure = new Zend_Form_Element_Text('measure_name');
			$measure->setAttribs(array('class'=>'validate[required]',));
			$this->addElement($measure);
				
			return $this;
		}	
}

