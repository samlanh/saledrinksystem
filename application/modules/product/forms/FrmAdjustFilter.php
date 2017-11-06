<?php 
class Product_Form_FrmAdjustFilter extends Zend_Form
{
	public function init()
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	/////////////Filter stock/////////////////
    	$nameValue = $request->getParam('s_name');
		$nameElement = new Zend_Form_Element_Text('s_name');
		$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	/////////////Date of lost item		/////////////////
    	$startDateValue = $request->getParam('search_start_date');
		$startDateElement = new Zend_Form_Element_Text('search_start_date');
		$startDateElement->setValue($startDateValue);
    	$this->addElement($startDateElement);
    	
    	$endDateValue = $request->getParam('search_end_date');
		$endDateElement = new Zend_Form_Element_Text('search_end_date');
		$endDateElement->setValue($endDateValue);
    	$this->addElement($endDateElement);

    	/////////////	Search subject in Lost Item		/////////////////
    	$subjectValue = $request->getParam('subject');
		$subjectElement = new Zend_Form_Element_Text('subject');
		$subjectElement->setValue($subjectValue);
    	$this->addElement($subjectElement);
    	
    	//Product Name 
    	$rs=$db->getGlobalDb('SELECT pro_id,item_name FROM tb_product');
		$productValue = $request->getParam('pro_id');
    	$options=array(''=>'Please Select Product');
		if(!empty($rs)) foreach($rs as $read) $options[$read['pro_id']]=$read['item_name'];
		$product_id=new Zend_Form_Element_Select('pro_id');
    	$product_id->setMultiOptions($options);
    	$product_id->setattribs(array(
    						'id'=>'pro_id',
    						'onchange'=>'this.form.submit()',
    						));
    	$product_id->setValue($productValue);
    	$this->addElement($product_id);
    	
    	//location
    	
    	$rs=$db->getGlobalDb('SELECT LocationId,Name FROM tb_sublocation ');
    	$productValue = $request->getParam('LocationId');
    	$options=array(''=>'Please Select Product');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$product_id=new Zend_Form_Element_Select('LocationId');
    	$product_id->setMultiOptions($options);
    	$product_id->setattribs(array(
    			'id'=>'LocationId',
    			'onchange'=>'this.form.submit()',
    	));
    	$product_id->setValue($productValue);
    	$this->addElement($product_id);
    	
    	//Vendor of purchase
    	if(!empty($session_stock->stockID)) {
	    	$session_stock=new Zend_Session_Namespace('stock');
	    	$rs=$db->getGlobalDb('SELECT id, name FROM rsmk_stock WHERE id!='.$session_stock->stockID);
			$providerValue = $request->getParam('stock_sale_id');
	    	$optionsProvider=array(''=>'Please Select Product');
			if(!empty($rs)) foreach($rs as $read) $optionsProvider[$read['id']]=$read['name'];
			$provider=new Zend_Form_Element_Select('stock_sale_id');
	    	$provider->setMultiOptions($optionsProvider);
	    	$provider->setattribs(array(
	    						'id'=>'stock_sale_id',
	    						'onchange'=>'this.form.submit()',
	    						));
	    	$provider->setValue($providerValue);
	    	$this->addElement($provider);
    	}
    	
    	//status of purchase
		$statusValue = $request->getParam('status');
    	$optionsStatus=array(''=>'Please Select',1=>'Created',2=>'Approve',3=>'Deliver',4=>'Cancel',5=>'Recieve Shipping');
		$status=new Zend_Form_Element_Select('status');
    	$status->setMultiOptions($optionsStatus);
    	$status->setattribs(array(
    						'id'=>'status',
    						'onchange'=>'this.form.submit()',
    						));
    	$status->setValue($statusValue);
    	$this->addElement($status);
    	
    	//Sale Agent
    	$rowAgents=$db->getGlobalDb('SELECT id, name FROM rsmk_sale_agent');
		$agentValue = $request->getParam('sale_agent_id');
    	$options=array(''=>'Please Select Sale Agent');
		if(!empty($rowAgents)) foreach($rowAgents as $rowAgent) $options[$rowAgent['id']]=$rowAgent['name'];
		$agent_id=new Zend_Form_Element_Select('sale_agent_id');
    	$agent_id->setMultiOptions($options);
    	$agent_id->setattribs(array(
    						'id'=>'sale_agent_id',
    						'onchange'=>'this.form.submit()',
    						));
    	$agent_id->setValue($agentValue);
    	$this->addElement($agent_id);
    	
    	//status of purchase
		$statusCOValue = $request->getParam('status');
    	$optionsCOStatus=array(''=>'Please Select',1=>'Request',2=>'Offer',3=>'Reject',4=>'Debt',5=>'Paid');
		$statusCO=new Zend_Form_Element_Select('status');
    	$statusCO->setMultiOptions($optionsCOStatus);
    	$statusCO->setattribs(array(
    						'id'=>'status',
    						'onchange'=>'this.form.submit()',
    						));
    	$statusCO->setValue($statusCOValue);
    	$this->addElement($statusCO);
    	
    	
    	Application_Form_DateTimePicker::addDateField(array('search_start_date', 'search_end_date'));
    }
}