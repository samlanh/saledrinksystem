<?php 
class Sales_Form_Frmcustomercomment extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$db=new Application_Model_DbTable_DbGlobal();
		
		$customerid=new Zend_Form_Element_Select('customer_id');
		$customerid ->setAttribs(array(
				'class' => 'form-control select2me',
				'Onchange'=>'getCustomerInfo()',
		));
		$options = $db->getAllCustomer(1);
		$customerid->setMultiOptions($options);
		$this->addElement($customerid);
		
		$start_date=new Zend_Dojo_Form_Element_TextBox("start_date");
		$start_date->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker'));
		
		$start_date->setValue(date("m/d/Y"));
		
		$_description = new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array('dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'form-control',
				'required'=>1,
		         "style"=>'height:170px;'
		));
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		
		$id=new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'class'=>'form-control'));
		
		
		if(!empty($data)){
			$id->setValue($data['id']);
			$_description->setValue($data['comment']);
			$_status->setValue($data['status']);
			$start_date->setValue(date("m/d/Y",strtotime($data['date'])));
			$customerid->setValue($data['customer_id']);
		}
		$this->addElements(array($id,$start_date,$_status,$_description));
		return $this;
		
	}
	
}