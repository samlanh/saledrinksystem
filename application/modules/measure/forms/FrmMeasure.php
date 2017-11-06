<?php 
class Measure_Form_FrmMeasure extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function measure($data=null){
		$db = new Measure_Model_DbTable_DbMeasure();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('measure_name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
// 		$parent = new Zend_Form_Element_Select("parent");
// 		$parent->setAttribs(array(
// 				'class'=>'form-control',
// 		));
// 		$opt = array(''=>$tr->translate("SEELECT_Measure"));
// 		if(!empty($db->getAllMeasure())){
// 			foreach ($db->getAllMeasure() as $rs){
// 				$opt[$rs["id"]] = $rs["name"];
// 			}
// 		}
// 		$parent->setMultiOptions($opt);
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
		
		if($data != null){
			$name->setValue($data["name"]);
			//$parent->setValue($data["parent_id"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($name,$status,$remark));
		return $this;
	}
	
	public function MeasureFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Measure_Model_DbTable_DbMeasure();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_Measure"));
		$row = $db->getAllMeasure();
		if(!empty($row)){
			foreach ($row as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$this->addElements(array($parent,$name,$status));
		return $this;
	}
}