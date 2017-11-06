<?php

class purchase_Model_DbTable_DbSQLReturnItem extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
// 	static $_name="tb_return";
// 	public function setName($name){
// 		$this->_name=$name;
// 	}
	public function customerReturnInfo($id){
		$db=$this->getAdapter();
		$sql="SELECT 
				  v.customer_id,
				  v.cust_name,
				  v.phone,
				  v.add_name,
				  v.contact_name,
				  v.add_remark,
				  loc.Name,
				  r.return_id,
				  r.return_no,
				  r.date_return,
				  r.remark,
				  r.paid,
				  r.all_total,
				  r.balance,
				  loc.LocationId,
				  loc.Name 
				FROM
				  tb_return_customer_in AS r 
				  INNER JOIN tb_sublocation AS loc 
				    ON loc.LocationId = r.location_id 
				  INNER JOIN tb_customer AS v 
				    ON v.customer_id = r.customer_id 
				WHERE r.return_id = $id LIMIT 1 ";
		$rows = $db->fetchRow($sql);
		return $rows;
	}
	public function returnInfo($id){
		$db=$this->getAdapter();
		$sql = "SELECT v.vendor_id,v.v_name,v.phone,v.add_name,v.contact_name,v.add_remark,loc.Name,
		r.return_id,r.return_no,r.date_return,
		r.remark,r.paid,r.all_total,r.balance,loc.LocationId,loc.Name
		FROM tb_return AS r
		INNER JOIN tb_sublocation AS loc ON loc.LocationId=r.location_id
		INNER JOIN tb_vendor AS v ON v.vendor_id= r.vendor_id
		WHERE r.return_id= ".$id." LIMIT 1";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	
	public function returnVendorInfoIn($id){
		$db=$this->getAdapter();
		$sql = "SELECT ri.returnin_id, ri.returnin_no,ri.`returnout_id`, ri.date_return_in,ri.`date_return_out`,ri.`vendor_id`, ri.location_id, ri.remark, ri.all_total,ro.return_no,loc.Name
		FROM tb_return_vendor_in AS ri,tb_return AS ro,tb_sublocation AS loc
		WHERE loc.LocationId=ri.location_id AND ro.return_id = ri.returnout_id AND ri.returnin_id = ".$id." LIMIT 1";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	
	public function getReturnItem($id){
		$db = $this->getAdapter();
// 		$sql = "SELECT CONCAT(p.item_name,' (',p.item_code,' )') AS item_name, ri.return_id, ri.pro_id, ri.qty_return, ri.price, ri.sub_total , ri.return_remark
// 		FROM tb_return_vendor_item AS ri
// 		INNER JOIN tb_product AS p ON p.pro_id=ri.pro_id WHERE ri.return_id = ".$id;
		$sql ="
			SELECT 
			  CONCAT(p.item_name,' (',p.item_code,' )') AS item_name,
			  ri.return_id,
			  ri.pro_id,
			  ri.qty_return,
			  ri.price,
			  ri.sub_total,
			  ri.return_remark,
			  rt.`location_id` 
			FROM
			  tb_return_vendor_item AS ri,
			  tb_return AS rt,
			  tb_product AS p 
			WHERE p.pro_id = ri.pro_id 
			  AND ri.`return_id` = rt.`return_id`
			 AND ri.return_id =".$id
				;
		$row = $db->fetchAll($sql);
		return $row;
	}
	public function getReturnItemIn($id){
		$db = $this->getAdapter();
		// 		$sql = "SELECT CONCAT(p.item_name,' (',p.item_code,' )') AS item_name, ri.return_id, ri.pro_id, ri.qty_return, ri.price, ri.sub_total , ri.return_remark
		// 		FROM tb_return_vendor_item AS ri
		// 		INNER JOIN tb_product AS p ON p.pro_id=ri.pro_id WHERE ri.return_id = ".$id;
		$sql ="
			SELECT
			  CONCAT(p.item_name,' (',p.item_code,' )') AS item_name,
			  ri.return_id,
			  ri.pro_id,
			  ri.qty_return,
			  ri.price,
			  ri.sub_total,
			  ri.return_remark,
			  rt.`location_id`
			FROM
			  tb_return_vendor_item_in AS ri,
			  tb_return_vendor_in AS rt,
			  tb_product AS p
			WHERE p.pro_id = ri.pro_id
			  AND ri.`return_id` = rt.`returnin_id`
			 AND ri.return_id =".$id;
			$row = $db->fetchAll($sql);
			return $row;
	}
}