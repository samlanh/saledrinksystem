<?php
class report_UserController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
		
    }
   
	public function userlistAction()
    {
		$formfilter=new Rsvacl_Form_FrmUser();
		$this->view->formfilter=$formfilter;
    	$where = "";
		// action body
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getUser = new Rsvacl_Model_DbTable_DbUser();
         $where='';
        if($this->getRequest()->getParam('user_type_filter')){
			$user_type_id = $this->getRequest()->getParam('user_type_filter');
			$where.=" AND u.user_type_id=".$user_type_id;
		}
		if($this->getRequest()->getParam('location')){
			$location = $this->getRequest()->getParam('location');
			$where.=" AND u.LocationId=".$location;
		}
		//if($this->getRequest()->getParam('status')){
			$status = $this->getRequest()->getParam('status_se');
			//echo "adae".$status;
			if($status!=""){
				$where.=" AND u.status="."'".$status."'";
			}
		//}
		if($this->getRequest()->getParam('ad_search')){
			$ad_search = $this->getRequest()->getParam('ad_search');
			$s_where=array();
			$s_search = addslashes(trim($ad_search));
			$s_where[]= " u.fullname LIKE '%{$s_search}%'";
			$s_where[]=" u.username LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
        $userQuery = "SELECT u.`user_id`,u.fullname,u.`username`,
        (SELECT user_type FROM `tb_acl_user_type`  WHERE user_type_id=u.user_type_id) AS user_type,
        (SELECT NAME FROM `tb_sublocation` WHERE id=u.LocationId) AS branch_name,
		(SELECT v.name_en FROM tb_view AS v WHERE v.key_code=u.`status` AND v.type=5) AS `status`,
        u.`created_date`,u.`modified_date` FROM tb_acl_user AS u WHERE 1 ";
		//echo $userQuery.$where;
		$order =" ORDER BY u.status DESC";
        $userQuery = $userQuery.$where.$order;
        
        $rows = $getUser->getUserInfo($userQuery);
		$this->view->rs = $rows;
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
	
	 public function saleagentAction()
    {
    	if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'text_search'=>'',
					'start_date'=>1,
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'status'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_DbSalesAgent();
		$rows= $db->getAllSaleAgent($search);
		$this->view->rs = $rows;
        $list = new Application_Form_Frmlist();
    	$columns=array("BRANCH_NAME","AGENT_CODE","SALE_AGENT","CONTACT_NUM","EMAIL","ADDRESS","POSTION","START_WORKING_DATE","DESC_CAP","STATUS");
    	$link=array(
    		'module'=>'sales','controller'=>'saleagent','action'=>'edit',
    	);
    	$urlEdit = BASE_URL . "/sales/saleagent/edit";
    	$glClass = new Application_Model_GlobalClass();
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('branch_name'=>$link,'name'=>$link,'phone'=>$link));
    	
    	$formFilter = new Sales_Form_FrmSearchStaff();
    	$this->view->formFilter = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function usertypeAction()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $getUser = new Rsvacl_Model_DbTable_DbUserType();
        $userQuery = "SELECT u.user_type_id,u.user_type,(SELECT u1.user_type FROM `tb_acl_user_type` u1 WHERE u1.user_type_id = u.parent_id LIMIT 1) parent_id FROM `tb_acl_user_type` u";
        $rows = $getUser->getUserTypeInfo($userQuery);
		$this->view->rs = $rows;
        if($rows){
        	$link = array("rsvacl","usertype","edit");
        	$links = array('user_type'=>$link);
        	$list=new Application_Form_Frmlist();
        	$columns=array($tr->translate('USER_TYPE_CAP'), $tr->translate('TYPE_OF_CAP'));
        	$this->view->form=$list->getCheckList('radio', $columns, $rows, $links);
        }else $this->view->form = $tr->translate('NO_RECORD_FOUND');
    }
	public function locationAction()
    {
		$db = new Product_Model_DbTable_DbBranch();
		$formFilter = new Product_Form_FrmBranchFilter();
		$frmsearch = $formFilter->branchFilter();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'branch_name'	=>	'',
					'status'	=>	1
			);
		}
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllBranch($data);
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
}