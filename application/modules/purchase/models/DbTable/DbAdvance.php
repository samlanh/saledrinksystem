<?php

class purchase_Model_DbTable_DbAdvance extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_purchase_order_receive";
	public function setName($name)
	{
		$this->_name=$name;
	}
	//check if purchase_order have in table_purchase order tmp 
	public function purchaseTMPExist($post){
		$db= $this->getAdapter();
		$sql="SELECT id FROM tb_purchase_order_item_tmp 
		WHERE order_id =".$post['purchase_order'];
		$row=$db->fetchAll($sql);
		if(!$row) return false;
		return true;
	}
	public function addReveivPurchaseOrder($post){
		$db=$this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
		$advance = new purchase_Model_DbTable_DbPurchaseAdvance();
		$session_user = new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		
		try {
		$data =array(
				"purchase_order_id"	=>	$post["purchase_id"],
				"vendor_id"			=>	$post["v_id"],
				"location_id"		=>	$post["location_id"],
				"pro_id"			=>	$post["pro_id"],
				"qty_order"			=>	$post["qty_order"],
				"qty_receive"		=>	$post["qty_receive"],
				"qty_remain"		=>	$post["qty_remain"],
				"receive_date"		=>	$post["reveive_date"],
				"user_id"			=> $GetUserId,
				"mod_date"			=>	new Zend_Date()				
				);
		$db->insert("tb_purchase_order_receive", $data);
 		$rows=$db_global->inventoryLocation($post["location_id"], $post["pro_id"]);
		if($rows)
		{
			$qty_on_order = array(
					"QuantityOnHand"    => $rows["QuantityOnHand"] + $post["qty_receive"],
					"QuantityAvailable" => $rows["QuantityAvailable"] + $post["qty_receive"] ,
					"QuantityOnOrder"   => $rows["QuantityOnOrder"] - $post["qty_receive"],
					"Timestamp"			=> new zend_date()		
			);
			//update total stock
			$db_global->updateRecord($qty_on_order,$post["pro_id"],"ProdId","tb_inventorytotal");
			unset($qty_on_order);
			$updatedata=array(
					'qty' 				=> $rows["qty"]+$post["qty_receive"],
					"last_usermod"		=> $GetUserId,
					"last_mod_date"		=> new Zend_Date()
					);
			//update stock product location
 			$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
			unset($updatedata);
			$row_get = $advance-> getItemPurchaseExist($post["purchase_id"], $post["pro_id"]);
			if($row_get){
				$data_update = array(
						"qty_order" => $row_get["qty_order"]-$post["qty_receive"],
						);
				$db_global->updateRecord($data_update,$row_get["id"],"id","tb_purchase_order_item_tmp");
			}
		}///not yet cos double add product location if select change location order
		else{
			$insertdata=array(
						'pro_id'     => $post["pro_id"],
						'LocationId' => $post["location_id"],
						'qty'        => $post["qty_receive"]
				);
			//update stock product location
			$db->insert("tb_prolocation", $insertdata);
			unset($insertdata);
			//update tmp purchase order
			$row_get = $advance-> getItemPurchaseExist($post["purchase_id"], $post["pro_id"]);
			if($row_get){
				$data_update = array(
						"qty_order" => $row_get["qty_order"]-$post["qty_receive"],
				);
				$db_global->updateRecord($data_update,$row_get["id"],"id","tb_purchase_order_item_tmp");
			}
			
			$rows_stock=$db_global->InventoryExist($post["pro_id"]);
				if($rows_stock){
					$dataInventory= array(
							'ProdId'            => $post["pro_id"],
							'QuantityOnHand'    => $rows_stock["QuantityOnHand"]+ $post["qty_receive"],
							'QuantityOnOrder'   => $rows_stock["QuantityOnOrder"]- $post["qty_receive"],
							'QuantityAvailable' => $rows_stock["QuantityAvailable"]+$post["qty_receive"],
							'Timestamp'         => new Zend_date()
					);
					$db_global->updateRecord($dataInventory,$rows_stock["ProdId"],"ProdId","tb_inventorytotal");
					unset($dataInventory);
				}
				else{
					$addInventory= array(
							'ProdId'            => $post["pro_id"],
							'QuantityOnHand'    => $post["qty_receive"],
							'QuantityAvailable' => $post["qty_receive"],
							'Timestamp'         => new Zend_date()
					);
					$db->insert("tb_inventorytotal", $addInventory);
					unset($addInventory);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			$e->getMessage();
			
		}
	}
	public function receivedCompleted($data){
	    $db_global = new Application_Model_DbTable_DbGlobal();
		$advance = new purchase_Model_DbTable_DbPurchaseAdvance();
		$session_user = new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$rows_tmp =$advance-> purchaseOrderTMPExist($data['purchase_order']);
		if($rows_tmp){
					
				foreach ($rows_tmp as $post){
					//add info received item 
					$data_receive =array(
							"purchase_order_id"	=>	$data['purchase_order'],
							//							"vendor_id"			=>	$post["v_id"],
							"location_id"		=>	$post["qty_order"],
							"pro_id"			=>	$post["pro_id"],
							"qty_order"			=>	$post["qty_order"],
							"qty_receive"		=>	$post["qty_order"],
							"qty_remain"		=>	0,
							"receive_date"		=>	new Zend_Date(),
							"user_id"			=>  $GetUserId,
							"mod_date"			=>	new Zend_Date()
					);
					$db->insert("tb_purchase_order_receive", $data_receive);
					
 					$rows=$db_global->inventoryLocation($data["location_id"], $post["pro_id"]);
					if($rows)
					{
						$qty_on_order = array(
								'QuantityOnHand'    => $rows["QuantityOnHand"]+$post["qty_order"],
								'QuantityOnOrder'   => $rows["QuantityOnOrder"]-$post["qty_order"],
								'QuantityAvailable' => $rows["QuantityAvailable"]+$post["qty_order"],
								'Timestamp'         => new Zend_date()
						);
// 						//update total stock
 						$db_global->updateRecord($qty_on_order,$post["pro_id"],"ProdId","tb_inventorytotal");
// 						unset($qty_on_order);
						$updatedata=array(
								'qty' 				=> $rows["qty"] + $post["qty_order"],
								"last_usermod"		=> $GetUserId,
								"last_mod_date"		=> new Zend_Date()
						);
						//update stock product location
						$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
						unset($updatedata);
						$row_get = $advance-> getItemPurchaseExist($data['purchase_order'], $post["pro_id"]);
						if($row_get){
							$data_update = array(
									"qty_order" => $row_get["qty_order"]-$post["qty_order"],
							);
							$db_global->updateRecord($data_update,$row_get["id"],"id","tb_purchase_order_item_tmp");
							unset($data_update);
						}
					}///not yet cos double add product location if select change location order
 					else{
// 						$insertdata=array(
// 								'pro_id'     => $post["pro_id"],
// 								'LocationId' => $data["location_id"],
// 								'qty'        => $post["qty_order"]
// 						);
// 						//update stock product location
// 						$db->insert("tb_prolocation", $insertdata);
// // 						unset($insertdata);
// // 						//update tmp purchase order
// 						$row_get = $advance-> getItemPurchaseExist($data["purchase_id"], $post["pro_id"]);
// 						if($row_get){
// 							$data_update = array(
// 									"qty_order" => $row_get["qty_order"]-$post["qty_order"],
// 							);
// 							$db_global->updateRecord($data_update,$row_get["id"],"id","tb_purchase_order_item_tmp");
// 							unset($data_update);
// 						}	
// 						$rows_stock=$db_global->InventoryExist($post["pro_id"]);
// 						if($rows_stock){
// 							$dataInventory= array(
// 									'QuantityOnHand'    => $rows_stock["QuantityOnHand"]+ $post["qty_order"],
// 									'QuantityOnOrder'   => $rows_stock["QuantityOnOrder"]- $post["qty_order"],
// 									'QuantityAvailable' => $rows_stock["QuantityAvailable"]+$post["qty_order"],
// 									'Timestamp'         => new Zend_date()
// 							);
// 							$db_global->updateRecord($dataInventory,$rows_stock["ProdId"],"ProdId","tb_inventorytotal");
// 							unset($dataInventory);
//     					}
// 						else{
// 							$addInventory= array(
// 									'ProdId'            => $post["pro_id"],
// 									'QuantityOnHand'    => $post["qty_order"],
// 									'QuantityAvailable' => $post["qty_order"],
// 									'Timestamp'         => new Zend_date()
// 							);
// 							$db->insert("tb_inventorytotal", $addInventory);
// 							unset($addInventory);
// 						}
 					}
				}
		}
	 		
	}
	public function calCulatePayment($post){
		$db_global = new Application_Model_DbTable_DbGlobal();
		$session_user = new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$data_update = array(
				"paid" => $post['amount_paid'],
				"all_total" => $post['all_total'],
				"balance" 	=> $post['all_total']-$post['amount_paid'],
				"user_mod" 	=> $GetUserId,
				"timestamp" => new Zend_Date()				
				);
		$succ=$db_global->updateRecord($data_update, $post['purchase_id'], "order_id", "tb_purchase_order");
		return $succ;
	}
	public function updatePurchaseOrder($data){
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
	
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			//for update order by id\
			$id_order_update=$data['id'];
			//print_r($id_order_update);exit();
			//$recieved_id = $data["recieve_id"];
			//update info of order in tb_purchase order
	
			// Select all qty in tb_product and tb_purchase_order_item for compare product exist or not for update qty to old qty
			
			$sql_itm ="SELECT
						(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
							
						,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
						
						,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
						
						,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
							
						, SUM(po.`qty_order`) AS qty_order FROM
					
					tb_purchase_order_item AS po WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
			
			$rows_order=$db_global->getGlobalDb($sql_itm);
			
			if($rows_order){
				foreach ($rows_order as $row){
					$row_get = $db_global->porductLocationExist($row["pro_id"],$data["old_location"]);
					$qty_onorder = $row["qty_onorder"]-$row["qty_order"];
					$qty_available = $row["qty_available"]-$row["qty_order"];
					$qty_onorder_prolo = $row_get["qty_onorder"]-$row["qty_order"];
					$qty_available_prolo = $row_get["qty_avaliable"]-$row["qty_order"];
						
					if($qty_onorder<=0 OR $qty_onorder_prolo<=0){
						Application_Form_FrmMessage::message("The Main Stock or Location Stock is Not enough ");
						Application_Form_FrmMessage::redirectUrl("/purchase/advance/advance/id/".$id_order_update);
						exit();
					}else{
			
						$update_product = array(
			
								"qty_onorder"	=>	$qty_onorder,
								"qty_available"	=>	$qty_available,
									
						);
						$this->_name="tb_product";
						$where = $this->getAdapter()->quoteInto("pro_id=?", $row["pro_id"]);
						$this->update($update_product, $where);
						unset($update_product);
			
						$update_prolocation = array(
									
								"qty_onorder"		=>	$qty_onorder_prolo,
								"qty_avaliable"		=>	$qty_available_prolo
						);
						$this->_name="tb_prolocation";
						$where = $this->getAdapter()->quoteInto("ProLocationID=?", $row_get["ProLocationID"]);
						$this->update($update_prolocation, $where);
					}
				}
					
			}
			
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"LocationId"     => 	$data["LocationId"],
					"order"          => 	$data['txt_order'],
					"date_order"     => 	$data['order_date'],
					"status"         => 	$data["status"],
					"remark"         => 	$data['remark'],
					"user_mod"       => 	$GetUserId,
					"timestamp"      => 	new Zend_Date(),
					"discount_value" =>		$data["dis_value"],
					"discount_real"	 =>		$data["global_disc"],
					"paid"           => 	$data['paid'],
					"net_total"		=>		$data["net_total"],
					"all_total"      => 	$data['all_total'],
					"payment_method" =>		$data["payment_name"],
					"currency_id"	=>		$data["currency"],
					"balance"        => 	$data['balance'],
			);
			$this->_name="tb_purchase_order";
			$where = $this->getAdapter()->quoteInto("order_id=?", $id_order_update);
			$this->update($info_purchase_order, $where);
			//$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
			
			
			
			$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
			$db_global->deleteRecords($sql);
			unset($sql);
			
			
			$sql_history= "DELETE FROM tb_purchase_order_history WHERE `order` IN ($id_order_update)";
			$db_global->deleteRecords($sql_history);
			unset($sql_history);
			/// update
	
			$ids=explode(',',$data['identities']);
		//	print_r($ids);
			
			foreach ($ids as $i) {
				
				if(@$data["pricefree_".$i]){
					$is_free = 1;
				}else $is_free = 0;
				// Insert New purchase order item in old order_id
				
				$data_item[$i]= array(
						'order_id'	 	 => 	$id_order_update,
						'pro_id'	  	 => 	$data['item_id_'.$i],
						'qty_order'	 	 => 	$data['qty'.$i],
						'price'		 	 => 	$data['price'.$i],
						'sub_total'	 	 => 	$data['total'.$i],
						'total_befor'	 => 	$data['total'.$i],
						'remark'	 	 => 	$data['remark'.$i],
						'disc_value'	 =>		$data['dis-value'.$i],
						'is_free'		 =>		$is_free
				);
				//print_r($data_item); echo "<br />";echo "<br />";
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				unset($data_item[$i]);
				
				$data_history[$i] = array(
						
						'order'	 	 		=> 		$id_order_update,
						'pro_id'	 		=> 		$data['item_id_'.$i],
						'type'		 		=> 		1,
						'customer_id'		=>	 	$data['v_name'],
						'status'	 		=> 		$data["status"],
						'order_total'		=>		$data['total'.$i],
						'qty'		 		=> 		$data['qty'.$i],
						'unit_price' 		=> 		$data['price'.$i],
						'sub_total'  		=> 		$data['total'.$i],
						//'date'				=> 		$data["old_history_date"],
						'last_update_date' 	=> 		new Zend_Date()
				);
				//print_r($data_history);exit();
				$db->insert("tb_purchase_order_history", $data_history[$i]);
				unset($data_history[$i]);
				
				
				$locationid=$data['LocationId'];
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
				
				// Update stock in tb_product
				
				$rows=$db_global->productLocationInventory($itemId, $locationid);//to check product location
				if($rows){
					$update_prolo = array(
				
							"qty_onorder"		=>		$rows["qty_onorder"]+$qtyrecord,
							"qty_avaliable"		=> 		$rows["qty_avaliable"]+$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
						
					$this->_name="tb_prolocation";
					$where = $this->getAdapter()->quoteInto("ProLocationID=?", $rows["ProLocationID"]);
					$this->update($update_prolo, $where);
					unset($update_prolo);
				
					$update_product = array(
				
							"qty_onorder"		=>		$rows["qty_onorder"]+$qtyrecord,
							"qty_available"		=>		$rows["qty_available"]+$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
					$this->_name="tb_product";
					$where = $this->getAdapter()->quoteInto("pro_id=?", $itemId);
					$this->update($update_product, $where);
				
						
				}else{
					$update_prolo = array(
								
							"qty_onorder"		=>		$rows["qty_onorder"]+$qtyrecord,
							"qty_avaliable"		=> 		$rows["qty_avaliable"]+$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
				
					$this->_name="tb_prolocation";
					$this->insert($update_prolo);
					unset($update_prolo);
				
					$update_product = array(
								
							"qty_onorder"		=>		$rows["qty_onorder"]+$qtyrecord,
							"qty_available"		=>		$rows["qty_available"]+$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
					$this->_name="tb_product";
					$this->insert($update_product);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$e->getMessage();
		}
	}
	
	/// Received and Paid
	
	public function receivePaidOrder($data){
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
	
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$id_order_update=$data['id'];
			
			// Select all qty in tb_product and tb_purchase_order_item for compare product exist or not for update qty to old qty
			$sql_recieve = new purchase_Model_DbTable_DbPurchaseOrder();
			$result = $sql_recieve->recieved_info($id_order_update);
			
			$sqls = "SELECT * FROM tb_setting WHERE `code`=16";
			$ro = $db_global->getGlobalDbRow($sqls);
			$RO = $ro["key_value"];
			$date= new Zend_Date();
			$recieve_no=$RO.$date->get('hh-mm-ss');
			
			$sql_recieve_order = "SELECT `recieve_id`,`recieve_type`,order_id,`vendor_id`,`location_id`,`disc_value`,paid,`all_total`,`balance`
			FROM tb_recieve_order WHERE order_id = $id_order_update";
			$result_recieve = $db_global->getGlobalDbRow($sql_recieve_order);
			
			if($result_recieve){

				$receive = array(
						
						"vendor_id"		=>	$data['v_name'],
						"location_id"	=>	$data["LocationId"],
						"order_date"	=>	$data['order_date'],
						"date_recieve"	=>	new Zend_Date(),
						"status"		=>	4,
						"is_active"		=>	1,
						"disc_value"	=>	$data["dis_value"],
						"paid"			=>	$data["all_total"],
						"all_total"		=>	$data["all_total"],
						"balance"		=>	0,
						"user_recieve"	=>	$GetUserId
				);
				$this->_name = "tb_recieve_order";
				$where = $this->getAdapter()->quoteInto("recieve_id=?", $result_recieve["recieve_id"]);
				$recieve_id = $this->update($receive, $where);
				unset($receive);
			}else{
				$receive = array(
							"recieve_no"	=>	$recieve_no,
							"recieve_type"	=>	1,
							"order_id"		=>	$id_order_update,
							"order_no"		=>	$data['txt_order'],
							"vendor_id"		=>	$data['v_name'],
							"location_id"	=>	$data["LocationId"],
							"order_date"	=>	$data['order_date'],
							"date_recieve"	=>	new Zend_Date(),
							"status"		=>	4,
							"is_active"		=>	1,
							"disc_value"	=>	$data["dis_value"],
							"paid"			=>	$data["all_total"],
							"all_total"		=>	$data["all_total"],
							"balance"		=>	0,
							"user_recieve"	=>	$GetUserId
				);
				$this->_name = "tb_recieve_order";
				$recieve_id = $this->insert($receive);
				unset($receive);
			
			}

			$info_purchase_order=array(
					
					"vendor_id"      => 	$data['v_name'],
					"LocationId"     => 	$data["LocationId"],
					"order"          => 	$data['txt_order'],
					"date_order"     => 	$data['order_date'],
					"status"         => 	4,
					"remark"         => 	$data['remark'],
					"user_mod"       => 	$GetUserId,
					"timestamp"      => 	new Zend_Date(),
					"discount_value" =>		$data["dis_value"],
					"discount_real"	 =>		$data["global_disc"],
					"paid"           => 	$data['all_total'],
					"net_total"		=>		$data["net_total"],
					"all_total"      => 	$data['all_total'],
					"payment_method" =>		$data["payment_name"],
					"currency_id"	=>		$data["currency"],
					"balance"        => 	0,
					
			);
			
			$this->_name = "tb_purchase_order";
			$where = $this->getAdapter()->quoteInto("order_id=?", $id_order_update);
			$this->update($info_purchase_order, $where);
			unset($info_purchase_order);
			//$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");

			
// 			$sql_itm ="SELECT
// 							(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
							
// 							,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
								
// 							,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
								
// 							,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
							
// 							, SUM(po.`qty_order`) AS qty_order FROM
						
// 						tb_purchase_order_item AS po WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
				
// 			$rows_order=$db_global->getGlobalDb($sql_itm);
				
// 			if($rows_order){
// 				foreach ($rows_order as $row){
// 					$row_get = $db_global->porductLocationExist($row["pro_id"],$data["old_location"]);
// 					$qty_onhand = $row["qty_onhand"]-$row["qty_order"];
// 					$qty_available = $row["qty_available"]-$row["qty_order"];
// 					$qty = $row_get["qty"]-$row["qty_order"];
// 					$qty_available_prolo = $row_get["qty_avaliable"]-$row["qty_order"];
					
// 					if($qty_onhand<=0 OR $qty<=0){
// 						Application_Form_FrmMessage::message("The Main Stock or Location Stock is Not enough ");
// 						Application_Form_FrmMessage::redirectUrl("/purchase/advance/advance/id/".$id_order_update);
// 						exit();
// 					}else{
						
// 						$update_product = array(
								
// 								"qty_onhand"	=>	$qty_onhand,
// 								"qty_available"	=>	$qty_available,
									
// 						);
// 						$this->_name="tb_product";
// 						$where = $this->getAdapter()->quoteInto("pro_id=?", $row["pro_id"]);
// 						$this->update($update_product, $where);
// 						unset($update_product);
						
// 						$update_prolocation = array(
							
// 								"qty"				=>	$qty,
// 								"qty_avaliable"		=>	$qty_available_prolo
// 						);
// 						$this->_name="tb_prolocation";
// 						$where = $this->getAdapter()->quoteInto("ProLocationID=?", $row_get["ProLocationID"]);
// 						$this->update($update_prolocation, $where);
// 					}
// 				}
			
// 			}

			$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
			$db_global->deleteRecords($sql);
			unset($sql);
			
			$sql_history= "DELETE FROM tb_purchase_order_history WHERE `order` IN ($id_order_update)";
			$db_global->deleteRecords($sql_history);
			unset($sql_history);

			/// update
	
			$ids=explode(',',$data['identities']);
				
			foreach ($ids as $i) {
				
				if(@$data["pricefree_".$i]){
					$is_free = 1;
				}else $is_free = 0;
				// Insert New purchase order item in old order_id
				
				$data_item[$i]= array(
						'order_id'	 	 => 	$id_order_update,
						'pro_id'	  	 => 	$data['item_id_'.$i],
						'qty_order'	 	 => 	$data['qty'.$i],
						'price'		 	 => 	$data['price'.$i],
						'sub_total'	 	 => 	$data['total'.$i],
						'total_befor'	 => 	$data['total'.$i],
						'remark'	 	 => 	$data['remark'.$i],
						'disc_value'	 =>		$data['dis-value'.$i],
						'is_free'		 =>		$is_free
				);
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				unset($data_item[$i]);;
				
				$recieve_item = array(
					
						"recieve_id"	=>  $recieve_id,
						"pro_id"		=>	$data['item_id_'.$i],
						"order_id"		=> 	$id_order_update,
						"qty_order"		=>	$data['qty'.$i],
						"qty_recieve"	=>	$data['qty'.$i],
						"qty_remian"	=>	0,
						"price"			=>	$data['price'.$i],
						"disc_value"	=>	$data["dis-value".$i],
						"is_free"		=>	$is_free,
						"total_before"	=>	0,
						"sub_total"		=>	$data['total'.$i],
				);
				$this->_name="tb_recieve_order_item";
				$this->insert($recieve_item);
				unset($recieve_item);
				
				$data_history[$i] = array(
						'order'	 	 		=> 		$id_order_update,
						'pro_id'	 		=> 		$data['item_id_'.$i],
						'type'		 		=> 		1,
						'customer_id'		=>	 	$data['v_name'],
						'status'	 		=> 		$data["status"],
						'order_total'		=>		$data['total'.$i],
						'qty'		 		=> 		$data['qty'.$i],
						'status'			=>		4,
						'unit_price' 		=> 		$data['price'.$i],
						'sub_total'  		=> 		$data['total'.$i],
						//'date'				=> 		$data["old_history_date"],
						'last_update_date' 	=> 		new Zend_Date()
				);
				$db->insert("tb_purchase_order_history", $data_history[$i]);
				unset($data_history[$i]);
				
				
				$locationid=$data['LocationId'];
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
				
				// Update stock in tb_product
				
				$rows=$db_global->productLocationInventory($itemId, $locationid);//to check product location
				if($rows){
					$update_prolo = array(
						
							"qty"				=>		$rows["qty"]+$qtyrecord,
							"qty_avaliable"		=> 		$rows["qty_avaliable"]+$qtyrecord,
							"qty_onorder"		=>		$rows["qty_onorder"]-$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
					
					$this->_name="tb_prolocation";
					$where = $this->getAdapter()->quoteInto("ProLocationID=?", $rows["ProLocationID"]);
					$this->update($update_prolo, $where);
					unset($update_prolo);

					$update_product = array(
						
							"qty_onhand"		=>		$rows["qty_onhand"]+$qtyrecord,
							"qty_available"		=>		$rows["qty_available"]+$qtyrecord,
							"qty_onorder"		=>		$rows["pqty_onorder"]-$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
					$this->_name="tb_product";
					$where = $this->getAdapter()->quoteInto("pro_id=?", $itemId);
					$this->update($update_product, $where);

					
				}else{
					$update_prolo = array(
					
							"qty"				=>		$rows["qty"]+$qtyrecord,
							"qty_avaliable"		=> 		$rows["qty_avaliable"]+$qtyrecord,
							//"qty_onorder"		=>		$rows["qty_onorder"]-$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
						
					$this->_name="tb_prolocation";
					$this->insert($update_prolo);
					unset($update_prolo);

					$update_product = array(
					
							"qty_onhand"		=>		$rows["qty_onhand"]+$qtyrecord,
							"qty_available"		=>		$rows["qty_available"]+$qtyrecord,
							"last_mod_date"		=>		new Zend_Date()
					);
					$this->_name="tb_product";
					$where = $this->getAdapter()->quoteInto("pro_id=?", $itemId);
					$this->update($update_product, $where);
				}
			}
				
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$e->getMessage();
		}
	}
	
	// reorder
	
	public function rePurchaseOrder($data){
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
	
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
				
			$id_order_update=$data['id'];
			print_r($id_order_update);
			// Select all qty in tb_product and tb_purchase_order_item for compare product exist or not for update qty to old qty
			$sql_recieve = new purchase_Model_DbTable_DbPurchaseOrder();
			$result = $sql_recieve->recieved_info($id_order_update);
				
			$sqls = "SELECT * FROM tb_setting WHERE `code`=16";
			$ro = $db_global->getGlobalDbRow($sqls);
			$RO = $ro["key_value"];
			$date= new Zend_Date();
			$recieve_no=$RO.$date->get('hh-mm-ss');
			
			$sql_recieve_order = "SELECT `recieve_id`,`recieve_type`,order_id,`vendor_id`,`location_id`,`disc_value`,paid,`all_total`,`balance`
								FROM tb_recieve_order WHERE order_id = $id_order_update";
			$result_recieve = $db_global->getGlobalDbRow($sql_recieve_order);
			//print_r($result_recieve);exit();
			if($result_recieve){
				$db->getProfiler()->setEnabled(true);
				$receive = array(
	// 					"recieve_no"	=>	$recieve_no,
	// 					"recieve_type"	=>	1,
	// 					"order_id"		=>	$id_order_update,
	// 					"order_no"		=>	$data['txt_order'],
	// 					"vendor_id"		=>	$data['v_name'],
	// 					"location_id"	=>	$data["LocationId"],
	// 					"order_date"	=>	$data['order_date'],
	// 					"date_recieve"	=>	new Zend_Date(),
	// 					"status"		=>	4,
						"is_active"		=>	0,
	// 					"disc_value"	=>	$data["dis_value"],
	// 					"paid"			=>	$data["all_total"],
	// 					"all_total"		=>	$data["all_total"],
	// 					"balance"		=>	0,
						"user_recieve"	=>	$GetUserId
				);
				$this->_name = "tb_recieve_order";
				$where = $this->getAdapter()->quoteInto("recieve_id=?", $result_recieve["recieve_id"]);
				$recieve_id = $this->update($receive, $where);
				//unset($receive);
		
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
			}
			
			$db->getProfiler()->setEnabled(true);
			$info_purchase_order=array(
						
// 					"vendor_id"      => 	$data['v_name'],
// 					"LocationId"     => 	$data["LocationId"],
// 					"order"          => 	$data['txt_order'],
// 					"date_order"     => 	$data['order_date'],
					"status"         => 	3,
// 					"remark"         => 	$data['remark'],
// 					"user_mod"       => 	$GetUserId,
// 					"timestamp"      => 	new Zend_Date(),
// 					"discount_value" =>		$data["dis_value"],
// 					"discount_real"	 =>		$data["global_disc"],
 					"paid"           => 	0,
// 					"net_total"		=>		$data["net_total"],
// 					"all_total"      => 	$data['all_total'],
// 					"payment_method" =>		$data["payment_name"],
// 					"currency_id"	=>		$data["currency"],
 					"balance"        => 	$data['all_total']
						
			);
			$this->_name="tb_purchase_order";
			$where = $this->getAdapter()->quoteInto("order_id=?", $id_order_update);
			$this->update($info_purchase_order, $where);
			//$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
	
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			$db->getProfiler()->setEnabled(false);
			
			$sql_itm ="SELECT
				(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
					
				,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
		
				,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
		
				,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
					
				, SUM(po.`qty_order`) AS qty_order FROM
	
			tb_purchase_order_item AS po WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
	
			$rows_order=$db_global->getGlobalDb($sql_itm);
	
			if($rows_order){
				foreach ($rows_order as $row){
					$row_get = $db_global->porductLocationExist($row["pro_id"],$data["old_location"]);
					$qty_onhand = $row["qty_onhand"]-$row["qty_order"];
					$qty_available = $row["qty_available"]-$row["qty_order"];
					$qty = $row_get["qty"]-$row["qty_order"];
					$qty_available_prolo = $row_get["qty_avaliable"]-$row["qty_order"];
						
					if($qty_onhand<=0 OR $qty<=0){
						Application_Form_FrmMessage::message("The Main Stock or Location Stock is Not enough ");
						exit();
					}else{
	
						$db->getProfiler()->setEnabled(true);
						$update_product = array(
	
								"qty_onhand"	=>	$qty_onhand,
								"qty_available"	=>	$qty_available,
								"qty_onorder"	=>	$row["qty_onorder"]+$row["qty_order"]
									
						);
						$this->_name="tb_product";
						$where = $this->getAdapter()->quoteInto("pro_id=?", $row["pro_id"]);
						$this->update($update_product, $where);
						unset($update_product);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
						$db->getProfiler()->setEnabled(false);
	
						$db->getProfiler()->setEnabled(true);
						
						$update_prolocation = array(
									
								"qty"				=>	$qty,
								"qty_avaliable"		=>	$qty_available_prolo,
								"qty_onorder"		=>	$row_get["qty_onorder"]+$row["qty_order"]
						);
						$this->_name="tb_prolocation";
						$where = $this->getAdapter()->quoteInto("ProLocationID=?", $row_get["ProLocationID"]);
						$this->update($update_prolocation, $where);
						
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
						$db->getProfiler()->setEnabled(false);
					}
				}
					
			}
	
			/// update
	
			$ids=explode(',',$data['identities']);
	
			foreach ($ids as $i) {
	
				if(@$data["pricefree_".$i]){
					$is_free = 1;
				}else $is_free = 0;
				// Insert New purchase order item in old order_id
				
				$db->getProfiler()->setEnabled(true);
				
				$data_history[$i] = array(
						'order'	 	 		=> 		$id_order_update,
						'pro_id'	 		=> 		$data['item_id_'.$i],
						'type'		 		=> 		1,
						'customer_id'		=>	 	$data['v_name'],
						'status'	 		=> 		$data["status"],
						'order_total'		=>		$data['total'.$i],
						'qty'		 		=> 		$data['qty'.$i],
						'status'			=>		4,
						'unit_price' 		=> 		$data['price'.$i],
						'sub_total'  		=> 		$data['total'.$i],
						//'date'				=> 		$data["old_history_date"],
						'last_update_date' 	=> 		new Zend_Date()
				);
				$db->insert("tb_purchase_order_history", $data_history[$i]);
				unset($data_history[$i]);
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
	

			}
	
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$e->getMessage();
		}
	}
	
	// Cancell Order
	
	public function cancelPurchaseOrder($data){
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
	
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
	
			$id_order_update=$data['id'];
			
			if($data["status"]==4){
				$sql_recieve_order = "SELECT `recieve_id`,`recieve_type`,order_id,`vendor_id`,`location_id`,`disc_value`,paid,`all_total`,`balance`
				FROM tb_recieve_order WHERE order_id = $id_order_update";
				$result_recieve = $db_global->getGlobalDbRow($sql_recieve_order);
				//print_r($result_recieve);exit();
				if($result_recieve){
					$db->getProfiler()->setEnabled(true);
					$receive = array(
							
							"is_active"		=>	0,
							"user_recieve"	=>	$GetUserId
					);
					$this->_name = "tb_recieve_order";
					$where = $this->getAdapter()->quoteInto("recieve_id=?", $result_recieve["recieve_id"]);
					$recieve_id = $this->update($receive, $where);
					
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
					
					
					$sql_itm ="SELECT
					(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
					
					,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
					
					,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
					
					,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
					
					, SUM(po.`qty_order`) AS qty_order FROM
					
					tb_purchase_order_item AS po WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
					
					$rows_order=$db_global->getGlobalDb($sql_itm);
					
					if($rows_order){
						foreach ($rows_order as $row){
							$row_get = $db_global->porductLocationExist($row["pro_id"],$data["old_location"]);
							$qty_onhand = $row["qty_onhand"]-$row["qty_order"];
							$qty_available = $row["qty_available"]-$row["qty_order"];
							$qty = $row_get["qty"]-$row["qty_order"];
							$qty_available_prolo = $row_get["qty_avaliable"]-$row["qty_order"];
					
							if($qty_onhand<=0 OR $qty<=0){
								Application_Form_FrmMessage::message(" Can't cancel!!! The Main Stock or Location Stock is Not enough ");
								exit();
							}else{
					
								$db->getProfiler()->setEnabled(true);
								$update_product = array(
					
										"qty_onhand"	=>	$qty_onhand,
										"qty_available"	=>	$qty_available,
										//"qty_onorder"	=>	$row["qty_onorder"]+$row["qty_order"]
											
								);
								$this->_name="tb_product";
								$where = $this->getAdapter()->quoteInto("pro_id=?", $row["pro_id"]);
								$this->update($update_product, $where);
								unset($update_product);
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
								$db->getProfiler()->setEnabled(false);
					
								$db->getProfiler()->setEnabled(true);
					
								$update_prolocation = array(
											
										"qty"				=>	$qty,
										"qty_avaliable"		=>	$qty_available_prolo,
										//"qty_onorder"		=>	$row_get["qty_onorder"]+$row["qty_order"]
								);
								$this->_name="tb_prolocation";
								$where = $this->getAdapter()->quoteInto("ProLocationID=?", $row_get["ProLocationID"]);
								$this->update($update_prolocation, $where);
					
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
								Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
								$db->getProfiler()->setEnabled(false);
							}
						}
							
					}
				}
				
				$db->getProfiler()->setEnabled(true);
				$info_purchase_order=array(
				
						"status"         => 	6,
							
				);
				$this->_name="tb_purchase_order";
				$where = $this->getAdapter()->quoteInto("order_id=?", $id_order_update);
				$this->update($info_purchase_order, $where);
				//$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
				
			}elseif ($data["status"]==3){
				
			}
				
			$db->getProfiler()->setEnabled(true);
			$info_purchase_order=array(

					"status"         => 	6,
					
			);
			$this->_name="tb_purchase_order";
			$where = $this->getAdapter()->quoteInto("order_id=?", $id_order_update);
			$this->update($info_purchase_order, $where);
			//$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
	
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			$db->getProfiler()->setEnabled(false);
			
			$ids=explode(',',$data['identities']);
			
			foreach ($ids as $i) {
			
				if(@$data["pricefree_".$i]){
					$is_free = 1;
				}else $is_free = 0;
				// Insert New purchase order item in old order_id
			
				$db->getProfiler()->setEnabled(true);
			
				$data_history[$i] = array(
						'order'	 	 		=> 		$id_order_update,
						'pro_id'	 		=> 		$data['item_id_'.$i],
						'type'		 		=> 		1,
						'customer_id'		=>	 	$data['v_name'],
						'status'	 		=> 		$data["status"],
						'order_total'		=>		$data['total'.$i],
						'qty'		 		=> 		$data['qty'.$i],
						'status'			=>		6,
						'unit_price' 		=> 		$data['price'.$i],
						'sub_total'  		=> 		$data['total'.$i],
						//'date'				=> 		$data["old_history_date"],
						'last_update_date' 	=> 		new Zend_Date()
				);
				$db->insert("tb_purchase_order_history", $data_history[$i]);
				unset($data_history[$i]);
			
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
			
			
			}
				
			$sql_itm ="SELECT
			(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
				
			,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
	
			,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
	
			,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
				
			, SUM(po.`qty_order`) AS qty_order FROM
	
			tb_purchase_order_item AS po WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
	
			$rows_order=$db_global->getGlobalDb($sql_itm);
	
			if($rows_order){
				foreach ($rows_order as $row){
					$row_get = $db_global->porductLocationExist($row["pro_id"],$data["old_location"]);
					$qty_onhand = $row["qty_onhand"]-$row["qty_order"];
					$qty_available = $row["qty_available"]-$row["qty_order"];
					$qty = $row_get["qty"]-$row["qty_order"];
					$qty_available_prolo = $row_get["qty_avaliable"]-$row["qty_order"];
	
					if($qty_onhand<=0 OR $qty<=0){
						Application_Form_FrmMessage::message("The Main Stock or Location Stock is Not enough ");
						exit();
					}else{
	
						$db->getProfiler()->setEnabled(true);
						$update_product = array(
	
								"qty_onhand"	=>	$qty_onhand,
								"qty_available"	=>	$qty_available,
								"qty_onorder"	=>	$row["qty_onorder"]+$row["qty_order"]
									
						);
						$this->_name="tb_product";
						$where = $this->getAdapter()->quoteInto("pro_id=?", $row["pro_id"]);
						$this->update($update_product, $where);
						unset($update_product);
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
						$db->getProfiler()->setEnabled(false);
	
						$db->getProfiler()->setEnabled(true);
	
						$update_prolocation = array(
									
								"qty"				=>	$qty,
								"qty_avaliable"		=>	$qty_available_prolo,
								"qty_onorder"		=>	$row_get["qty_onorder"]+$row["qty_order"]
						);
						$this->_name="tb_prolocation";
						$where = $this->getAdapter()->quoteInto("ProLocationID=?", $row_get["ProLocationID"]);
						$this->update($update_prolocation, $where);
	
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
						$db->getProfiler()->setEnabled(false);
					}
				}
					
			}
	
			/// update
	
			$ids=explode(',',$data['identities']);
	
			foreach ($ids as $i) {
	
				if(@$data["pricefree_".$i]){
					$is_free = 1;
				}else $is_free = 0;
				// Insert New purchase order item in old order_id
	
				$db->getProfiler()->setEnabled(true);
	
				$data_history[$i] = array(
						'order'	 	 		=> 		$id_order_update,
						'pro_id'	 		=> 		$data['item_id_'.$i],
						'type'		 		=> 		1,
						'customer_id'		=>	 	$data['v_name'],
						'status'	 		=> 		$data["status"],
						'order_total'		=>		$data['total'.$i],
						'qty'		 		=> 		$data['qty'.$i],
						'status'			=>		4,
						'unit_price' 		=> 		$data['price'.$i],
						'sub_total'  		=> 		$data['total'.$i],
						//'date'				=> 		$data["old_history_date"],
						'last_update_date' 	=> 		new Zend_Date()
				);
				$db->insert("tb_purchase_order_history", $data_history[$i]);
				unset($data_history[$i]);
	
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
	
	
			}
	
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$e->getMessage();
		}
	}
}
