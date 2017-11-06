<?php 
class Branch_Form_FrmBranch extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function branch($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$branch_name = new Zend_Form_Element_Text('branch_name');
		$branch_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$addres = new Zend_Form_Element_Textarea("address");
		$addres->setAttribs(array(
				'class'=>'form-control',
				'style'=>'height:59px',
				'required'=>'required'
		));
		 
		$contact_name = new Zend_Form_Element_Text("contact");
		$contact_name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$contact_num = new Zend_Form_Element_Text("contact_num");
		$contact_num->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$email = new Zend_Form_Element_Text("email");
		$email->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$fax = new Zend_Form_Element_Text("fax");
		$fax->setAttribs(array(
				'class'=>'form-control',
				
		));
		
		$office_num = new Zend_Form_Element_Text("office_num");
		$office_num->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text("remark");
		$remark->setAttribs(array(
				'class'=>'form-control',
		
		));
		
		if($data != null){
			$branch_name->setValue($data["name"]);
			$contact_name->setValue($data["contact"]);
			$contact_num->setValue($data["phone"]);
			$email->setValue($data["email"]);
			$fax->setValue($data["fax"]);
			$office_num->setValue($data["office_tel"]);
			$status->setValue($data["status"]);
			$remark->setValue($data["remark"]);
			$addres->setValue($data["address"]);
		}
			
		$this->addElements(array($branch_name,$addres,$contact_name,$contact_num,$email,$fax,$office_num,$status,$remark));
		return $this;
	}
}