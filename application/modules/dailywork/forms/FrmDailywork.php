<?php 
class Dailywork_Form_FrmDailywork extends Zend_Form
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
		
		$db = new Dailywork_Model_DbTable_DbWorkcomplete();
		$row = $db->getUser();
		$db=new Dailywork_Model_DbTable_DbDailywork();
		$row_projct=$db->getProjectName();
		$_work= new Zend_Dojo_Form_Element_TextBox('work');
		$_work->setAttribs(array('missingMessage'=>'Invalid Module!','class'=>'form-control validate[required]'
		));
		
		$_description= new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array('dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'form-control',
		         "style"=>'height:170px;'
		));
		
		$opt_u = array(''=>"select user");
		if(!empty($row)){
			foreach ($row as $rs){
				$opt_u[$rs["id"]] = $rs["first_name"];
			}
		}
		$_user = new Zend_Dojo_Form_Element_FilteringSelect("user");
		$_user->setMultiOptions($opt_u);
		$_user->setAttribs(array(
				'class'=>'form-control select2me'));
		
		$opt_u=array(''=>"select Project");
		if (!empty($row_projct)){
			foreach ($row_projct as $rs){
				$opt_u[$rs["id"]]=$rs["projectname"];
			}
		}
		
		
		$_projectname=new Zend_Dojo_Form_Element_FilteringSelect("projectname");
		$_projectname->setMultiOptions($opt_u);
		$_projectname->setAttribs(array(
		
				'class'=>'form-control select2me'));
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'required'=>'true',
				'class'=>'form-control'));
		
		$start_date=new Zend_Dojo_Form_Element_TextBox("start_date");
		$start_date->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker'));
		$start_date->setValue(date("m/d/Y"));
		
		if(!empty($data)){
			$_work->setValue($data['work']);
			$_user->setValue($data['user']);
			$_description->setValue($data['description']);
			$_status->setValue($data['status']);
			$_projectname->setValue($data['projectname']);
			$start_date->setValue(date("m/d/Y",strtotime($data['date'])));
			$customerid->setValue($data['customer_id']);
		}
		$this->addElements(array($start_date,$_projectname,$_work,$_status,$_user,$_description));
		return $this;
		
	}
	
}