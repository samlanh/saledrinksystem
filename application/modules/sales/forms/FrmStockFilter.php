<?php 
class Sales_Form_FrmStockFilter extends Zend_Form
{
	public function init()
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	/////////////Filter stock/////////////////
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$nameElement = new Zend_Form_Element_Text('s_name');
		$nameValue = $request->getParam('s_name');
		$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	$phonevalue = $request->getParam('phone');
    	$phoneElement = new Zend_Form_Element_Text('phone');
    	$phoneElement->setValue($phonevalue);
    	$this->addElement($phoneElement);
    	
    	$rs=$db->getGlobalDb('SELECT id,name FROM tb_sublocation WHERE name!="" ORDER BY id DESC ');
    	$options=array(''=>$tr->translate('Please_Select'));
    	$agentValue = $request->getParam('stock_location');
    	foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$sale_agent=new Zend_Form_Element_Select('stock_location');
    	$sale_agent->setMultiOptions($options);
    	$sale_agent->setAttribs(array(
    			'id'=>'LocationId',
    			//'onchange'=>'this.form.submit()',
    			'class'=>'demo-code-language'
    	));
    	$sale_agent->setValue($agentValue);
    	$this->addElement($sale_agent);
    	
	    return $this;
    }
}