<?php

class Product_Model_DbTable_DbItem extends Zend_Db_Table_Abstract
{

    protected $_name = 'rsmk_item';
    
    public function setName($name)
    {
		$this->_name=$name;
	}

	/**
	 * get item with specifec stock
	 * @param string $
	 */
	public function getItem($stock){
		$db = $this->getAdapter();
		$sql = "SELECT i.`id`, i.`name`
				FROM rsmk_item i
				INNER JOIN rsmk_stock_item si ON i.`id` = si.`item_id`
				WHERE si.`stock_id` = $stock";
		$rows = $db->fetchAll($sql);
		return $rows;
	}

}