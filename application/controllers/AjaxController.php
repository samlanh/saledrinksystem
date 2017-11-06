<?php
class AjaxController extends Zend_Controller_Action
{ 
	public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    function changelangeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$session_lang=new Zend_Session_Namespace('lang');
    		$session_lang->lang_id=$data['lange'];
    		Application_Form_FrmLanguages::getCurrentlanguage($data['lange']);
    		print_r(Zend_Json::encode(2));
    		exit();
    	}
    }
    function getproductAction(){
    	if($this->getRequest()->isPost()){
    		try {
    			$post=$this->getRequest()->getPost();
    			$db = new Sales_Model_DbTable_Dbpos();
    			$rs =$db->getProductById($post['product_id'],$post['branch_id']);
    			print_r(Zend_Json::encode($rs));
    			exit();
    		}catch (Exception $e){
    			$result = array('err'=>$e->getMessage());
    			echo Zend_Json::encode($result);
    			exit();
    		}
    	}
    }
}