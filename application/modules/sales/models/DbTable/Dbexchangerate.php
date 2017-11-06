<?php

class Sales_Model_DbTable_Dbexchangerate extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_termcondition";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'con_khmer'		=>	$data["name_kh"],
				'con_english'	=>	$data["name_en"],
				'type'			=>	$data["type"],
				'status'		=>	$data["status"],
		);
		//$this->_name = "tb_category";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'key_value'		=>	$data["txt_value"],
		);
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllExchange(){
		$db = $this->getAdapter();
		$sql = "SELECT id,key_name,key_value FROM `tb_setting` WHERE code='exchange_rate' ";
		return $db->fetchAll($sql);
	}
	public function getTermById($id){
		$db = $this->getAdapter();
		$sql = "SELECT t.* FROM tb_termcondition AS t WHERE t.id= $id";
		return $db->fetchRow($sql);
	}
	
	public function getTermcondictionType(){
		$db = $this->getAdapter();
		$sql = "SELECT v.`key_code`,v.`name_en` FROM tb_view AS v WHERE v.`type`=12";
		return $db->fetchAll($sql);
	}
}