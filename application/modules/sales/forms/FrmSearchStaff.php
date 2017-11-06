<?php 
class Sales_Form_FrmSearchStaff extends Zend_Form
{
public function init()
	{
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$nameValue = $request->getParam('text_search');
		$nameElement = new Zend_Form_Element_Text('text_search');
		$nameElement->setAttribs(array(
				'class'=>'form-control',
				'placeholder'=>'Enter Keyword...'
				));
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		
		$startDateValue = $request->getParam('start_date');
		$endDateValue = $request->getParam('end_date');
		
		if($endDateValue==""){
			$endDateValue=date("m/d/Y");
		}
		
		$startDateElement = new Zend_Form_Element_Text('start_date');
		$startDateElement->setValue($startDateValue);
		$startDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
		$this->addElement($startDateElement);
		
		$options="";
		$sql = "SELECT id, name FROM tb_sublocation WHERE name!='' ";
		$sql.=" ORDER BY id DESC ";
		$rs=$db->getGlobalDb($sql);
		$options=array(0=>"Choose Branch");
		if(!empty($rs)) foreach($rs as $read) $options[$read['id']]=$read['name'];
		$locationID = new Zend_Form_Element_Select('branch_id');
		$locationID ->setAttribs(array('class'=>'validate[required] form-control select2me'));
		$locationID->setMultiOptions($options);
		$locationID->setattribs(array(
				'Onchange'=>'AddLocation()',));
		$this->addElement($locationID);
		
		$endDateElement = new Zend_Form_Element_Text('end_date');
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		$endDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker'
		));
		
		$opt_s = array("-1"=>"All",1=>"Active",0=>"Deactive");
		$status = new Zend_Form_Element_Select('status');
		$status->setattribs(array(
				'class'=>'form-control',));
		$status->setMultiOptions($opt_s);
		$status->setValue($request->getParam("status"));
		$this->addElement($status);
		
		
		
		
	}
}