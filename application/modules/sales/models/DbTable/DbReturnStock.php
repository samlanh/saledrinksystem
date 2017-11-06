<?php

class sales_Model_DbTable_DbReturnStock extends Zend_Db_Table_Abstract
{
	//for click payment
	public function getCustomerInfo($data)
	{
		 $db=$this->getAdapter();
			$sql = "SELECT * FROM tb_customer WHERE customer_id = ".$data['customer_id']." LIMIT 1";
			$row = $db->fetchRow($sql);
			return $row;
	} 
	public function getCustomerOrder($data){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM tb_sales_order_item WHERE order_id = ".$data['order_id'];
		$row = $db->fetchall($sql);
		return $row;
	}
	public function getCurrentQuantity($post){
		$db = $this->getAdapter();
		$sql="SELECT qty_order FROM tb_sales_order_item WHERE order_id = ".$post['order_id']." AND pro_id= ".$post['item_id']." LIMIT 1";
		$row=$this->fetchRow($sql);
		return $row;		
		
	}
	
	public function getPriceByCustomer($pro_id,$customer_id){
		$db=$this->getAdapter();
		$sql = " SELECT pr.price
		FROM tb_product_price pr, tb_customer c
		WHERE pr.type_price = c.type_price
		AND product_id = $pro_id AND c.customer_id = $customer_id LIMIT 1 ";
		$row = $db->fetchRow($sql);
		return $row;
	
	}
	
    final public function getCurrentPriceOld($data){//get by fore
    	$db=$this->getAdapter();
    	$sql="SELECT price FROM tb_product WHERE pro_id=".$data['item_id']." LIMIT 1";
    	$rows=$db->fetchRow($sql);
    	return $rows;
    }
    public function getQtyByItemId($pro_id,$qty){
    	$db=$this->getAdapter();
    	$sql="SELECT message ,qty_onhand FROM tb_qty_setting AS qp,`tb_product` AS p
    	WHERE ((qty_onhand-$qty)<=min_qty)
    	AND qp.pro_id=p.`pro_id` AND  qp.pro_id = $pro_id";
    	$sql.=" LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    public function getQtyWarningByItemId($pro_id,$location){
    	$db=$this->getAdapter();
    	$sql="SELECT 
				  pl.`ProLocationID`,
				  pl.`qty`,
				  pl.`qty_onsold`,
				  pl.`qty_warn`,
				  pl.qty_avaliable ,
				  pl.`pro_id`,
				 pl.`LocationId`
				FROM
				  tb_prolocation AS pl 
				WHERE pl.`pro_id`=$pro_id 
				 
				  AND pl.`LocationId`=$location";
    	$sql.=" LIMIT 1";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	
}