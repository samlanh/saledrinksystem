<?php

class Rsvacl_UserController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
		$formfilter=new Rsvacl_Form_FrmUser();
		$this->view->formfilter=$formfilter;
    	$where = "";
		// action body
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getUser = new Rsvacl_Model_DbTable_DbUser();
              
        if($this->getRequest()->getParam('user_type_filter')){
			$user_type_id = $this->getRequest()->getParam('user_type_filter');
			$where=" where user_type_id=".$user_type_id;
		}
        $userQuery = "select `user_id`,fullname,`username`,
        (SELECT user_type FROM `tb_acl_user_type`  WHERE user_type_id=tb_acl_user.user_type_id) AS user_type,
        (SELECT name FROM `tb_sublocation` WHERE id=LocationId) AS branch_name,
        `created_date`,`modified_date`,`status` from tb_acl_user";
        $userQuery = $userQuery.$where;
        
        $rows = $getUser->getUserInfo($userQuery);
        if($rows){
        	$imgnone='<img src="'.BASE_URL.'/images/icon/none.png"/>';
        	$imgtick='<img src="'.BASE_URL.'/images/icon/tick.png"/>';
        	        	        	
        	foreach ($rows as $i =>$row){
        		if($row['status'] == 1){
        			$rows[$i]['status'] = $imgtick;
        		}
        		else{
        			$rows[$i]['status'] = $imgnone;
        		}
        	}
        	
        	$link = array("rsvacl","user","edit");
        	$links = array('username'=>$link,'fullname'=>$link);
        	
        	$list=new Application_Form_Frmlist();
        	$columns=array("FULL_NAME",$tr->translate('USER_NAME_CAP'),"USER_TYPE","BRANCH_NAME",$tr->translate('CREATED_DATE'),$tr->translate('MODIFIED_DATE'),$tr->translate('STATUS_CAP'));
        	$this->view->form=$list->getCheckList('radio', $columns, $rows, $links);
        	
        }else $this->view->form = $tr->translate('NO_RECORD_FOUND');
        Application_Model_Decorator::removeAllDecorator($formfilter);
    }
    
    public function viewAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new Rsvacl_Model_DbTable_DbUser();
    		$user_id = $this->getRequest()->getParam('id');
    		$rs=$db->getUser($user_id);
    		$this->view->rs=$rs;
    	}  	 
    	
    }
	public function addAction()
	{		
		if($this->getRequest()->isPost())
		{
			$db=new Rsvacl_Model_DbTable_DbUser();
			$post=$this->getRequest()->getPost();
			if(!$db->ifUserExist($post['username']))
			{
				$id=$db->insertUser($post);
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$this->_redirect('/rsvacl/user/index');
			}
			else {
				Application_Form_FrmMessage::message('User had existed already');
			}
		}
		$form= new Rsvacl_Form_FrmUser();
		$this->view->form=$form;
		Application_Model_Decorator::removeAllDecorator($form);
		
		$items = new Application_Model_GlobalClass();
		$locationRows = $items->getLocationAssign();
		$this->view->locations = $locationRows;
		
		$popup = new Application_Form_FrmPopup();
		$frm_poup = $popup->popuLocation(null);
		Application_Model_Decorator::removeAllDecorator($frm_poup);
		$this->view->popup_location = $frm_poup;
		
		
	}
	// Edit user
    public function editAction()
    {
    	$user_id=$this->getRequest()->getParam('id');
    	if(!$user_id)$user_id=0;
   		$form = new Rsvacl_Form_FrmUser();
    	$db = new Rsvacl_Model_DbTable_DbUser();
		$rs = $db->getUserInfo('SELECT * FROM tb_acl_user where user_id='.$user_id);
		Application_Model_Decorator::setForm($form, $rs);
		
    	$this->view->form = $form;
    	$this->view->user_id = $user_id;
    	
    	$rsloc = $db->getUserInfo('SELECT * FROM tb_acl_ubranch where user_id='.$user_id ." GROUP BY location_id ");
    	$this->view->branchname = $rsloc;
    	
    	$items = new Application_Model_GlobalClass();
    	$locationRows = $items->getLocationAssign();
    	$this->view->locations = $locationRows;
    	
    	if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			$db->updateUser($post,$user_id);
// 			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 			Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
// 			Application_Form_FrmMessage::redirector('/Rsvacl/user/index');
			$this->_redirect('/rsvacl/user/index');
		}
		Application_Model_Decorator::removeAllDecorator($form);
		
    }

 
    public function changepasswordAction()
	{
		$session_user=new Zend_Session_Namespace('auth');
		
		if($session_user->user_id==$this->getRequest()->getParam('id') OR $session_user->level == 1){
			$form = new Rsvacl_Form_FrmChgpwd();	
			$this->view->form=$form;
			
			if($this->getRequest()->isPost())
			{
				$db=new Rsvacl_Model_DbTable_DbUser();
				$user_id=$this->getRequest()->getParam('id');		
				if(!$user_id) $user_id=0;			
				$current_password=$this->getRequest()->getParam('current_password');
				$password=$this->getRequest()->getParam('password');
				if($db->isValidCurrentPassword($user_id,$current_password)){ 
					$db->changePassword($user_id, md5($password));	
					Application_Form_FrmMessage::message('Password has been changed');
					Application_Form_FrmMessage::redirector('/Rsvacl/user/'.$user_id);
				}else{
					Application_Form_FrmMessage::message('Invalid current password');
				}
			}		
		}else{ 
			Application_Form_FrmMessage::message('Access Denied!');
		    Application_Form_FrmMessage::redirector('/Rsvacl');	
		}
		
	}

}

