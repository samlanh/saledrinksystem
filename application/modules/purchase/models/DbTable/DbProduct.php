<?php

class Purchase_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{
	protected $_name = 'tb_product';
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	function addNewItem($post){
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user = new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$data=array(
					'cate_id'		=> 1,
					'type'			=> 2,
					'last_usermod'	=> $GetUserId,
					'last_mod_date'	=> new Zend_Date(),//test
					'item_name'		=> $post["pro_name"],
					'p_code'		=> $post["pro_code"],
					'cate_id'		=> $post["category_id"],//test
					'branch_id'	 	=> $post["brand_id"],//test
					'remark'		=> $post['remark']
			);
			$GetProductId = $db_global->addRecord($data, "tb_product");
			$dataproduct=array
			(
					'pro_id'     => $GetProductId,
					'LocationId' => 1,
					'qty'        => 0
			);
			//add qty to product location
			$db->insert("tb_prolocation", $dataproduct);
				
			$stockdata= array(
					'ProdId'           => $GetProductId,
					'QuantityOnHand'   => 0,
					'QuantityAvailable'=> 0,
					'Timestamp'        => new Zend_date()
			);
			$db->insert("tb_inventorytotal", $stockdata);
				
			$data_history = array(
					'transaction_type'  => 1,
					'pro_id'     		=> $GetProductId,
					'date'				=> new Zend_Date(),
					'location_id' 		=> 1,
					'Remark'			=> $post['remark'],
					'qty_edit'        	=> 0,
					'qty_before'        => 0,//qty have in recode table
					'qty_after'        	=> 0,
					'user_mod'			=> $GetUserId
			);
			$db->insert("tb_move_history", $data_history);
			$db->commit();
			return $GetProductId;
		}catch(Exception $e){
			$db->rollBack();
				
		}
	}
}