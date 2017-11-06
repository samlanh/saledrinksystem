<?php 
class Brand_Form_FrmBrand extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function Brand($data=null){
		$db = new Brand_Model_DbTable_DbBrand();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('cat_name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_Brand"));
		if(!empty($db->getAllBrand())){
			foreach ($db->getAllBrand() as $rs){
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
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
		
		if($data != null){
			$name->setValue($data["name"]);
			$parent->setValue($data["parent_id"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($parent,$name,$status,$remark));
		return $this;
	}
	
	public function BrandFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Brand_Model_DbTable_DbBrand();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control',
		));
		$name->setValue($request->getParam("name"));
		
// 		$parent = new Zend_Form_Element_Select("parent");
// 		$parent->setAttribs(array(
// 				'class'=>'form-control',
// 		));
// // 		$opt = array(''=>$tr->translate("SEELECT_Brand"));
// // 		if(!empty($db->getAllBrand(null))){
// // 			foreach ($db->getAllBrand() as $rs){
// // 				$opt[$rs["id"]] = $rs["name"];
// // 			}
// // 		}
// 		$parent->setMultiOptions($opt);
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		
		$this->addElements(array($name,$status));
		return $this;
	}
}