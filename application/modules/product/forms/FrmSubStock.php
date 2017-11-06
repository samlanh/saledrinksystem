<?php 
class Product_Form_FrmSubStock extends Zend_Form
{
	// for input sub stock name 8/22/13
	public function init()
    {

	}
	public function orderSubstockForm($data=null) {
		$db=new Application_Model_DbTable_DbGlobal();		
			
		$nameElement = new Zend_Form_Element_Text('Stock Name');
		$nameElement->setAttribs(array('class'=>'validate[required]','placeholder'=>'Enter Stock Name'));
		$this->addElement($nameElement);
		 
		$contactElement = new Zend_Form_Element_Text('Contact Name');
		$contactElement->setAttribs(array('placeholder'=>'Enter Contact Name'));
		$this->addElement($contactElement);
		
		$phoneElement = new Zend_Form_Element_Text('Contact Number');
		$phoneElement->setAttribs(array('placeholder'=>'Enter Phone Number'));
		$this->addElement($phoneElement);

		$locationElement = new Zend_Form_Element_Textarea('Stock Location');
		$locationElement->setAttribs(array('placeholder'=>'Enter Stock Location'));
		$this->addElement($locationElement);
		
		$descriptionElement = new Zend_Form_Element_Textarea('description');
		$descriptionElement->setAttribs(array('placeholder'=>'Description Here...'));
		$this->addElement($descriptionElement);
		
		$optionsStatus=array(1=>"Active",2=>'Deactive');
		$statusElement = new Zend_Form_Element_Select('status');
		$statusElement->setMultiOptions($optionsStatus);
		$statusElement->setValue($data["status"]);
    	$statusElement->setAttribs(array('class'=>'demo-code-language',));
		$this->addElement($statusElement);
		//set value when edit
		if($data != null) {
			$idElement = new Zend_Form_Element_Hidden('id');
			$this->addElement($idElement);
			
			
			
			$idElement->setValue($data['LocationId']);
			$nameElement->setValue($data['Name']);
			$contactElement->setValue($data['contact']);
			$phoneElement->setValue($data['phone']);
			$locationElement->setValue($data['stock_add']);
			$descriptionElement->setValue($data['remark']);
		}
		return $this;
	}
	
	
}