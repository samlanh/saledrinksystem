<?php

class sales_Model_DbTable_DbReturnQuery extends Zend_Db_Table_Abstract
{
	public function getCustomerInfoIn($id)//for get customer info return in
	{	
  		$db=$this->getAdapter();
		$sql = "SELECT 
					r.`return_id`,
					r.return_no,
					r.invoice_no,
					r.date_return,
					r.remark,
					r.all_total,
					c.`customer_id`,
					c.cust_name,
					c.add_name, 
					c.contact_name, 
					c.phone,
					l.Name,
					l.`LocationId`
				FROM tb_return_customer_in AS r, tb_return_customer_item_in AS ri,tb_customer AS c,tb_sublocation AS l
				WHERE l.LocationId=r.location_id AND r.return_id=ri.return_id AND r.return_id = ".$id." 
				AND c.customer_id = r.customer_id LIMIT 1";
		$rows=$db->fetchRow($sql);
		return $rows;
   }
   public function getReturnInItem($return_in){//for get product items of return in
	   	$db = $this->getAdapter();
	   	$sql="SELECT 
	   			p.`pro_id`,
	   			r.qty_return ,
	   			r.price,
	   			r.sub_total,
	   			r.return_remark,
	   			CONCAT(p.item_name,' (',p.item_code,' )') AS item_name
	   		  FROM tb_return_customer_item_in AS r ,tb_product AS p 
	   		  WHERE p.pro_id = r.pro_id AND r.return_id = $return_in";
	   	$rows = $db->fetchAll($sql);
   		return  $rows;
   }
   public function getReferenceInfo($return_id){//for get product items of return in
	   	$db = $this->getAdapter();
	   	$sql="SELECT 
				  ri.return_no,
				  ro.returnout_no,
				  ro.date_return_in,
				  ro.date_return_out,
				  ro.remark,
				  ro.all_total,
				  l.Name 
				FROM
				  tb_return_customer_out AS ro,
				  tb_return_customer_in AS ri,
				  tb_sublocation AS l 
				WHERE ri.return_id = ro.returnin_id 
				  AND l.LocationId = ro.location_id 
				  AND ro.returnout_id = ".$return_id." LIMIT 1";
	   	$rows = $db->fetchRow($sql);
   		return  $rows;
   }
   public function getReturnItemOut($id){
   		$db=$this->getAdapter();
   		$sql = "SELECT CONCAT(p.item_name,' (',p.item_code,' )') AS item_name, ri.qty_return, ri.price, ri.sub_total,ri.return_remark 
   		FROM  tb_return_customer_item_out AS ri,tb_product AS p
   		WHERE p.pro_id = ri.pro_id AND ri.return_id = $id";
   		$rows =$db->fetchAll($sql);
   		return $rows;
   }
   
   
	
}