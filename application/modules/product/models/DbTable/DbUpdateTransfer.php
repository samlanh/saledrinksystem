<?php

class Product_Model_DbTable_DbUpdateTransfer extends Zend_Db_Table_Abstract
{
// 	protected $_name = "tb_stocktransfer";
// 	public function setName($name)
// 	{
// 		$this->_name=$name;
// 	}
	public function updateTransferStockTransaction($post){
		$db=$this->getAdapter();
		$session_user = new Zend_Session_Namespace('auth');
		$userName  = $session_user->user_name;
		$GetUserId = $session_user->user_id;
	
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		if($post['from_location']!== $post['to_location']){
				$id_update = $post["transfer_id"];
			   $data_transfer=array(
									'transfer_date' => $post['transfer_date'],
									'from_location'	=> $post['from_location'],
									'to_location'	=> $post['to_location'],
									'user_id' 		=> $GetUserId,
									'mod_date'		=> new Zend_Date(),
									'remark'	    => $post['remark_transfer']
							       );
				//$transfer_id = $db_global->addRecord($data_transfer, "tb_stocktransfer");
				$db_global->updateRecord($data_transfer, $id_update, "transfer_id", "tb_stocktransfer");
			    unset($data_transfer);
			    $sql_item="SELECT st.transfer_id, ti.pro_id, SUM( ti.qty ) AS qty
							FROM tb_stocktransfer AS st, tb_transfer_item AS ti, tb_product AS p
							WHERE ti.transfer_id = st.transfer_id
							AND p.pro_id = ti.pro_id
							AND st.transfer_id = $id_update
							GROUP BY ti.pro_id ";
			    $rows_transfer=$db_global->getGlobalDb($sql_item);
			    if($rows_transfer){
			    	foreach ($rows_transfer as $row_qty){
			    		//for from location id
			    		$rows = $db_global -> porductLocationExist($row_qty['pro_id'], $post['old_from_location']);
			    		if($rows){
			    			//update poduct location from
			    			$data_qty_location=array(
			    					'qty' 			=> $rows['qty'] + $row_qty['qty'],
			    					'last_usermod'	=> $GetUserId,
			    					'last_mod_date' => new Zend_Date()
			    			);
			    			$db_global->updateRecord($data_qty_location, $rows['ProLocationID'], "ProLocationID","tb_prolocation");
			    			unset($data_qty_location);unset($rows);
			    		}
			    		//for to location
			    		$row = $db_global ->porductLocationExist($row_qty['pro_id'], $post['old_to_location']);
			    		if($row){
			    			//update poduct location from
			    			$data_qty_location=array(
			    					'qty' 			=> $row['qty']- $row_qty['qty'],
			    					'last_usermod'	=> $GetUserId,
			    					'last_mod_date' => new Zend_Date()
			    			);
			    			$db_global->updateRecord($data_qty_location, $row['ProLocationID'], "ProLocationID","tb_prolocation");
			    			unset($data_qty_location);unset($row);
			    		}
			    		
			  		 }
			  		 
			   }
			   unset($rows_transfer);
			   $sql= "DELETE FROM tb_transfer_item WHERE transfer_id IN ($id_update)";
			   $db_global->deleteRecords($sql);
			   $identity  = explode(',',$post['identity']);
				foreach($identity as $i){
				 				$data_item=array(
									'transfer_id'	 => $id_update,
									'pro_id'		 => $post['item_id_'.$i],
				 					'qty'			 => $post['qty_id_'.$i],
				 					'remark_transfer'=> $post['remark_'.$i]
								 );
			 				    $db->insert("tb_transfer_item", $data_item);
				 				unset($data_item);
	
				$rows = $db_global ->porductLocationExist($post['item_id_'.$i], $post['from_location']);
				if($rows){
					//update poduct location from
					$data_qty_location=array(
							'qty' =>$rows['qty']- $post['qty_id_'.$i]
					);
					$db_global->updateRecord($data_qty_location, $rows['ProLocationID'], "ProLocationID","tb_prolocation");
						
					//add move history
					$data_history = array
					(
							'transaction_type'  => 2,
							'pro_id'     		=> $post['item_id_'.$i],
							'date'				=> new Zend_Date(),
							'location_id' 		=> $post['from_location'],
							'Remark'			=> $post['remark_'.$i],
							'qty_edit'        	=> $post['qty_id_'.$i],
							'qty_before'        => $rows['qty'],
							'qty_after'        	=> $rows['qty']- $post['qty_id_'.$i],
							'user_mod'			=> $GetUserId
					);
					$db->insert("tb_move_history", $data_history);
					unset($data_qty_location);unset($rows);
					//update product location to
					$rows_gets_qty=$db_global -> porductLocationExist($post['item_id_'.$i], $post['to_location']);
	
					if($rows_gets_qty){
						$data_qty_location=array(
								'qty' =>$rows_gets_qty['qty']+ $post['qty_id_'.$i]
						);
						$db_global->updateRecord($data_qty_location, $rows_gets_qty['ProLocationID'], "ProLocationID","tb_prolocation");
						//add move history
						$data_history = array
						(
								'transaction_type'  => 2,
								'pro_id'     		=> $post['item_id_'.$i],
								'date'				=> new Zend_Date(),
								'location_id' 		=> $post['to_location'],
								'Remark'			=> $post['remark_'.$i],//can't add remark cos short table in form
								'qty_edit'        	=> $post['qty_id_'.$i],
								'qty_before'        => $rows_gets_qty['qty'],
								'qty_after'        	=> $rows_gets_qty['qty']+ $post['qty_id_'.$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($rows_gets_qty);
					}//if recieve deosn't exist in product location
					else{
						$add_pro_location = array(
								'pro_id'        => $post['item_id_'.$i],
								'LocationId'    => $post['to_location'],
								'qty'           => $post['qty_id_'.$i],
								'last_usermod'  => $GetUserId,
								'last_mod_date' => new Zend_Date()
						);
						$db->insert("tb_prolocation", $add_pro_location);
						//if receive not have
						$data_history = array
						(
								'transaction_type'  => 2,
								'pro_id'     		=> $post['item_id_'.$i],
								'date'				=> new Zend_Date(),
								'location_id' 		=> $post['to_location'],
								'Remark'			=> $post['remark_'.$i],
								'qty_edit'        	=> $post['qty_id_'.$i],
								'qty_before'        => 0,
								'qty_after'        	=> $post['qty_id_'.$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($data_history);unset($add_pro_location);
					}
				}
				else{//if from doesn't exist
					//add qty in location if from doesn't exist
					$add_pro_location = array(
							'pro_id'        => $post['item_id_'.$i],
							'LocationId'    => $post['from_location'],
							'qty'           => -$post['qty_id_'.$i],
							'last_usermod'  => $GetUserId,
							'last_mod_date' => new Zend_Date()
					);
					$db->insert("tb_prolocation", $add_pro_location);
					unset($add_pro_location);
					//echeck for get product location
					$data_history = array
					(
							'transaction_type'  => 1,
							'pro_id'     		=> $post['item_id_'.$i],
							'date'				=> new Zend_Date(),
							'location_id' 		=> $post['from_location'],
							'Remark'			=> $post['remark_'.$i],
							'qty_edit'        	=> $post['qty_id_'.$i],
							'qty_after'        	=> -$post['qty_id_'.$i],
							'user_mod'			=> $GetUserId
					);
					$db->insert("tb_move_history", $data_history);
					unset($data_history);
						
					//for get stock
					$rows_gets_qty=$db_global -> porductLocationExist($post['item_id_'.$i], $post['to_location']);
					if($rows_gets_qty){
						$data_qty_location=array(
								'qty' =>$rows_gets_qty['qty']+ $post['qty_id_'.$i]
						);
						$db_global->updateRecord($data_qty_location, $rows_gets_qty['ProLocationID'], "ProLocationID","tb_prolocation");
						//add move history
						$data_history = array
						(   	'transaction_type'  => 2,
								'pro_id'     		=> $post['item_id_'.$i],
								'date'				=> new Zend_Date(),
								'location_id' 		=> $post['to_location'],
								'Remark'			=> $post['remark_'.$i],
								'qty_edit'        	=> $post['qty_id_'.$i],
								'qty_before'        => $rows_gets_qty['qty'],
								'qty_after'        	=> $rows_gets_qty['qty']+ $post['qty_id_'.$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($data_history);
					}//if recieve deosn't exist in product location
					else{ //if doesn't exist from and to
						$add_pro_location = array(
								'pro_id'        => $post['item_id_'.$i],
								'LocationId'    => $post['to_location'],
								'qty'           => $post['qty_id_'.$i],
								'last_usermod'  => $GetUserId,
								'last_mod_date' => new Zend_Date()
						);
						$db->insert("tb_prolocation", $add_pro_location);
						//if doesn't exist from and to
						$data_history = array
						(
								'transaction_type'  => 1,
								'pro_id'     		=> $post['item_id_'.$i],
								'date'				=> new Zend_Date(),
								'location_id' 		=> $post['to_location'],
								'Remark'			=> $post['remark_'.$i],
								'qty_edit'        	=> $post['qty_id_'.$i],
								'qty_after'        	=> $post['qty_id_'.$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($add_pro_location);unset($data_history);
					}
				}
			}//forforeach
		}//for if
	}
	public function transferExist($id){
		$db= $this->getAdapter();
		$sql = "SELECT transfer_id FROM tb_stocktransfer WHERE transfer_id = ".$id." LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	//echeck if transfer  user location exist
	public function transferUserExist($id,$location){
		$db= $this->getAdapter();
		$sql = "SELECT transfer_id FROM tb_stocktransfer WHERE transfer_id = ".$id." AND to_location = ".$location." LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
}