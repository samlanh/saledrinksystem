<?php 
class Dailywork_Form_FrmContact extends Zend_Form
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
		$db = new Dailywork_Model_DbTable_DbContact();
		$row = $db->getCategoryname();
		$_name= new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
				
		));
		$_phone= new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
		
		));
		$_note= new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'form-control'
			,"style"=>'height:170px;'
		));
		
		$opt_u = array(''=>"select Category");
		if(!empty($row)){
			foreach ($row as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		
		//$_arr = array(1=>$this->tr->translate("WEBSITE"),2=>$this->tr->translate("SYSTEM"));
		$_category = new Zend_Dojo_Form_Element_FilteringSelect("category");
		$_category->setMultiOptions($opt_u);
		$_category->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		if(!empty($data)){
			//print_r($data); exit();
			$_name->setValue($data['contactname']);
			$_phone->setValue($data['phone']);
			$_note->setValue($data['note']);
			$_category->setValue($data['category']);
			
		}
		$this->addElements(array($_name,$_phone,$_note,$_category));
		return $this;
		
	}
	
}