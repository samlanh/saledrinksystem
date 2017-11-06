<?php

class Product_Model_DbTable_DbAddLocation extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sublocation";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	final public function  getSubLoation(){
		$db= $this->getAdapter();
		$sql="select LocationId, Name, stock_add, contact, phone,user_id, status,remark from tb_sublocation";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	
	public function saveSubStock($data) {
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$GetUserId= $session_user->user_id;
	
		$dataInfo = array(
				'Name' 			=> $data['StockName'],
				'user_id'  => $GetUserId,
				'last_mod_date' => new Zend_Date(),
				'contact' 		=> $data['ContactName'],
				'phone' 		=> $data['ContactNumber'],
				'stock_add' 	=> $data['StockLocation'],
				'status' 	=> $data['status'],
				'remark' 		=> $data['description']
		);
		$this->insert($dataInfo);
	}
	public function updateSubStock($data){
		$session_user=new Zend_Session_Namespace('auth');
		$GetUserId= $session_user->user_id;
		$data_stock = array(
				'Name' 			=> $data['StockName'],
				'user_id'       => $GetUserId,
				'last_mod_date' => new Zend_Date(),
				'contact' 		=> $data['ContactName'],
				'phone' 		=> $data['ContactNumber'],
				'stock_add' 	=> $data['StockLocation'],
				'status' 	    => $data['status'],
				'remark' 		=> $data['description']			
				);
		$where=$this->getAdapter()->quoteInto('LocationId=?',$data['id']);
		$this->update($data_stock,$where);
		
		//$db_global->updateRecord($data,$data["location_id"], "LocationId", "tb_sublocation");		
	}
	public function addBrand($data){
		$db= new Application_Model_DbTable_DbGlobal();
		$data_cate = array(
				"parent_id"	=> $data["parent_id"],
				"Name"		=> $data["branch_name"],
				"Timestamp"	=> new Zend_Date()
				);
		$result = $db->addRecord($data_cate, "tb_branch");
		return $result;
	}
	public function addCategory($data){
		$db= new Application_Model_DbTable_DbGlobal();
		$_arr = array(
				"parent_id"	=> $data["parent_id"],
				"Name"		=> $data["cate_name"],
				"Timestamp"	=> new Zend_Date()
		);
		$result = $db->addRecord($_arr, "tb_category");
		return $result;
	}
	public function addMeasure($data){
		$db= new Application_Model_DbTable_DbGlobal();
		$_arr = array(
				"measure_name"	=> $data["measure_name"],
		);
		$result = $db->addRecord($_arr, "tb_measure");
		return $result;
	}
	public function getCodeItem($p_code){
		$db=$this->getAdapter();
		$sql = "SELECT pro_id FROM tb_product WHERE p_code = '$p_code' LIMIT 1";
		$row= $db->fetchRow($sql);
		return $row;
	}
}