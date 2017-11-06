<?php

class Sales_Model_DbTable_DbTermCondiction extends Zend_Db_Table_Abstract
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
				'con_khmer'		=>	$data["name_kh"],
				'con_english'	=>	$data["name_en"],
				'type'			=>	$data["type"],
				'status'		=>	$data["status"],
		);
		//$this->_name = "tb_category";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllTerm(){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  t.id,
				  t.con_khmer,
				  t.con_english,
				  (SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=t.`type` AND v.type=12) AS `type`,
				  t.status
				  
				FROM
				  tb_termcondition AS t ";
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