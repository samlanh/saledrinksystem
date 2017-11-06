<?php

class purchase_Model_DbTable_DbCheckIn extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	public function vendorPurchaseOrderCheckIn($data)
	{
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$db = $this->getAdapter();	
			$db->beginTransaction();
		
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
		//	print_r($data);
			$idrecord=$data['v_name'];
	// 		$datainfo=array(
					
	// 				"contact_name"=>$data['contact'],
	// 				"phone"       =>$data['txt_phone'],
	// 		);
	// 		//updage vendor info
	// 		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
			
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"LocationId"     => $data["LocationId"],
					"order"          => $order_add,
					"date_order"     => $data['order_date'],
					"date_in"     	 => $data['date_in'],
					"status"         => $data['status'],
					//"payment_method" => $data['payment_name'],
					//"currency_id"    => $data['currency'],
					"remark"         => $data['remark'],
					"user_mod"       => $GetUserId,
					"timestamp"      => new Zend_Date(),
	// 				"net_total"      => $data['net_total'],
	// 				"discount_type"	 => $data['discount_type'],
	// 				"discount_value" => $data['discount_value'],
	// 				"discount_real"  => $data['discount_real'],
					"paid"           => $data['remain'],
					"all_total"      => $data['remain'],
					"balance"        => 0
			);
			//and info of purchase order
			
			$purchase_id = $db_global->addRecord($info_purchase_order,"tb_purchase_order");
		
			unset($info_purchase_order);
			unset($datainfo); unset($idrecord);
		
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			foreach ($ids as $i)
			{
					
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
				//add history purchase order
				$data_history[$i] = array(
						'pro_id'	 => $data['item_id_'.$i],
						'type'		 => 1,
						'order'		 => $purchase_id,
						'customer_id'=> $data['v_name'],
						'date'		 => new Zend_Date(),
						'status'	 => $data['status'],
						'order_total'=> $data['remain'],
						'qty'		 => $data['qty'.$i],
						'unit_price' => $data['price'.$i],
						'sub_total'  => $data['total'.$i],
				);
				$order_history = $db_global->addRecord($data_history[$i],"tb_purchase_order_history");
				unset($data_history[$i]);
				//add purchase order item
				$data_item[$i]= array(
						'order_id'	  => $purchase_id,
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'price'		  => $data['price'.$i],
						'total_befor' => $data['total'.$i],
	// 					'disc_type'	  => $data['dis-type-'.$i],
	// 					'disc_value'  => $data['dis-value'.$i],
						'sub_total'	  => $data['total'.$i],
						'remark'	  => $data['remark_'.$i]
				);
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				
				unset($data_item[$i]);
				//check stock product location
				
				
				$rows=$db_global -> productLocationInventory($itemId, $locationid);
				if($rows)
				{
					
					$qtyold       = $rows['qty_onorder'];
					$getrecord_id = $rows["ProLocationID"];
					//$qty_onhand   = $rows["QuantityOnHand"]+$qtyrecord;
					$itemOnOrder   = array(
							'qty_onorder'   => $rows["qty_onorder"]+$qtyrecord,
							//'QuantityAvailable'=> $rows["QuantityAvailable"]+$qtyrecord
					);
					//update total stock
					$itemid=$db_global->updateRecord($itemOnOrder,$itemId,"pro_id","tb_product");
					//update stock dork
					//$newqty       = ;
					$updatedata=array(
							'qty_onorder' => $qtyold+$qtyrecord
					);
					//update stock product location
					$itemid=$db_global->updateRecord($updatedata,$getrecord_id,"ProLocationID","tb_prolocation");
					//add move hostory
				}
				else
				{
					//insert stock ;
					$rows_pro_exit= $db_global->productLocation($itemId, $locationid);
					if($rows_pro_exit){
						$updatedata=array(
								'qty_onorder' => $rows_pro_exit['qty_onorder']+$qtyrecord
						);
						//update stock product location
						$itemid=$db_global->updateRecord($updatedata,$rows_pro_exit['ProLocationID'], "ProLocationID", "tb_prolocation");
					}
					else{
						$insertdata=array(
								'pro_id'       => $data['item_id_'.$i],
								'LocationId'   => $locationid,
								'last_usermod' => $GetUserId,
								'qty_onorder'          => $qtyrecord,
								'last_mod_date'=>new Zend_Date()
						);
						//update stock product location
						$db->insert("tb_prolocation", $insertdata);
					}
					//add move hostory
					$rows=$db_global->InventoryExist($itemId);
					if($rows)
					{
						//$qty_onhand   = $rowitem["QuantityOnHand"]+$data['qty'.$i];
						$itemOnOrder   = array(
								'qty_onorder'    => $rows["qty_onorder"]+$data['qty'.$i],
								//"QuantityAvailable" => $rows["QuantityAvailable"]+$data['qty'.$i],
								'pro_id'		    => $itemId
						);
						//update total stock
						 $db_global->updateRecord($itemOnOrder,$itemId,"pro_id","tb_product");
					}
					else
					{
						$dataInventory= array(
								'pro_id'            => $itemId,
								'qty_onorder'    => $data['qty'.$i],
								//'QuantityAvailable' => $data['qty'.$i],
								'Timestamp'      => new Zend_date()
						);
						$db->insert("tb_product", $dataInventory);
						//add move hostory
					}
				}
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			
		}
		
	} 
	//for add purchase order then click save
	public function VendorOrder($data)
	{
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db= $this->getAdapter(); 
	
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
	
		$idrecord=$data['v_name'];
		$datainfo=array(
				"contact_name"=> $data['contact'],
				"phone"       => $data['txt_phone']
		);
		//updage vendor info
		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
		if($data['txt_order']==""){
			$date= new Zend_Date();
			$order_add="PO".$date->get('hh-mm-ss');
		}
		else{
			$order_add=$data['txt_order'];
			
		}
		$info_purchase_order=array(
				"vendor_id"      => $data['v_name'],
				"LocationId"     => $data["LocationId"],
				"order"          => $order_add,
				"date_order"     => $data['order_date'],
				"status"         => 2,
				"payment_method" => $data['payment_name'],
				"currency_id"    => $data['currency'],
				"remark"         => $data['remark'],
				"user_mod"       => $GetUserId,
				"timestamp"      => new Zend_Date(),
				"version"        => 1,
				"net_total"      => $data['net_total'],
				"discount_type"	 => $data['discount_type'],
				"discount_value" => $data['discount_value'],
				"discount_real"  => $data['discount_real'],
				"paid"           => $data['paid'],
				"all_total"      => $data['all_total'],
				"balance"        => $data['all_total']-$data['paid']
		);
		//and info of purchase order
		$purchase_id = $db_global->addRecord($info_purchase_order,"tb_purchase_order");
		unset($info_purchase_order);
			
		$ids=explode(',',$data['identity']);
		//   $qtyonhand=0;
		foreach ($ids as $i)
		{
			//add history purchase order
				$data_history = array(
						'pro_id'	 => $data['item_id_'.$i],
						'type'		 => 1,
						'order'		 => $purchase_id,//$data['txt_order']
						'customer_id'=> $data['v_name'],
						'date'		 => new Zend_Date(),
						'status'	 => 2,
						'order_total'=> $data['all_total'],
						'qty'		 => $data['qty'.$i],
						'unit_price' => $data['price'.$i],
						'sub_total'  => $data['after_discount'.$i],
				);
				
				$db_global->addRecord($data_history,"tb_purchase_order_history");
				unset($data_history);
			//add purchase order item
			$data_item[$i]= array(
					'order_id'	  => $purchase_id,
					'pro_id'	  => $data['item_id_'.$i],
					'qty_order'	  => $data['qty'.$i],
					'price'		  => $data['price'.$i],
					'total_befor' => $data['total'.$i],
					'disc_type'	  => $data['dis-type-'.$i],
					'disc_value'  => $data['dis-value'.$i],
					'sub_total'	  => $data['after_discount'.$i]
			);
			$id_order_item=$db_global->addRecord($data_item[$i],"tb_purchase_order_item");
			unset($data_item[$i]);
				
			//update stock total inventory
			$locationid=$data['LocationId'];
			$itemId=$data['item_id_'.$i];
			$qtyrecord=$data['qty'.$i];//qty on 1 record
			$sql="SELECT tv.ProdId, tv.QuantityOnOrder,tv.QuantityAvailable
			FROM tb_inventorytotal AS tv
			INNER JOIN tb_product AS p ON tv.ProdId = p.pro_id
			WHERE p.pro_id = ".$data['item_id_'.$i];
			$rows=$db_global->getGlobalDbRow($sql);
			if($rows)
			{
				$qty_onhand   = $rows["QuantityOnOrder"]+$qtyrecord;
	
				$qty_on_order = array(
						"QuantityOnOrder"=>$rows["QuantityOnOrder"]+$qtyrecord
				);
				//update total stock
				$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
				unset($qty_on_order);
			}
			else{
				$row = $db_global->InventoryExist($itemId);
				if($row){
					$qty_onhand   = $rows["QuantityOnOrder"]+$qtyrecord;
					$qty_on_order = array(
							"QuantityOnOrder"=>$rows["QuantityOnOrder"]+$qtyrecord
					);
					//update total stock
					$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
					unset($qty_on_order);
				}
				else{
					$addInventory= array(
							'ProdId'            => $itemId,
							'QuantityOnOrder'    => $data['qty'.$i],
							'Timestamp'         => new Zend_date()
					);
					$db_global->addRecord($addInventory,"tb_inventorytotal");
					unset($addInventory);
				}
	
			}
	
		}
	
	}
	/*for update page purchase
	 * 
	 * 
	 * */
	///page update purchase order when click update
	public function updateVendorOrder($data)
	{
		try{
			$db = $this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
		
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
		
			//for update order by id\
			$id_order_update=$data['id'];
			$info_purchase_order=array(
					"vendor_id"      => $data['v_name'],
					"LocationId"     => $data["LocationId"],
					"order"          => $data['txt_order'],
					"date_order"     => $data['order_date'],
					"status"         => 4,
					"remark"         => $data['remark'],
					"user_mod"       => $GetUserId,
					"timestamp"      => new Zend_Date(),
					"paid"           => $data['paid'],
					"balance"        => $data['remain']
			);
			//update info of order
			$db_global->updateRecord($info_purchase_order,$id_order_update,"order_id","tb_purchase_order");
		
			$sql_itm="SELECT iv.ProdId, iv.QuantityOnHand,iv.QuantityAvailable,sum(po.qty_order) AS qty_order FROM tb_purchase_order_item AS po
			INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
			$rows_order=$db_global->getGlobalDb($sql_itm);
			if($rows_order){
				foreach ($rows_order as $row_order){
					$qty_on_order = array(
							"QuantityOnHand"	=> $row_order["QuantityOnHand"]-$row_order["qty_order"],
							"QuantityAvailable"	=> $row_order["QuantityAvailable"]-$row_order["qty_order"],
							"Timestamp" 		=> new Zend_Date()
					);
					//update total stock
					$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
					
					$row_get = $db_global->porductLocationExist($row_order["ProdId"],$data["old_location"]);
					if($row_get){
						$qty_on_location = array(
								"qty"			=> $row_get["qty"]-$row_order["qty_order"],
								"last_usermod" 	=> $GetUserId,
								"last_mod_date" => new Zend_Date()
						);
						//update total stock
						$db_global->updateRecord($qty_on_location,$row_get["ProLocationID"],"ProLocationID","tb_prolocation");
					}
				}
				
			}
			unset($rows_order);
			$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
			$db_global->deleteRecords($sql);
		
			$ids=explode(',',$data['identity']);
			//add order in tb_inventory must update code again 9/8/13
			foreach ($ids as $i)
			{
				$data_item[$i]= array(
						'order_id'	  => $id_order_update,
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'price'		  => $data['price'.$i],
						'sub_total'	  => $data['total'.$i],
						'remark'	  => $data['remark_'.$i],//just add new 
				);
				$db->insert("tb_purchase_order_item", $data_item[$i]);
				unset($data_item[$i]);
				
				$locationid=$data['LocationId'];
				$itemId=$data['item_id_'.$i];
				$qtyrecord=$data['qty'.$i];//qty on 1 record
					
				$rows=$db_global -> productLocationInventory($itemId, $locationid);//to check product location
				if($rows)
				{
					$getrecord_id = $rows["ProLocationID"];
					$itemOnHand = array(
							'QuantityOnHand'   => $rows["QuantityOnHand"]+$qtyrecord,
							'QuantityAvailable'=> $rows["QuantityAvailable"]+$qtyrecord
					);
					//update total stock
					$db_global->updateRecord($itemOnHand,$itemId,"ProdId","tb_inventorytotal");
					unset($itemOnHand);
					//update stock dork
					//$newqty       = $rows['qty']-$qtyrecord;
					$updatedata=array(
							'qty' => $rows['qty']+$qtyrecord
					);
					//update stock product location
					$db_global->updateRecord($updatedata,$getrecord_id,"ProLocationID","tb_prolocation");
					unset($updatedata);
					//update stock record
				}else
				{
						//insert stock ;
						$rows_pro_exit= $db_global->productLocation($itemId, $locationid);
						if($rows_pro_exit){
							$updatedata=array(
									'qty' => $rows_pro_exit['qty']+$qtyrecord
							);
							//update stock product location
							$itemid=$db_global->updateRecord($updatedata,$rows_pro_exit['ProLocationID'], "ProLocationID", "tb_prolocation");
							unset($updatedata);
						}
						else{
							$insertdata=array(
									'pro_id'       => $itemId,
									'LocationId'   => $locationid,
									'last_usermod' => $GetUserId,
									'qty'          => $qtyrecord,
									'last_mod_date'=>new Zend_Date()
							);
							//update stock product location
							$db->insert("tb_prolocation", $insertdata);
							unset($insertdata);
						}
					
						$rowitem=$db_global->InventoryExist($itemId);//to check product location
						if($rowitem)
						{
							$itemOnHand   = array(
									'QuantityOnHand'=>$rowitem["QuantityOnHand"]+$qtyrecord,
									'QuantityAvailable'=>$rowitem["QuantityAvailable"]+$qtyrecord,
					
							);
							//update total stock
							$itemid=$db_global->updateRecord($itemOnHand,$itemId,"ProdId","tb_inventorytotal");
							unset($itemOnHand);
						}
						else
						{
							$dataInventory= array(
									'ProdId'            => $itemId,
									'QuantityOnHand'    => $qtyrecord,
									'QuantityAvailable' => $qtyrecord,
									'Timestamp'         => new Zend_date()
							);
							$db->insert("tb_inventorytotal", $dataInventory);
							unset($dataInventory);
							//update stock product location
						}
					}
			}
			
			$db->commit();
			
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	/*
	 * for update purchase order then click payment
	 * 29-13
	 * 
	 * */
	///when click payment on page update purchase order
	public function updateVendorOrderPayment($data){
	
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
	
		$session_user=new Zend_Session_Namespace('auth');
		$userName = $session_user->user_name;
		$GetUserId = $session_user->user_id;
	
// 		$idrecord=$data['v_name'];
// 		$datainfo=array(
// 				"contact_name"=>$data['contact'],
// 				"phone"       =>$data['txt_phone'],
// 				//	"add_remark"  =>$data['remark_add']
// 		);
// 		//updage customer info
// 		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
		$id_order_update=$data['id'];
		$info_order = array(
				"vendor_id"      => $data['v_name'],
				"LocationId"     => $data["LocationId"],
				"order"          => $data['txt_order'],
				"date_order"     => $data['order_date'],
				"status"         => 4,
// 				"payment_method" => $data['payment_name'],
// 				"currency_id"    => $data['currency'],
				"remark"         => $data['remark'],
				"user_mod"       => $GetUserId,
				"timestamp"      => new Zend_Date(),
				"version"        => 1,
				"net_total"      => $data['net_total'],
				"discount_type"	 => $data['discount_type'],
				"discount_value" => $data['discount_value'],
				"discount_real"  => $data['discount_real'],
				"paid"           => $data['all_total'],
				"all_total"      => $data['all_total'],
				"balance"        => 0
		);
		//update info of order not done
		$db_global->updateRecord($info_order,$id_order_update,"order_id","tb_purchase_order");
			$rows_exist=$db_global->purchaseOrderHistoryExitAll($id_order_update);
			if($rows_exist){
				foreach ($rows_exist as $id_history){
					$data_status=array(
							'status'=> 4
					);
					
					$db_global->updateRecord($data_status, $id_history['history_id'], "history_id", "tb_purchase_order_history");	
					unset($data_status);				
				}
				
			}
		unset($info_order);//if error check this here
		//and info of order
		$sql_item="SELECT iv.ProdId, iv.QuantityOnOrder,sum(po.qty_order) AS qtyorder
		FROM tb_purchase_order_item AS po
		INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
		$rows_order=$db_global->getGlobalDb($sql_item);
			if($rows_order){
				foreach ($rows_order as $row_order){
					$qty_on_order = array(
							"QuantityOnOrder"=>$row_order["QuantityOnOrder"]-$row_order["qtyorder"] ,
					);
							//update total stock
					$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
				}
			}
				unset($rows_order); unset($rows_order);
				$sql= "DELETE FROM tb_purchase_order_item WHERE order_id IN ($id_order_update)";
				$db_global->deleteRecords($sql);
				//$db->DeleteData("tb_purchase_order_item"," WHERE order_id = ".$id_order_update);
				$ids=explode(',',$data['identity']);
				$qtyonhand=0;
				foreach ($ids as $i)
				{
					$data_item[$i]= array(
					'order_id'	  => $id_order_update,
					'pro_id'	  => $data['item_id_'.$i],
					'qty_order'	  => $data['qty'.$i],
					'price'		  => $data['price'.$i],
					'total_befor' => $data['total'.$i],
					'disc_type'	  => $data['dis-type-'.$i],
					'disc_value'  => $data['dis-value'.$i],
					'sub_total'	  => $data['after_discount'.$i]
					);
					$db->insert("tb_purchase_order_item", $data_item[$i]);
						
					unset($data_item[$i]);
					//UPDATE STOCK
					//check stock product location
					$locationid=$data['LocationId'];
					$itemId=$data['item_id_'.$i];
					$qtyrecord=$data['qty'.$i];//qty on 1 record

					$rows=$db_global->inventoryLocation($locationid, $itemId);
					if($rows)
					{
						$qty_on_order = array(
								"QuantityAvailable" => $rows["QuantityAvailable"] + $data['qty'.$i] ,
								"QuantityOnHand"    => $rows["QuantityOnHand"] + $data['qty'.$i]
						);
								//update total stock
						$db_global->updateRecord($qty_on_order,$itemId,"ProdId","tb_inventorytotal");
								unset($qty_on_order);
								//update stock dork
						$newqty       = $rows["qty"]+$qtyrecord;
						$updatedata=array(
								'qty' => $newqty
						);
						//update stock product location
						$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
								unset($updatedata);
								//update stock record
					}
							else
							{
								$insertdata=array(
										'pro_id'     => $itemId,
										'LocationId' => $locationid,
										'qty'        => $qtyrecord
								);
								//update stock product location
								$db->insert("tb_prolocation", $insertdata);
								
								$rows_stock=$db_global->InventoryExist($itemId);
								if($rows_stock){
									$dataInventory= array(
											'ProdId'            => $itemId,
											'QuantityOnHand'    => $rows_stock["QuantityOnHand"]+ $data['qty'.$i],
											'QuantityAvailable' => $rows_stock["QuantityAvailable"]+$data['qty'.$i],
											'Timestamp'         => new Zend_date()
									);
									$db_global->updateRecord($dataInventory,$rows_stock["ProdId"],"ProdId","tb_inventorytotal");
									unset($dataInventory);
								}//add new to stock inventory if don't have in stock inventory
								else{
									$addInventory= array(
											'ProdId'            => $itemId,
											'QuantityOnHand'    => $data['qty'.$i],
											'QuantityAvailable' => $data['qty'.$i],
											'Timestamp'         => new Zend_date()
									);
									$db->insert("tb_inventorytotal", $addInventory);
									unset($addInventory);
								}
							}
					}
				}
				public function cancelPurchaseOrder($data){
					try{
						$db_global= new Application_Model_DbTable_DbGlobal();
						$db = $this->getAdapter();
						$db->beginTransaction();
				
						$session_user=new Zend_Session_Namespace('auth');
						$GetUserId= $session_user->user_id;
				
						$id_order_update=$data['id'];
						$sql_itm="SELECT iv.ProdId, iv.QuantityOnHand,iv.QuantityAvailable,sum(po.qty_order) AS qty_order FROM tb_purchase_order_item AS po
						INNER JOIN tb_inventorytotal AS iv ON iv.ProdId = po.pro_id WHERE po.order_id = $id_order_update GROUP BY po.pro_id";
						$rows_order=$db_global->getGlobalDb($sql_itm);
						if($rows_order){
							foreach ($rows_order as $row_order){
								$qty_on_order = array(
										"QuantityOnHand"	=> $row_order["QuantityOnHand"]-$row_order["qty_order"],
										"QuantityAvailable"	=> $row_order["QuantityAvailable"]-$row_order["qty_order"],
										"Timestamp" 		=> new Zend_Date()
								);
								$db_global->updateRecord($qty_on_order,$row_order["ProdId"],"ProdId","tb_inventorytotal");
								
								$row_get = $db_global->porductLocationExist($row_order["ProdId"],$data["old_location"]);
								if($row_get){
									$qty_on_location = array(
											"qty"			=> $row_get["qty"]-$row_order["qty_order"],
											"last_usermod" 	=> $GetUserId,
											"last_mod_date" => new Zend_Date()
									);
									$db_global->updateRecord($qty_on_location,$row_get["ProLocationID"],"ProLocationID","tb_prolocation");
								}
								
								$this->getPurchaseHistory($id_order_update, $row_order["ProdId"]);
							}
							
							$update =array("status"=>6);
							$db_global->updateRecord($update, $id_order_update,"order_id","tb_purchase_order");
						}
						$db->commit();
					}catch(Exception $e){
						$db->rollBack();
					}
				
				}
				public function getPurchaseHistory($order_id,$item_name){
					$db = $this->getAdapter();
					$sql = " SELECT history_id FROM tb_purchase_order_history 
					WHERE `order` = $order_id AND pro_id =$item_name ";
					$rows=$db->fetchAll($sql);
					$db_global= new Application_Model_DbTable_DbGlobal();
					$update =array("status"=>6);
					if(!empty($rows)){
						foreach ($rows AS $row){
							$db_global->updateRecord($update, $row["history_id"],"history_id","tb_purchase_order_history");
						}
					}
				}
	
}