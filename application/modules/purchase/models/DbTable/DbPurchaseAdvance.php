<?php

class purchase_Model_DbTable_DbPurchaseAdvance extends Zend_Db_Table_Abstract
{	
	//get update order but not well
	public function getPurchaseOrder($post){//not yet use cos change table to tmp
		$db = $this->getAdapter();
		$sql = "SELECT p.item_name,od.order_id, od.pro_id, od.qty_order, od.price, od.total_befor, od.disc_type,
		od.disc_value, od.sub_total 
		FROM tb_purchase_order_item AS od
		LEFT JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id =".$post['purchase_order'];
		$row = $db->fetchAll($sql);
		return $row;
	}
	
	public function getPurchaseOrderExist($post){
		$db = $this->getAdapter();
		$id=$post['purchase_order'];
		$sql = "SELECT p.item_name,od.order_id, od.pro_id, SUM( qty_order ) AS qty_order  FROM tb_purchase_order_item_tmp AS od
		LEFT JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id = $id GROUP BY od.pro_id";
		$row = $db->fetchAll($sql);
		return $row;
	}
	public function getItemPurchaseExist($order_id,$item_id){//must group by Item
		$db = $this->getAdapter();
		$sql = "SELECT id,qty_order FROM tb_purchase_order_item_tmp WHERE order_id = ".$order_id." AND pro_id = ".$item_id." LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	
	//for get info received item 
	public function getProductReceived($id){
		$db= $this->getAdapter();
		$sql= "SELECT p.item_name, r.location_id, r.qty_order, r.qty_receive, r.qty_remain, r.receive_date
				FROM tb_product AS p
				INNER JOIN tb_purchase_order_receive AS r ON p.pro_id = r.pro_id
				WHERE purchase_order_id = $id";
		$row = $db->fetchAll($sql);
		return $row;
	}
	public function getStatusOrder($id){
		$db= $this->getAdapter();
		$sql = "SELECT h.history_id FROM tb_order_history as h WHERE h.order = ".$id;
		$row = $db->fetchAll($sql);
		return $row;
	}
	//for get item from tb_purchase order item tmp
	public function purchaseOrderTMPExist($purchase_order){
		$db = $this->getAdapter();
		$sql = "SELECT order_id, pro_id, sum(qty_order) as qty_order FROM tb_purchase_order_item_tmp WHERE order_id = $purchase_order GROUP BY pro_id";
		$row = $db->fetchAll($sql);
		return $row;
	}
}