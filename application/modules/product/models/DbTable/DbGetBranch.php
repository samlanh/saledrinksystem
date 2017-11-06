<?php

class Product_Model_DbTable_DbGetBranch extends Zend_Db_Table_Abstract
{
	public function getBranchbyUser($user_id){
		$db=$this->getAdapter();
		$sql=" SELECT name,location_id 
		FROM tb_sublocation AS l, tb_acl_ubranch AS ul
		WHERE  l.id = ul.location_id AND ul.user_id= $user_id AND name!='' ";
		$row=$db->fetchAll($sql);
		return $row;
		
	}
   
    
}