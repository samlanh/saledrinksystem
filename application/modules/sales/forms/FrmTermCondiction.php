<?php 
class Sales_Form_FrmTermCondiction extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function Formterm($data=null) {
		$db = new Sales_Model_DbTable_DbTermCondiction();
		$rs_type = $db->getTermcondictionType();
		$name_en = new Zend_Form_Element_Textarea('name_en');
		$name_en->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Name EN',"style"=>"height:40px;"));
    	$this->addElement($name_en);
    	
    	$name_kh = new Zend_Form_Element_Textarea('name_kh');
    	$name_kh->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Enter Name KM',"style"=>"height:40px;"));
    	$this->addElement($name_kh);
    	
    	$opt= array('1'=>'Active',0=>'Deactive');
    	$status = new Zend_Form_Element_Select('status');
    	$status->setAttribs(array('class'=>'form-control select2me'));
    	$status->setMultiOptions($opt);
    	$this->addElement($status);
    	
    	$opt_type= array();
		if(!empty($rs_type)){
			foreach($rs_type AS $rs){
				$opt_type[$rs["key_code"]] = $rs["name_en"];
			}
		}
    	$type = new Zend_Form_Element_Select('type');
    	$type->setAttribs(array('class'=>'validate[required] form-control select2me'));
    	$type->setMultiOptions($opt_type);
    	$this->addElement($type);
    	 
    	if($data != null) {
    	   $name_en->setValue($data["con_english"]);
    	   $name_kh->setValue($data["con_khmer"]);
    	   $status->setValue($data["status"]);
    	   $type->setValue($data["type"]);
    	}
    	return $this;
	}
}