<?php

class purchase_Model_DbTable_DbCheckInProduct extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	
	public function RecievedPurchaseOrder($data){
		try{
		$db = $this->getAdapter();
		$db->beginTransaction();
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$_order_no = $data["order_no"];
		$_order_id = $data["order_id"];
		$ids=explode(',',$data['identity']);
		//print_r($row);//exit();
		if($data['invoice_no']==""){
				$date= new Zend_Date();
				$recieved_num="RO".$date->get('hh-mm-ss');
		}else{
			$recieved_num=$data['invoice_no'];
		}
		
		$sql_itm ="SELECT
				(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS pro_id
					
				,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onorder
				
				,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_onhand
				
				,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = po.`pro_id`) AS qty_available
					
				, SUM(po.`qty_order`) AS qty_order FROM
				
				tb_purchase_order_item AS po WHERE po.order_id = $_order_no GROUP BY po.pro_id";
		
		$result = $db_global->getGlobalDb($sql_itm);
		if($result){
			foreach ($result as $row_pro){
				$row_get = $db_global->porductLocationExist($row_pro["pro_id"],$data["LocationId"]);
				//print_r($row_get);
				if($row_get){
					$update_prolo_stock = array(
							"qty_onorder"		=> $row_get["qty_onorder"]-$row_pro["qty_order"],
							"last_usermod" 		=> $GetUserId,
							"last_mod_date" 	=> new Zend_Date()
					);
		
					$update_data = $db_global->updateRecord($update_prolo_stock, $row_get["ProLocationID"],"ProLocationID","tb_prolocation");
					unset($update_prolo_stock);
		
				}
		
				$update_product_stock = array(
						"qty_onorder"			=>	$row_pro["qty_onorder"] - $row_pro["qty_order"],
						"last_mod_date" 		=> new Zend_Date()
				);
				$sqls = $db_global->updateRecord($update_product_stock,$row_pro["pro_id"],"pro_id","tb_product");
				unset($update_product_stock);
			}
		}
		unset($result);
		
		try{
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"LocationId"     => 	$data["LocationId"],
					"status"         => 	5,
					"remark"         => 	$data['remark'],
					"user_mod"       => 	$GetUserId,
					"timestamp"      => 	new Zend_Date(),
					"paid"           => 	$data['paid'],
					"all_total"      => 	$data['totalAmoun'],
					"balance"        => 	$data['remain']
			);
			$db_global->updateRecord($info_purchase_order,$_order_no,"order_id","tb_purchase_order");
			unset($info_purchase_order);
		}catch (Exception $e){
			echo $e->getMessage();
		}
		
		$sql_recieve = new purchase_Model_DbTable_DbPurchaseOrder();
		$result_recieve = $sql_recieve->recieved_info($_order_no);
		$recieved_id = $result_recieve["recieve_id"];
		if($result_recieve){
			$data_recieved_order = array(
					"recieve_type" 	=> 		1,
					"vendor_id"		=>		$data['v_name'],
					"location_id" 	=>		$data["LocationId"],
					"date_recieve"	=> 		new Zend_Date(),
					"status"		=> 		5,
					"is_active"		=>		1,
					"paid"			=> 		$data['paid'],
					"all_total"		=>		$data['totalAmoun'],
					"balance"		=>		$data['remain'],
					"user_recieve"	=>		$GetUserId
			);
			$recieved_order = $db_global->updateRecord($data_recieved_order, $result_recieve["recieve_id"], "recieve_id", "tb_recieve_order");
			unset($data_recieved_order);
			
			$sqls= "DELETE FROM tb_recieve_order_item WHERE recieve_id IN ($recieved_id)";
			$db_global->deleteRecords($sqls);
			unset($sqls);
			foreach ($ids as $i){
				$recieved_item[$i] = array(
						"recieve_id"	=> 		$recieved_id,
						"pro_id"		=> 		$data['item_id_'.$i],
						"order_id"		=> 		$_order_no,
						"qty_order"		=> 		$data['qty'.$i],
						"qty_recieve"	=> 		$data['qty'.$i],
						//"qty_remian"	=> ,
						"price"			=> 		$data['price'.$i],
						"total_before"	=> 		$data['total'.$i],
						"sub_total"		=> 		$data['total'.$i],
			
				);
				$db->insert("tb_recieve_order_item", $recieved_item[$i]);
				unset($recieved_item[$i]);
			}
		}else{
			$recieve_order = array(
					"recieve_no"		=> $recieved_num,
					"order_id"			=>	$_order_no,
					"order_no"			=> 	$data['order_num'],
					"vendor_id"			=>	$data["v_name"],
					"recieve_type"		=>	1,
					"location_id"		=> 	$data["LocationId"],
					"order_date"		=>	$data["order_date"],
					"date_recieve"		=>	new Zend_Date(),
					"status"			=>	5,
					"is_active"			=>  1,
					"paid"				=>	$data["paid"],
					"all_total"			=>	$data["remain"],
					"user_recieve"		=>  $GetUserId,
			);
			$this->_name = "tb_recieve_order";
			$recieved_order = $this->insert($recieve_order);
			unset($recieve_order);
			
			foreach ($ids as $i){
				$recieved_item[$i] = array(
						"recieve_id"	=> 		$recieved_order,
						"pro_id"		=> 		$data['item_id_'.$i],
						"order_id"		=> 		$_order_no,
						"qty_order"		=> 		$data['qty'.$i],
						"qty_recieve"	=> 		$data['qty'.$i],
						//"qty_remian"	=> ,
						"price"			=> 		$data['price'.$i],
						"total_before"	=> 		$data['total'.$i],
						"sub_total"		=> 		$data['total'.$i],
				);
				$db->insert("tb_recieve_order_item", $recieved_item[$i]);
				unset($recieved_item[$i]);
			}
		}
		
		$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($_order_no)";
		$db_global->deleteRecords($sql);
		unset($sql);
			
		$sql_history= "DELETE FROM tb_purchase_order_history WHERE `order` IN ($_order_no)";
		$db_global->deleteRecords($sql_history);
		unset($sql_history);
			
		
		foreach ($ids as $i) {
			// Insert New purchase order item in old order_id
		
			$data_item[$i]= array(
					'order_id'	 	 => 	$_order_no,
					'pro_id'	  	 => 	$data['item_id_'.$i],
					'qty_order'	 	 => 	$data['qty'.$i],
					'price'		 	 => 	$data['price'.$i],
					'sub_total'	 	 => 	$data['total'.$i],
					'total_befor'	 => 	$data['total'.$i],
					'remark'	 	 => 	$data['remark_'.$i]//just add new
			);
			//print_r($data_item); echo "<br />";echo "<br />";
			$db->insert("tb_purchase_order_item", $data_item[$i]);
			unset($data_item[$i]);
		
			$data_history[$i] = array(
					'order'	 	 		=> 		$_order_no,
					'pro_id'	 		=> 		$data['item_id_'.$i],
					'type'		 		=> 		1,
					'customer_id'		=>	 	$data['v_name'],
					'status'	 		=> 		5,
					'order_total'		=>		$data['total'.$i],
					'qty'		 		=> 		$data['qty'.$i],
					'unit_price' 		=> 		$data['price'.$i],
					'sub_total'  		=> 		$data['total'.$i],
					'date'				=> 		$data["order_date"],
					'last_update_date' 	=> 		new Zend_Date()
			);
			//print_r($data_history);exit();
			$db->insert("tb_purchase_order_history", $data_history[$i]);
			unset($data_history[$i]);
			
			$locationid=$data['LocationId'];
			$itemId=$data['item_id_'.$i];
			$qtyrecord=$data['qty'.$i];//qty on 1 record
			
			// Update stock in tb_product
			$rows=$db_global -> productLocationInventory($itemId, $locationid);//to check product location
			if($rows){
				$getrecord_id = $rows["ProLocationID"];
			
					$itemOnHand = array(
							'qty_onhand'   		=> 		$rows["qty_onhand"]+$qtyrecord,
							'qty_available'		=> 		$rows["qty_available"]+$qtyrecord,
							'last_mod_date'     => 		new Zend_date()
					);
					$db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
					
					unset($itemOnHand);
					$updatedata=array(
							'qty' 				=> 		$rows['qty']+$qtyrecord,
							'last_mod_date'     => 		new Zend_date()
					);
					$db_global->updateRecord($updatedata,$getrecord_id,"ProLocationID","tb_prolocation");
					
			}else {
				//insert stock ;
				$rows_pro_exit= $db_global->productLocation($itemId, $locationid); // check product location exist
				if($rows_pro_exit){
					
						$updatedata=array(
								'qty' 				=> 		$rows['qty']+$qtyrecord,
								'last_mod_date'     => 		new Zend_date()
						);
						$itemid=$db_global->updateRecord($updatedata,$rows_pro_exit['ProLocationID'], "ProLocationID", "tb_prolocation");
						unset($updatedata);
						
				}else{ // If product not exist insert New product in tb_prolocation
					$insertdata=array(
							'pro_id'       		=> 		$itemId,
							'LocationId'   		=> 		$locationid,
							'last_usermod'		=> 		$GetUserId,
							'qty'         		=> 		$qtyrecord,
							'last_mod_date'		=> 		new Zend_Date()
					);
					//update stock product location
					$db->insert("tb_prolocation", $insertdata);
					unset($insertdata);
				}
				// End If product not exist insert New product in tb_prolocation
					
				$rowitem=$db_global->InventoryExist($itemId);//to check product exist
				// If productt exist update product in tb_product
				if($rowitem){
						$itemOnHand = array(
								'qty_onhand'   		=> 		$rows["qty_onhand"]+$qtyrecord,
								'qty_available'		=> 		$rows["qty_available"]+$qtyrecord,
								'last_mod_date'     => 		new Zend_date()
						);
						$itemid=$db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
						unset($itemOnHand);
				}else { // If product not exist insert new product in tb_product
					$dataInventory= array(
							'pro_id'            => 		$itemId,
							'qty_onhand'    	=> 		$qtyrecord,
							'qty_available' 	=> 		$qtyrecord,
							'last_mod_date'     => 		new Zend_date()
					);
					$db->insert("tb_product", $dataInventory);
					unset($dataInventory);
					//update stock product location
				}
			}
		}
		$db->commit();
		}catch (Exception $e){
			echo $e->getMessage();
			exit();
			$db->rollBack();
		}
	}
	
}