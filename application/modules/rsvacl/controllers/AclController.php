<?php
class Rsvacl_AclController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
        // action body    	
    	//$this->_helper->layout()->disableLayout();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getAcl = new Rsvacl_Model_DbTable_DbAcl();
        $aclQuery = "SELECT `acl_id`,`module`,`controller`,`action`,`status` FROM tb_acl_acl";
        $rows = $getAcl->getAclInfo($aclQuery);
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
        	
        	$list=new Application_Form_Frmlist();
        	$columns=array($tr->translate('MODULE'),$tr->translate('CONTROLLER'),$tr->translate('ACTION'), $tr->translate('STATUS'));
        	
        	$link = array("rsvAcl","acl","view-acl");
        	$links = array('module'=>$link,'controller'=>$link,"action"=>$link);
        	
        	$this->view->form=$list->getCheckList('radio', $columns, $rows, $links );
        	
        }else $this->view->form = $tr->translate('NO_RECORD_FOUND');
    }
    
    public function viewAclAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new Rsvacl_Model_DbTable_DbAcl();
    		$acl_id = $this->getRequest()->getParam('id');
    		$rs=$db->getAcl($acl_id);
    		$this->view->rs=$rs;
    	}  	 
    	
    }
	public function addAction()
		{
			$form = new Rsvacl_Form_FrmAcl();
			$this->view->form=$form;
			
			if($this->getRequest()->isPost())
			{
				$db=new Rsvacl_Model_DbTable_DbAcl();
				$post=$this->getRequest()->getPost();
				$id=$db->insertAcl($post);
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 				Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
// 				Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');
			}
		}
    public function editAclAction()
    {	
    	$acl_id=$this->getRequest()->getParam('id');
    	if(!$acl_id)$acl_id=0;  
   		$form = new RsvAcl_Form_FrmAcl();
    	$db = new RsvAcl_Model_DbTable_DbAcl();
        $rs = $db->getUserInfo('SELECT * FROM tb_acl_acl where acl_id='.$acl_id);
		Application_Model_Decorator::setForm($form, $rs);
    	$this->view->form = $form;
    	$this->view->acl_id = $acl_id;
    	if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			if($rs[0]['action']==$post['action']){
					$db->updateAcl($post,$rs[0]['acl_id']);
					$tr = Application_Form_FrmLanguages::getCurrentlanguage();
					Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
					Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');
			}else{
				if(!$db->isActionExist($post['action'])){
					$db->updateAcl($post,$rs[0]['acl_id']);
					$tr = Application_Form_FrmLanguages::getCurrentlanguage();
					Application_Form_FrmMessage::message($tr->translate('ROW_AFFECTED'));
					Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');
				}else {
					Application_Form_FrmMessage::message('Action had existed already');
				}
			}
		}
    }
}