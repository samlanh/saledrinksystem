<?php 
class Product_Form_FrmBranch extends Zend_Form
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
		
		$code = new Zend_Form_Element_Text("code");
		$code->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$prefix = new Zend_Form_Element_Text("prefix");
		$prefix->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$addres = new Zend_Form_Element_Textarea("address");
		$addres->setAttribs(array(
				'class'=>'form-control',
				'style'=>'height:59px',
				//'required'=>'required'
		));
		 
		$contact_name = new Zend_Form_Element_Text("contact");
		$contact_name->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		 
		$contact_num = new Zend_Form_Element_Text("contact_num");
		$contact_num->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$email = new Zend_Form_Element_Text("email");
		$email->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$fax = new Zend_Form_Element_Text("fax");
		$fax->setAttribs(array(
				'class'=>'form-control',
				
		));
		
		$office_num = new Zend_Form_Element_Text("office_num");
		$office_num->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text("remark");
		$remark->setAttribs(array(
				'class'=>'form-control',
		
		));
		
		$show_by = new Zend_Form_Element_Select("show_by");
		$show_by->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$opt_show = array('1'=>$tr->translate("SHOW_BY_TEXT"),'2'=>$tr->translate("SHOW_BY_LOGO"),'3'=>$tr->translate("SHOW_BY_ALL"));
		$show_by->setMultiOptions($opt_show);
		
		$logo = new Zend_Form_Element_File("logo");
    	$this->addElement($logo);
		$old_logo = new Zend_Form_Element_Hidden("old_logo");
    	$this->addElement($old_logo);
		
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
			$code->setValue($data["code"]);
			$prefix->setValue($data["prefix"]);
			$show_by->setValue($data["show_by"]);
			$old_logo->setValue($data["logo"]);
		}
			
		$this->addElements(array($show_by,$code,$prefix,$branch_name,$addres,$contact_name,$contact_num,$email,$fax,$office_num,$status,$remark));
		return $this;
	}
}