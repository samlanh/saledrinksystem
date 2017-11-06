<?php 
class Rsvacl_Form_FrmUser extends Zend_Form
{
	public function init($data=null)
    {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();	
    	$db=new Application_Model_DbTable_DbGlobal();

    	//user typefilter
		$sql = 'SELECT user_type_id,user_type FROM tb_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array('All User Type');
		$usertype = $request->getParam('user_type_filter');
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_filter=new Zend_Form_Element_Select('user_type_filter');
    	$user_type_filter->setMultiOptions($options);
    	$user_type_filter->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'validate[required] form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$user_type_filter->setValue($usertype);
    	$this->addElement($user_type_filter);
		
		$sql = 'SELECT p.`id`,p.`name` FROM `tb_sublocation` AS p';
		$rs=$db->getGlobalDb($sql);
		$options=array('All Location');
		$location_r = $request->getParam('location');
		foreach($rs as $read) $options[$read['id']]=$read['name'];
		$location=new Zend_Form_Element_Select('location');
    	$location->setMultiOptions($options);
    	$location->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'validate[required] form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$location->setValue($location_r);
    	$this->addElement($location);
		
		$options=array(''=>'All Status',1=>"ACTIVE",'0'=>"DEACTIVE");
		$status_r = $request->getParam('status_se');
		$status_se=new Zend_Form_Element_Select('status_se');
    	$status_se->setMultiOptions($options);
    	$status_se->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'validate[required] form-control',
    		'onchange'=>'this.form.submit()',
    	));
    	$status_se->setValue($status_r);
    	$this->addElement($status_se);
		
		$ad_search=new Zend_Form_Element_Text('ad_search');
    	$ad_search->setAttribs(array(
    		'id'=>'username',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($ad_search);

    	//uer title
    	$user_title = new Zend_Form_Element_Select("title");
    	$user_title->setAttribs(array('class'=>'form-control'));
    	$user_title->setMultiOptions(array("Mr"=>"Mr","Ms"=>"Ms"));
    	$this->addElement($user_title);

    	//user full name
    	$user_fullname = new Zend_Form_Element_Text("fullname");
    	$user_fullname->setAttribs(array(
    		'id'=>'fullname',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($user_fullname);
    	
    	//user name
    	$user_name=new Zend_Form_Element_Text('username');
    	$user_name->setAttribs(array(
    		'id'=>'username',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($user_name);
    	
    	//email
    	$email=new Zend_Form_Element_Text('email');
    	$email->setAttribs(array(
    		'id'=>'email',
    		'class'=>'validate[required] form-control centerRight',
    	));
    	$this->addElement($email);
    	 
    	
//password    	
    	$password=new Zend_Form_Element_Password('password');
    	$password->setAttribs(array(
    		'id'=>'password',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($password);
//confirm password    	
    	$confirm_password=new Zend_Form_Element_Password('confirm_password');
    	$confirm_password->setAttribs(array(
    		'id'=>'confirm_password',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($confirm_password);
    	
    	//user type
		$sql = 'SELECT user_type_id,user_type FROM tb_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array(''=>$tr->translate('Please_Select'));
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_id=new Zend_Form_Element_Select('user_type_id');		
    	$user_type_id->setMultiOptions($options);
    	$user_type_id->setAttribs(array(
    		'id'=>'user_type_id',
    		'class'=>'validate[required] form-control',
    	));
    	$this->addElement($user_type_id);
		
		$status = new Zend_Form_Element_Select("status");
    	$status->setAttribs(array('class'=>'form-control'));
    	$status->setMultiOptions(array("1"=>"Active","0"=>"Deactive"));
    	$this->addElement($status);

    	
    	//location 
    	$rs=$db->getGlobalDb('SELECT id, name FROM tb_sublocation WHERE name!="" AND status=1 ORDER BY id DESC');
    	$option =array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Location"));
    	if(!empty($rs)) foreach($rs as $read) $option[$read['id']]=$read['name'];
    	$locationID= new Zend_Form_Element_Select('LocationId');
    	$locationID->setMultiOptions($option);
    	$locationID->setattribs(array('id'=>'LocationId','Onchange'=>'AddLocation()','class'=>'form-control'));
    	$this->addElement($locationID);
    	
    	if($data!=null){
			$user_title->setValue($data["title"]);
			$user_fullname->setValue($data["fullname"]);
			$user_name->setValue($data["username"]);
			$email->setValue($data["email"]);
			$user_type_id->setValue($data["user_type_id"]);
			$status->setValue($data["status"]);
			$locationID->setValue($data["LocationId"]);
		}
    	
    	return $this;
    }
}
?>
