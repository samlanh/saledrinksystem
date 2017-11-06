<?php

class Application_Form_FrmInclude extends Zend_Form
{

    public function init()
    {
       $db=new Application_Model_DbTable_DbGlobal();
    	
    }
    public function category($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();    	
    	$rowsCategory= $db->getGlobalDb('SELECT CategoryId, parent_id, Name 
    			FROM tb_category');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if($rowsCategory) {
    		foreach($rowsCategory as $readCategory) $options[$readCategory['CategoryId']]=$readCategory['Name'];
    	}
    	$cateElement = new Zend_Form_Element_Select('Parent Category');
    	$cateElement->setAttribs(array('class'=>'demo-code-language',));
    	$cateElement->setMultiOptions($options);
    	$this->addElement($cateElement);
    	
    	$catelistElement = new Zend_Form_Element_Text('Category Name');
    	$catelistElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($catelistElement);
    	
    	$optionsStatus=array(1=>"Active",2=>'Deactive');
    	$statusElement = new Zend_Form_Element_Select('status');
    	$statusElement->setAttribs(array('class'=>'demo-code-language',));
    	$statusElement->setMultiOptions($optionsStatus);
    	$this->addElement($statusElement);
    	
    	if($data!=null){
    	   $idElement = new Zend_Form_Element_Hidden('id');
    	   $this->addElement($idElement);
    	   $statusElement->setValue($data["IsActive"]);
           $idElement->setValue($data['CategoryId']);
           $cateElement->setValue($data['parent_id']);
           $catelistElement->setValue($data['Name']);
    	}   	
    	return $this; 
    }
    public function addBrand($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db=new Application_Model_DbTable_DbGlobal();
    	$rowsbrand= $db->getGlobalDb('SELECT branch_id, Name
    			FROM tb_branch WHERE Name!="" ');
    	$options=array(''=>$tr->translate('Please_Select'));
    	if($rowsbrand) {
    		foreach($rowsbrand as $readBrand) $options[$readBrand['branch_id']]=$readBrand['Name'];
    	}
    	$brandElement = new Zend_Form_Element_Select('Parent brand');
    	$brandElement->setAttribs(array('class'=>'demo-code-language',));
    	$brandElement->setMultiOptions($options);
    	$this->addElement($brandElement);
    	 
    	$b_nameElement = new Zend_Form_Element_Text('brand Name');
    	$b_nameElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($b_nameElement);
    	 
    	$optionsStatus=array(1=>$tr->translate("ACTIVE"),2=>$tr->translate('DEACTIVE'));
    	$statusElement = new Zend_Form_Element_Select('status');
    	$statusElement->setAttribs(array('class'=>'demo-code-language',));
    	$statusElement->setMultiOptions($optionsStatus);
    	$this->addElement($statusElement);
    	 
    	if($data!=null){
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$statusElement->setValue($data["IsActive"]);
    		$idElement->setValue($data['branch_id']);
    		$b_nameElement->setValue($data['Name']);
    		$brandElement->setValue($data['parent_id']);
    	}
    	return $this;
    }
    public function productOrder()
    {
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$nameElement = new Zend_Form_Element_Text('name');
    	$nameElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($nameElement);
    	
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$this->addElement($contactElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$this->addElement($phoneElement);
    	
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	
    	$vorderElement = new Zend_Form_Element_Text('Vorder');
    	$this->addElement($vorderElement);
    	
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    	
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);  

    	$dateElement = new Zend_Form_Element_Text('date');
    	$dateElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($dateElement);
    	
    	$statusElement = new Zend_Form_Element_Text('status');
    	$statusElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($statusElement);
    	
    	$shiaddElement = new Zend_Form_Element_Text('shipaddress');
    	$this->addElement($shiaddElement);  
    	
    	//////////////bottom purchase
    	$carrierElement = new Zend_Form_Element_Text('carrier');
    	$this->addElement($carrierElement);
    	
    	$taxschemeElement = new Zend_Form_Element_Text('taxscheme');
    	$this->addElement($taxschemeElement);
    	
    	$nvcElement = new Zend_Form_Element_Text('NVC');
    	$this->addElement($nvcElement);
    	
    	$currencyElement = new Zend_Form_Element_Text('currency');
    	$this->addElement($currencyElement);
    	
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    	
    	$remarkElement = new Zend_Form_Element_Text('remark');
    	$this->addElement($remarkElement);
    	
    	$freightElement = new Zend_Form_Element_Text('freight');
    	$this->addElement($freightElement);
    	
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    	
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    	
    	$totalElement = new Zend_Form_Element_Text('t$totalElement');
    	$this->addElement($totalElement);
    	
    	 	
    	
    	
    	//set value when edit
//     	if($data != null) {
//     		$idElement = new Zend_Form_Element_Hidden('id');
//     		$this->addElement($idElement);
    	
//     		$idElement->setValue($data['id']);
//     		$nameElement->setValue($data['name']);
//     		$statusElement->setValue($data['status']);
//     		$phoneElement->setValue($data['phone']);
//     		$emailElement->setValue($data['email']);
//     		$addressElement->setValue($data['address']);
//     		$descriptionElement->setValue($data['description']);
//     		$contactElement->setValue($data['contact_id']);
//     		$mainStockElement->setValue($data['main_stock_id']);
//     	} else {$statusElement->setValue(1);
//     	}
//     	return $this;
    
    }
    
    public function SalesOrder()
    {
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$nameElement = new Zend_Form_Element_Text('name');
    	$nameElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($nameElement);
    	 
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$this->addElement($contactElement);
    	 
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$this->addElement($phoneElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	 
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	 
    	$POElement = new Zend_Form_Element_Text('PO');
    	$this->addElement($POElement);
    	
    	$SalesRepElement = new Zend_Form_Element_Text('SalesRep');
    	$this->addElement($SalesRepElement);
    	 
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    	 
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	 
    	$statusElement = new Zend_Form_Element_Text('status');
    	$statusElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($statusElement);
    	 
    	$shiaddElement = new Zend_Form_Element_Text('shipaddress');
    	$this->addElement($shiaddElement);
    	 
    	//////////////bottom purchase
    	$invDateElement = new Zend_Form_Element_Text('invdate');
    	$this->addElement($invDateElement);
    	 
    	$dueDateElement = new Zend_Form_Element_Text('duedate');
    	$this->addElement($dueDateElement);
    	 
    	$nvcElement = new Zend_Form_Element_Text('NVC');
    	$this->addElement($nvcElement);
    	
    	$texschemeElement = new Zend_Form_Element_Text('texscheme');
    	$this->addElement($texschemeElement);
    	 
    	$currencyElement = new Zend_Form_Element_Text('currency');
    	$this->addElement($currencyElement);
    	 
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    	 
    	$remarkElement = new Zend_Form_Element_Text('remark');
    	$this->addElement($remarkElement);
    	 
    	$freightElement = new Zend_Form_Element_Text('freight');
    	$this->addElement($freightElement);
    	 
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    	 
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$this->addElement($paidElement);
    	
//    	for select
//     	$rowsContact = $db->getGlobalDb('SELECT id, name FROM rsmk_contact');
//     	$options=array(''=>'Please select');
//     	if($rowsContact) {
//     		foreach($rowsContact as $readContact) $options[$readContact['id']]=$readContact['name'];
//     	}
//     	$contactElement = new Zend_Form_Element_Select('contact_id');
//     	$contactElement->setMultiOptions($options);
//     	$this->addElement($contactElement);
    	
    	 
    	 
    	 
    	 
    	//set value when edit
    	//     	if($data != null) {
    	//     		$idElement = new Zend_Form_Element_Hidden('id');
    	//     		$this->addElement($idElement);
    	 
    	//     		$idElement->setValue($data['id']);
    	//     		$nameElement->setValue($data['name']);
    	//     		$statusElement->setValue($data['status']);
    	//     		$phoneElement->setValue($data['phone']);
    	//     		$emailElement->setValue($data['email']);
    	//     		$addressElement->setValue($data['address']);
    	//     		$descriptionElement->setValue($data['description']);
    	//     		$contactElement->setValue($data['contact_id']);
    	//     		$mainStockElement->setValue($data['main_stock_id']);
    	//     	} else {$statusElement->setValue(1);
    	//     	}
    	//     	return $this;
    
    }
	
}

