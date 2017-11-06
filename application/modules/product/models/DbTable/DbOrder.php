<?php

class Product_Model_DbTable_DbOrder extends Zend_Db_Table_Abstract
{
    
    
    
    public function setName($name)
    {
    	//$this->_name=$name;
    }
    
    public function getItemInfoByID($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT p.`id`, p.`name`, p.`purchase_code`, v.`name` as vendor, p.`order_date`, p.`description`,
    	c.`name` as assign, p.`discount_type`, p.`discount_value`, s.`name` as shipping,
    	p.`shipping_charge`, p.`status`, p.`net_total`, p.`all_total`
    	FROM rsmk_purchase p
    	LEFT JOIN rsmk_vendor v ON p.`vendor_id` = v.`id`
    	LEFT JOIN rsmk_contact c ON p.`assign_contact` = c.`id`
    	LEFT JOIN rsmk_shipping s ON p.`shipping_id` = s.`id`
    	WHERE p.`id` = $id";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    public function GetProductLocation($id){
    	$db = $this->getAdapter();
    	$itemSql = "SELECT * from tb_prolocation where pro_id = $id";
    	$rows = $db->fetchAll($itemSql);
    	return $rows;
    }
    
    
}