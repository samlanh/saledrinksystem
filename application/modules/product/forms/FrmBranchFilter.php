<?php 
class Product_Form_FrmBranchFilter extends Zend_Form
{
	public function init(){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	/////////////Filter Product/////////////////
    	
    }
    public function branchFilter(){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$branch_name = new Zend_Form_Element_Text('branch_name');
    	$branch_name->setAttribs(array(
    		'class'=>'form-control'
    	));
    	$branch_name->setValue($request->getParam("branch_name"));
    	
    	$addres = new Zend_Form_Element_Text("address");
    	$addres->setAttribs(array(
    			'class'=>'form-control'
    	));
    	
    	$contact_name = new Zend_Form_Element_Text("contact");
    	$contact_name->setAttribs(array(
    			'class'=>'form-control'
    	));
    	
    	$contact_num = new Zend_Form_Element_Text("contact_num");
    	$contact_num->setAttribs(array(
    			'class'=>'form-control'
    	));
    	$status = new Zend_Form_Element_Select("status");
    	$status->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
    	$status->setMultiOptions($opt);
    	$status->setValue($request->getParam("status"));
    	
    	$this->addElements(array($branch_name,$addres,$contact_name,$contact_num,$status));
    	return $this;
    }
}