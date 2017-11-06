<?php

class Purchase_Model_DbTable_Dbexpensetitle extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_expensetitle";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'title'	=>	$data["title"],
				'title_en'	=>	$data["name_en"],
				'status'		=>	$data["status"],
				'date'		=>	date("Y-m-d"),
				'user_id'=>$this->getUserId()
		);
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'title'	=>	$data["title"],
				'title_en'	=>	$data["name_en"],
				'status'		=>	$data["status"],
				'date'		=>	date("Y-m-d"),
				'user_id'=>$this->getUserId()
		);
		//$this->_name = "tb_category";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	public function addajaxtitle($data){
		$db = $this->getAdapter();
		$arr = array(
				'title'	=>	$data["expense_title"],
				'status'		=>	1,
				'date'		=>	date("Y-m-d"),
				'user_id'=>$this->getUserId()
		);
		return $this->insert($arr);
	}
	public function getAllTerm(){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  t.id,
				  t.title,
				   t.title_en,
				  t.status	  
				FROM
				  tb_expensetitle AS t ORDER BY id desc ";
		return $db->fetchAll($sql);
	}
	public function getTermById($id){
		$db = $this->getAdapter();
		$sql = "SELECT t.* FROM tb_expensetitle AS t WHERE t.id= $id";
		return $db->fetchRow($sql);
	}
	public function getTermcondictionType(){
		$db = $this->getAdapter();
		$sql = "SELECT v.`key_code`,v.`name_en` FROM tb_view AS v WHERE v.`type`=10";
		return $db->fetchAll($sql);
	}
}