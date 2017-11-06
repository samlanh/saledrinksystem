<?php 
class Purchase_Form_FrmVendor extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function AddVendorForm($data=null) {
		$db=new Application_Model_DbTable_DbGlobal();
		
		$nameElement = new Zend_Form_Element_Text('txt_name');
		$nameElement->setAttribs(array('required'=>1,'class'=>'validate[required] form-control','placeholder'=>'Enter Vendor Name'));
    	$this->addElement($nameElement);
    	$vendor_phoneElement = new Zend_Form_Element_Text('v_phone');
    	$vendor_phoneElement->setAttribs(array('placeholder'=>'Enter Contact Number',"class"=>"form-control"));
    	$this->addElement($vendor_phoneElement);
    	
    	$contactElement = new Zend_Form_Element_Text('txt_contact_name');
    	$contactElement->setAttribs(array('placeholder'=>'Enter Contact Name',"class"=>"form-control"));
    	$this->addElement($contactElement);

    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder'=>'Enter Contact Number',"class"=>"form-control"));
    	$this->addElement($phoneElement);
    	
    	$contact_phone = new Zend_Form_Element_Text('contact_phone');
    	$contact_phone->setAttribs(array('placeholder'=>'Enter Contact Number',"class"=>"form-control"));
    	$this->addElement($contact_phone);
    	
    	$faxElement = new Zend_Form_Element_Text('txt_fax');
    	$faxElement->setAttribs(array('placeholder'=>'Enter Fax Number',"class"=>"form-control"));
    	$this->addElement($faxElement);
    	
    	$emailElement = new Zend_Form_Element_Text('txt_mail');
    	$emailElement->setAttribs(array('class'=>'validate[custom[email]] form-control','placeholder'=>'Enter Email Address'));
    	$this->addElement($emailElement);
    	
    	$websiteElement = new Zend_Form_Element_Text('txt_website');
    	$websiteElement->setAttribs(array('placeholder'=>'Enter Website Address',"class"=>"form-control"));
    	$this->addElement($websiteElement);
    	
    	///update 
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$remarkElement->setAttribs(array('placeholder'=>'Remark Here...',"class"=>"form-control","rows"=>3));
    	$this->addElement($remarkElement);
    	         
    	$addressElement = new Zend_Form_Element_Textarea('txt_address');
    	$addressElement->setAttribs(array('placeholder'=>'Enter Vendor Adress',"class"=>"form-control","rows"=>3));
    	$this->addElement($addressElement);
    	
    	$balancelement = new Zend_Form_Element_Text('txt_balance');
    	$balancelement->setValue("0.00");
    	$balancelement->setAttribs(array('readonly'=>'readonly',"class"=>"form-control"));
    	$this->addElement($balancelement); 

		$_stutas = new Zend_Form_Element_Select('status');
		$_stutas ->setAttribs(array(
				'class'=>' form-control',			
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$_stutas->setMultiOptions($options);
		$this->addElement($_stutas); 
    	
    	if($data != null) {
	       $idElement = new Zend_Form_Element_Hidden('id');
   		   $this->addElement($idElement);
    	   $idElement->setValue($data['vendor_id']);
    		
    	   $nameElement->setValue($data['v_name']);
    		$contactElement->setValue($data['contact_name']);
    		$addressElement->setValue($data["add_name"]);
    		$phoneElement->setValue($data['v_phone']);
    		$faxElement->setValue($data['fax']);
    		$emailElement->setValue($data['email']);
    		$websiteElement->setValue($data['website']);
    		$remarkElement->setValue($data['note']);
    		$contact_phone->setValue($data['phone_person']);
    		$balancelement->setValue($data['balance']);
			$_stutas->setValue($data['status']);
    	}
    	return $this;
	}
}