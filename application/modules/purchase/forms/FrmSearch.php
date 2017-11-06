<?php 
class purchase_Form_FrmSearch extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
	public function formSearch($data=null) {
		//$db=new Application_Model_DbTable_DbGlobal();
    	$nameElement = new Zend_Form_Element_Text('search_name');
    	$this->addElement($nameElement);
    	
    	$vendorElement = new Zend_Form_Element_Text('s_vednor');
    	$this->addElement($vendorElement); 	
    	
    	$submitElement = new Zend_Form_Element_Submit("refresh");
    	$this->addElement($submitElement);
    	return $this;
	}
	public function frmRetrunIn($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		//$db=new Application_Model_DbTable_DbGlobal();
		
		////////////////////////////////////////////////////////Purchase*****/////////////////////////////////////////////
		
		//get sales or purchase id text
		$returnOutValue = $request->getParam('invoice_in');
		$returnOutElement = new Zend_Form_Element_Text('invoice_in');
		$returnOutElement->setValue($returnOutValue);
		$this->addElement($returnOutElement);
		
		$outValue = $request->getParam('invoice_out');
		$returnInElement = new Zend_Form_Element_Text('invoice_out');
		$returnInElement->setValue($outValue);
		$this->addElement($returnInElement);
		
		$startDateValue = $request->getParam('search_start_date');
		$startDateElement = new Zend_Form_Element_Text('search_start_date');
		$startDateElement->setValue($startDateValue);
		$this->addElement($startDateElement);
			
		$endDateValue = $request->getParam('search_end_date');
		$endDateElement = new Zend_Form_Element_Text('search_end_date');
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		
		Application_Form_DateTimePicker::addDateField(array('search_start_date', 'search_end_date'));
		
		return $this;
	}

}