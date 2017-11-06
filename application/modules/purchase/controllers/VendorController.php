<?php
class Purchase_vendorController extends Zend_Controller_Action
{	
	const REDIRECT_URL ='/purchase';
	public function init()
	{
		/* Initialize action controller here */
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}else{
			$search =array(
					'text_search'=>'',
					'start_date'=>1,
					'end_date'=>date("Y-m-d"),
					'suppliyer_id'=>0,
					'status'	=>	-1,
					);
		}
		$db = new Purchase_Model_DbTable_DbVendor();
		$rows = $db->getAllVender($search);
		$columns=array("COMPANY_NAME","COMPANY_NUMBER","CON_NAME","CON_NUMBER","EMAIL_CAP","WEBSITE_CAP","ADDRESS_CAP","STATUS");
		$link=array(
				'module'=>'purchase','controller'=>'vendor','action'=>'edit',
		);
		$urlEdit = BASE_URL . "/purchase/vendor/edit";
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('v_name'=>$link,'phone_person'=>$link,'v_phone'=>$link,'contact_name'=>$link));
		
		$formFilter = new Application_Form_Frmsearch();
		$this->view->formFilter = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function addAction()
	{
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			try{
				$vendor = new Purchase_Model_DbTable_DbVendor();
				$vendor->addVendor($post);
				
				if(!empty($post['saveclose']))
				{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/vendor/index');
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		/////////////////for veiw form
		$formStock = new Purchase_Form_FrmVendor(null);
		$formStockAdd = $formStock->AddVendorForm(null);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
		//.end controller
	}
	public function editAction() {
		$db = new Purchase_Model_DbTable_DbVendor();
		if($this->getRequest()->isPost())
		{
			$post = $this->getRequest()->getPost();
			$db->addVendor($post);
			Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', self::REDIRECT_URL . '/vendor/index');
		}
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$row= $db->getvendorById($id);
		$this->view->is_over_sea = $row["is_over_sea"];
		$formStock = new Purchase_Form_FrmVendor();
		$formStockAdd = $formStock->AddVendorForm($row);
		Application_Model_Decorator::removeAllDecorator($formStockAdd);
		$this->view->form = $formStockAdd;
	}
	//for add vendor from purchase
	final function addvendorAction(){
		$post=$this->getRequest()->getPost();
		$db = new Purchase_Model_DbTable_DbVendor();
		$vid = $db->addnewvendor($post);
		$result = array('vid'=>$vid);
		echo Zend_Json::encode($result);
		exit();
	}
	
}