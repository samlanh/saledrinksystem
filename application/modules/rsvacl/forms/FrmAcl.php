<?php 
class RsvAcl_Form_FrmAcl extends Zend_Form
{
	public function init()
    {
//Module
    	$module=new Zend_Form_Element_Text('module');
    	$module->setAttribs(array(
    		'id'=>'module',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($module);
    	
//Controller
    	$controller=new Zend_Form_Element_Text('controller');
    	$controller->setAttribs(array(
    		'id'=>'controller',
    	    'class'=>'validate[required] form-control',
    	));
    	$this->addElement($controller);
    	
//Action
    	$action=new Zend_Form_Element_Text('action');
    	$action->setAttribs(array(
    		'id'=>'action',
    	 	'class'=>'validate[required] form-control',
    	));
    	$this->addElement($action);
    
	
	
//Label
    	$label=new Zend_Form_Element_Text('label');
    	$label->setAttribs(array(
    		'id'=>'label',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($label);
    	
//order by
    	$order=new Zend_Form_Element_Text('order');
    	$order->setAttribs(array(
    		'id'=>'order',
    	    'class'=>'validate[required] form-control',
    	));
    	$this->addElement($order);
		
//is menu	
		$is_menu = new Zend_Form_Element_Select("is_menu");
    	$is_menu->setAttribs(array('id'=>'is_menu','class'=>'form-control'));
    	$is_menu->setMultiOptions(array("1"=>"MENU","0"=>"NOT_MENU"));
    	$this->addElement($is_menu);		
		
		
//status	
		$status = new Zend_Form_Element_Select("status");
    	$status->setAttribs(array('id'=>'status','class'=>'form-control'));
    	$status->setMultiOptions(array("1"=>"Active","0"=>"Deactive"));
    	$this->addElement($status);
		
		
    }
	
			
	
	
}
?>