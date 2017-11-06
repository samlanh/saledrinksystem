<?php 
class purchase_Form_FrmVendorFilter extends Zend_Form
{
	public function init()
    {
    	//$session = new Zend_Session_Namespace('auth');
    	//$cso_id =$session->cso_id;
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	//$db=new Application_Model_DbTable_DbGlobal(); 
    	
    	/////////////Filter case hotline/////////////////
    	//case no 
    	$nameValue = $request->getParam('v_name');
		$nameElement = new Zend_Form_Element_Text('v_name');
		$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    }
}