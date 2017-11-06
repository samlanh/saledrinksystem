<?php

class sales_Model_DbTable_DbReturnItem extends Zend_Db_Table_Abstract
{
	public function returnItem($post)
	{
	    $db_global = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();	
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			if($post['return_no']==""){
				$date= new Zend_Date();
				$return_no="RCI".$date->get('hh-mm-ss');
			}
			else{
				$return_no=$post['return_no'];
			
			}
			$data_return = array(
					"customer_id"	=> $post["customer_id"],
					"return_no"		=> $return_no,
					"invoice_no"	=> $post["invoice_no"],
					"date_return"	=> $post["return_date"],
					"location_id"	=> $post["LocationId"],
					"remark"		=> $post["remark_return"],
					"user_mod"		=> $GetUserId,
					"timestamp"		=> new Zend_Date(),
					//"paid"			=> $post["paid"],
					"all_total"		=> $post["all_total"],
					//"balance" 		=> $post["all_total"]-$post["paid"]			
					);
			$return_id = $db_global->addRecord($data_return, "tb_return_customer_in");
			unset($data_update);
			$ids=explode(',',$post['identity']);
			foreach($ids as $i){
				$add_data = array(
						"return_id" 	=> $return_id,
						"pro_id" 		=> $post["item_id_".$i],
						"qty_return" 	=> $post["qty_return_".$i],
						"price" 		=> $post["price_".$i],
						"sub_total" 	=> $post["sub_total_".$i],
						"return_remark" => $post["remark_".$i]
				);
				$db->insert("tb_return_customer_item_in", $add_data);
				$rows=$db_global->productInvetoryLocation($post["LocationId"], $post["item_id_".$i]);
				
				if($rows){
					$updatedata=array(
							'qty' 				=> $rows["qty"]+$post["qty_return_".$i],
							'qty_avaliable' 	=> $rows["qty_avaliable"]+$post["qty_return_".$i],
							"last_usermod"		=> $GetUserId,
							"last_mod_date"		=> new Zend_Date()
					);
					//update stock product location
					$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
					unset($updatedata);			
					$qty_on_return = array(
							"qty_onhand"    => $rows["qty_onhand"] + $post["qty_return_".$i],
							"qty_available" => $rows["qty_available"] + $post["qty_return_".$i],
							"last_mod_date"			=> new zend_date()
					);
					//update total stock
					$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
					unset($qty_on_return);
							$data_history = array
								(		'transaction_type'  => 7,
										'pro_id'     		=> $post["item_id_".$i],
										'date'				=> new Zend_Date(),
										'location_id' 		=> $post["LocationId"],
										'Remark'			=> $return_no,
										'qty_edit'        	=> $post["qty_return_".$i],
										'qty_before'        => $rows["qty"],
										'qty_after'        	=> $rows["qty"]+$post["qty_return_".$i],
										'user_mod'			=> $GetUserId
								);
						$db->insert("tb_move_history", $data_history);
						unset($data_history);	
				}
				else{
					
					$insertdata=array(
							'pro_id'     		=> 		$post["item_id_".$i],
							'LocationId' 		=> 		$post["LocationId"],
							'qty'        		=> 		$post["qty_return_".$i],
							"qty_avaliable" 	=> 		$post["qty_return_".$i],
					);
					//update stock product location
					$db->insert("tb_prolocation", $insertdata);
					unset($insertdata);
						$data_history = array
								(		'transaction_type'  => 7,
										'pro_id'     		=> $post["item_id_".$i],
										'date'				=> new Zend_Date(),
										'location_id' 		=> $post["LocationId"],
										'Remark'			=> $return_no,
										'qty_edit'        	=> $post["qty_return_".$i],
										'qty_before'        => 0,
										'qty_after'        	=> $post["qty_return_".$i],
										'user_mod'			=> $GetUserId
								);
						$db->insert("tb_move_history", $data_history);//add history
						unset($data_history);	
					
					$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
					if($rows_stock){
						$dataInventory= array(
							"qty_onhand"    		=> $rows_stock["qty_onhand"] + $post["qty_return_".$i],
							"qty_available" 		=> $rows_stock["qty_available"] + $post["qty_return_".$i],
							"last_mod_date"			=> new zend_date()
						);
						$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
						unset($dataInventory);
					}
					else{
						$addInventory= array(
								'pro_id'            => $post["item_id_".$i],
								'qty_onhand'    	=> $post["qty_return_".$i],
								'qty_available' 	=> $post["qty_return_".$i],
								'last_mod_date'     => new Zend_date()
						);
						$db->insert("tb_product", $addInventory);
						unset($addInventory);
					}
					
				}
			
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
		}
  }
  
  //
  public function returnItemToCustomer($post)//for return item to customer not yet done
  {
  	$db_global = new Application_Model_DbTable_DbGlobal();
  	$db = $this->getAdapter();
  	$db->beginTransaction();
  	try{
  		$session_user=new Zend_Session_Namespace('auth');
  		$userName=$session_user->user_name;
  		$GetUserId= $session_user->user_id;
  		
  		$returnin_id = $post["return_id"];
  		//print_r($returnin_id);
  		if($post["invoice_no"]==""){
  			$date= new Zend_Date();
  			$returnout_no="RCO".$date->get('hh-mm-ss');
  		}
  		else{
  			$returnout_no=$post['invoice_no'];
  				
  		}
  		$data_return = array(
  				"returnin_id"		=> 		$post["return_id"],
  				"returnout_no"		=> 		$returnout_no,
  				"location_id"		=> 		$post["LocationId"],
  				"date_return_in"	=> 		$post["return_date_in"],
  				"date_return_out"	=> 		$post["return_out_date"],
  				"remark"			=> 		$post["remark_return"],
  				"user_mod"			=> 		$GetUserId,
  				"timestamp"			=> 		new Zend_Date(),
  				//"paid"			=> $post["paid"],
  				"all_total"			=> 		$post["all_total"],
  				//"balance" 		=> $post["all_total"]-$post["paid"]
  		);
  		$returnout_id = $db_global->addRecord($data_return, "tb_return_customer_out");
  		unset($data_update);
  		
  		$sql="SELECT 
				  c.status ,
				  c.`return_id`
				FROM
				  tb_return_customer_in AS c
				WHERE  c.`return_id` = $returnin_id";
  		$row_returnin = $db_global->getGlobalDbRow($sql);
  		
  		$update_return= array(
  			
  				"status"	=>	0
  		);
  		$db_global->updateRecord($update_return, $row_returnin["return_id"], "return_id", "tb_return_customer_in");
  		unset($update_return);
  		
 
  		
  		$ids=explode(',',$post['identity']);
  		foreach($ids as $i){
  			$add_data = array(
  					"return_id" 	=> $returnout_id,
  					"pro_id" 		=> $post["item_id_".$i],
  					"qty_return" 	=> $post["qty_return_".$i],
  					"price" 		=> $post["price_".$i],
  					"sub_total" 	=> $post["sub_total_".$i],
  					"return_remark" => $post["remark_".$i]
  			);
  			$db->insert("tb_return_customer_item_out", $add_data);
  			
  			$rows=$db_global->productInvetoryLocation($post["LocationId"], $post["item_id_".$i]);
  
  			if($rows){
  				$updatedata=array(
  						'qty' 				=> $rows["qty"]-$post["qty_return_".$i],
  						'qty_avaliable'		=> $rows["qty_avaliable"] - $post["qty_return_".$i],
  						"last_usermod"		=> $GetUserId,
  						"last_mod_date"		=> new Zend_Date()
  				);
  				//update stock product location
  				$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
  				unset($updatedata);
  				
  				$qty_on_return = array(
  						"qty_onhand"    		=> $rows["qty_onhand"] - $post["qty_return_".$i],
  						"qty_available" 		=> $rows["qty_available"] - $post["qty_return_".$i],
  						"last_mod_date"			=> new zend_date()
  				);
  				//update total stock
  				$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
  				unset($qty_on_return);
  				
  				//not info for return item to customer
  				$data_history = array (		
  						'transaction_type'  => 6,
						'pro_id'     		=> $post["item_id_".$i],
						'date'				=> new Zend_Date(),
						'location_id' 		=> $post["LocationId"],
						'Remark'			=> $returnout_no,
						'qty_edit'        	=> $post["qty_return_".$i],
						'qty_before'        => $rows["qty"],
						'qty_after'        	=> $rows["qty"]-$post["qty_return_".$i],
						'user_mod'			=> $GetUserId
				);
				$db->insert("tb_move_history", $data_history);
				unset($data_history);	
  			}
  			else{
  					
  				$insertdata=array(
  						'pro_id'     			=> $post["item_id_".$i],
  						'LocationId' 			=> $post["LocationId"],
  						'qty'        			=> -$post["qty_return_".$i],
  						'qty_avaliable'         => -$post["qty_return_".$i]
  				);
  				//update stock product location
  				$db->insert("tb_prolocation", $insertdata);
  				unset($insertdata);
  				
  				//not info for return item to customer
  				$data_history = array (
  						'transaction_type'  => 6,
						'pro_id'     		=> $post["item_id_".$i],
						'date'				=> new Zend_Date(),
						'location_id' 		=> $post["LocationId"],
						'Remark'			=> $returnout_no,
						'qty_edit'        	=> $post["qty_return_".$i],
						'qty_before'        => 0,
						'qty_after'        	=> -$post["qty_return_".$i],
						'user_mod'			=> $GetUserId
				);
				$db->insert("tb_move_history", $data_history);
				unset($data_history);	
  					
  				$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
  				if($rows_stock){
  					$dataInventory= array(
  						"qty_onhand"   	 		=> $rows_stock["qty_onhand"] - $post["qty_return_".$i],
  						"qty_available" 		=> $rows_stock["qty_available"] - $post["qty_return_".$i],
  						"last_mod_date"			=> new zend_date()
  					);
  					$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
  					unset($dataInventory);
  				}
  				else{
  					$addInventory= array(
  							'pro_id'            	=> 		$post["item_id_".$i],
  							'qty_onhand'    		=> 		-$post["qty_return_".$i],
  							'qty_available' 		=> 		-$post["qty_return_".$i],
  							'last_mod_date'         => 		new Zend_date()
  					);
  					$db->insert("tb_product", $addInventory);
  					unset($addInventory);
  				}
  			}
  				
  		}
  		$db->commit();
  	}catch(Exception $e){
  		$db->rollBack();
  		$e->getMessage();
  	}
   }
   
   public function updateReturnItemToCustomer($post)//for return item to customer not yet done
   {
   	try{
   	$db_global = new Application_Model_DbTable_DbGlobal();
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   
   		$session_user=new Zend_Session_Namespace('auth');
   		$userName=$session_user->user_name;
   		$GetUserId= $session_user->user_id;
   		$return_id = $post["id"];
   		//print_r($return_id);exit();
   		
//    		$db->getProfiler()->setEnabled(true);
   		$data_return = array(
   				"returnin_id"		=> 		$post["returnin_id"],
   				//"returnout_no"		=> 		$returnout_no,
   				"location_id"		=> 		$post["LocationId"],
   				"date_return_in"	=> 		$post["return_date_in"],
   				"date_return_out"	=> 		$post["return_out_date"],
   				"remark"			=> 		$post["remark_return"],
   				"user_mod"			=> 		$GetUserId,
   				"timestamp"			=> 		new Zend_Date(),
   				//"paid"			=> $post["paid"],
   				"all_total"			=> 		$post["all_total"],
   				//"balance" 		=> $post["all_total"]-$post["paid"]
   		);
   		$returnout_id = $db_global->updateRecord($data_return,$return_id, "returnout_id",  "tb_return_customer_out");
   		unset($data_return);
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    		$db->getProfiler()->setEnabled(false);
   	
//    		$db->getProfiler()->setEnabled(true);
   		$sql_item="SELECT 
					  co.`returnout_id`,
					  co.`location_id`,
					  rco.`pro_id`,
					  rco.`qty_return`,
					  p.`qty_onhand`,
					  p.`qty_available` 
					FROM
					  tb_return_customer_item_out AS rco,
					  tb_return_customer_out AS co,
					  tb_product AS p 
					WHERE rco.`return_id` = co.`returnout_id` 
					AND rco.`pro_id`=p.`pro_id`
					  AND co.`returnout_id` = $return_id";
   		 
   		$rows_return=$db_global->getGlobalDb($sql_item);
   		//print_r($rows_return);exit();
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    		$db->getProfiler()->setEnabled(false);
   		
   		if($rows_return){
   			foreach ($rows_return as $row_return){
   					
//    				$db->getProfiler()->setEnabled(true);
   				$qty_on_order = array(
   						"qty_onhand"	=> $row_return["qty_onhand"] + $row_return["qty_return"],
   						"qty_available"	=> $row_return["qty_available"] + $row_return["qty_return"],
   						"last_mod_date"			=> new zend_date()
   				);
   				//update total stock
   				$db_global->updateRecord($qty_on_order,$row_return["pro_id"],"pro_id","tb_product");
   				unset($qty_on_order);
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    				$db->getProfiler()->setEnabled(false);
   		
   					
   				$rowitem_exist=$db_global->porductLocationExist($row_return["pro_id"], $row_return["location_id"]);
   				if($rowitem_exist){
//    					$db->getProfiler()->setEnabled(true);
   					$updatedata=array(
   							'qty' 				=> $rowitem_exist["qty"]+$row_return["qty_return"],
   							"qty_avaliable"		=>	$rowitem_exist["qty_avaliable"] + $row_return["qty_return"],
   							"last_usermod"		=> $GetUserId,
   							"last_mod_date"		=> new Zend_Date()
   					);
   					//update stock product location
   					$db_global->updateRecord($updatedata,$rowitem_exist["ProLocationID"],"ProLocationID","tb_prolocation");
   					unset($updatedata);
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    					$db->getProfiler()->setEnabled(false);
   				
   		
   				}
   					
   			}
   		}
   		
//    		$db->getProfiler()->setEnabled(true);
   		$sql= "DELETE FROM tb_return_customer_item_out WHERE return_id IN ($return_id)";
   		$db_global->deleteRecords($sql);
   		
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    		$db->getProfiler()->setEnabled(false);
   		
   		$ids=explode(',',$post['identity']);
   		
   		foreach($ids as $i){
   			
    			$db->getProfiler()->setEnabled(true);
   			
   			$date_return =array(
   				
   					"return_id"			=> 		$return_id,
   					"pro_id"			=>		$post["item_id_".$i],
   					"qty_return"		=>		$post["qty_return_".$i],
   					"price"				=>		$post["price_".$i],
   					"sub_total"			=>		$post["sub_total_".$i],
   					"return_remark"		=>		$post["remark_".$i],
   			);
   			
   			$db->insert("tb_return_customer_item_out", $date_return);
   		
   			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
   			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
   			$db->getProfiler()->setEnabled(false);//exit();
   				
   			$rows=$db_global->productInvetoryLocation($post["LocationId"], $post["item_id_".$i]);
   
   			if($rows){
//    				$db->getProfiler()->setEnabled(true);
   				
   				$updatedata=array(
   						'qty' 				=> $rows["qty"]-$post["qty_return_".$i],
   						'qty_avaliable'		=> $rows["qty_avaliable"] - $post["qty_return_".$i],
   						"last_usermod"		=> $GetUserId,
   						"last_mod_date"		=> new Zend_Date()
   				);
   				//update stock product location
   				$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
   				unset($updatedata);
   			
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    				$db->getProfiler()->setEnabled(false);
   				
//    				$db->getProfiler()->setEnabled(true);
   				$qty_on_return = array(
   						"qty_onhand"    		=> $rows["qty_onhand"] - $post["qty_return_".$i],
   						"qty_available" 		=> $rows["qty_available"] - $post["qty_return_".$i],
   						"last_mod_date"			=> new zend_date()
   				);
   				//update total stock
   				$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
   				unset($qty_on_return);
   	
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    				$db->getProfiler()->setEnabled(false);
   
   				//not info for return item to customer
//    				$db->getProfiler()->setEnabled(true);
   				$data_history = array (
   						'transaction_type'  => 6,
   						'pro_id'     		=> $post["item_id_".$i],
   						'date'				=> new Zend_Date(),
   						'location_id' 		=> $post["LocationId"],
   						'Remark'			=> $post["remark_".$i],
   						'qty_edit'        	=> $post["qty_return_".$i],
   						'qty_before'        => $rows["qty"],
   						'qty_after'        	=> $rows["qty"]-$post["qty_return_".$i],
   						'user_mod'			=> $GetUserId
   				);
   				$db->insert("tb_move_history", $data_history);
   				unset($data_history);
   				
//    			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 			$db->getProfiler()->setEnabled(false);
   			}
   			else{
   				$db->getProfiler()->setEnabled(true);
   				$insertdata=array(
   						'pro_id'     			=> $post["item_id_".$i],
   						'LocationId' 			=> $post["LocationId"],
   						'qty'        			=> -$post["qty_return_".$i],
   						'qty_avaliable'         => -$post["qty_return_".$i]
   				);
   				//update stock product location
   				$db->insert("tb_prolocation", $insertdata);
   				unset($insertdata);
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    				$db->getProfiler()->setEnabled(false);
   			
   				//not info for return item to customer
//    				$db->getProfiler()->setEnabled(true);
   				$data_history = array (
   						'transaction_type'  => 6,
   						'pro_id'     		=> $post["item_id_".$i],
   						'date'				=> new Zend_Date(),
   						'location_id' 		=> $post["LocationId"],
   						'Remark'			=> $post["remark_".$i],
   						'qty_edit'        	=> $post["qty_return_".$i],
   						'qty_before'        => 0,
   						'qty_after'        	=> -$post["qty_return_".$i],
   						'user_mod'			=> $GetUserId
   				);
   				$db->insert("tb_move_history", $data_history);
   				unset($data_history);
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    				$db->getProfiler()->setEnabled(false);
   			
   					
   				$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
   				if($rows_stock){
//    					$db->getProfiler()->setEnabled(true);
   					$dataInventory= array(
   							"qty_onhand"   	 		=> $rows_stock["qty_onhand"] - $post["qty_return_".$i],
   							"qty_available" 		=> $rows_stock["qty_available"] - $post["qty_return_".$i],
   							"last_mod_date"			=> new zend_date()
   					);
   					$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
   					unset($dataInventory);
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    					$db->getProfiler()->setEnabled(false);
   				}
   				else{
//    					$db->getProfiler()->setEnabled(true);
   					$addInventory= array(
   							'pro_id'            	=> 		$post["item_id_".$i],
   							'qty_onhand'    		=> 		-$post["qty_return_".$i],
   							'qty_available' 		=> 		-$post["qty_return_".$i],
   							'last_mod_date'         => 		new Zend_date()
   					);
   					$db->insert("tb_product", $addInventory);
   					unset($addInventory);
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//    					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//    					$db->getProfiler()->setEnabled(false);
   				}
   			}
   		}
   		//exit();
   		$db->commit();
   	}catch(Exception $e){
   		$db->rollBack();
   		$e->getMessage();
   	}
   }
  public function updateReturnItem($post){	
  	try{
  	$db = $this->getAdapter();
  	$db->beginTransaction();
  	$db_global = new Application_Model_DbTable_DbGlobal();
  	$session_user=new Zend_Session_Namespace('auth');
  	$userName=$session_user->user_name;
  	$GetUserId= $session_user->user_id;
  	$return_id = $post["id"];
  	$db->getProfiler()->setEnabled(true);
			$data_update = array(
					"customer_id"		=> 		$post["customer_id"],
					"date_return"		=> 		$post["return_date"],
					"location_id"		=>		$post["LocationId"],
					"remark"			=> 		$post["remark_return"],
					"user_mod"			=> 		$GetUserId,
					"timestamp"			=> 		new Zend_Date(),
					"all_total"			=> 		$post["all_total"],
					//"return_no"		=> $post['retun_order'],
					//"paid"			=> $post["paid"],
					//"payment_method"=> $post["payment_name"],
					//"currency_id"	=> $post["currency"],
					//"balance" 		=> $post["all_total"]-$post["paid"]			
					);
			 $db_global->updateRecord($data_update, $return_id, "return_id", "tb_return_customer_in");
			unset($data_update);
			
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			$db->getProfiler()->setEnabled(false);
		/////
  	
  	$sql_item="SELECT 
				  rci.`return_id`,
				  rci.`location_id`,
				  rct.`pro_id`,
				  rct.`qty_return`,
				  p.`qty_onhand`,
				  p.`qty_available` 
				FROM
				  tb_return_customer_in AS rci,
				  tb_return_customer_item_in AS rct,
				  tb_product AS p 
				WHERE rct.`return_id` = rci.`return_id` 
				  AND rct.`pro_id` = p.`pro_id` 
				  AND rci.`return_id` = $return_id";
  	
  	$rows_return=$db_global->getGlobalDb($sql_item);
  	if($rows_return){
  		foreach ($rows_return as $row_return){
  			
  			$db->getProfiler()->setEnabled(true);
  			$qty_on_order = array(
  					"qty_onhand"	=> $row_return["qty_onhand"] + $row_return["qty_return"],
  					"qty_available"	=> $row_return["qty_available"] + $row_return["qty_return"],
  					"last_mod_date"			=> new zend_date()
  			);
  			//update total stock
  			$db_global->updateRecord($qty_on_order,$row_return["pro_id"],"pro_id","tb_product");
  			unset($qty_on_order);
  			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  			$db->getProfiler()->setEnabled(false);
  			
  			$rowitem_exist=$db_global->porductLocationExist($row_return["pro_id"], $row_return["location_id"]);
  			if($rowitem_exist){
  				$db->getProfiler()->setEnabled(true);
  				$updatedata=array(
  						'qty' 				=> $rowitem_exist["qty"]+$row_return["qty_return"],
  						"qty_avaliable"		=>	$rowitem_exist["qty_avaliable"] + $row_return["qty_return"],
  						"last_usermod"		=> $GetUserId,
  						"last_mod_date"		=> new Zend_Date()
  				);
  				//update stock product location
  				$db_global->updateRecord($updatedata,$rowitem_exist["ProLocationID"],"ProLocationID","tb_prolocation");
  				unset($updatedata);
  				
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  				$db->getProfiler()->setEnabled(false);
  				
  			}
  			
  		}
  	}
  	$db->getProfiler()->setEnabled(true);
	  	$sql= "DELETE FROM tb_return_customer_item_in WHERE return_id IN ($return_id)";
	  	$db_global->deleteRecords($sql);
	  	
	  	Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	  	Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	  	$db->getProfiler()->setEnabled(false);
  	
  	$ids=explode(',',$post['identity']);
  	//add order in tb_inventory must update code again 9/8/13
  	foreach ($ids as $i)
  	{
  		$db->getProfiler()->setEnabled(true);
  				$add_data = array(
					"return_id" 	=> $return_id,
					"pro_id" 		=> $post["item_id_".$i],
					//"location_id"	=> $post["LocationId_".$i],
					"qty_return" 	=> $post["qty_return_".$i],
					"price" 		=> $post["price_".$i],
					"sub_total" 	=> $post["sub_total_".$i],
					"return_remark" => $post["remark_".$i]
			);
			$db->insert("tb_return_customer_item_in", $add_data);
			
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
			$db->getProfiler()->setEnabled(false);
			
			$rows=$db_global->productInvetoryLocation($post["LocationId"], $post["item_id_".$i]);
			
			if($rows){
				$db->getProfiler()->setEnabled(true);
				$updatedata=array(
						'qty' 				=> $rows["qty"]-$post["qty_return_".$i],
						'qty_avaliable'		=>	$rows["qty_avaliable"] - $post["qty_return_".$i],
						"last_usermod"		=> $GetUserId,
						"last_mod_date"		=> new Zend_Date()
				);
				//update stock product location
				$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
				unset($updatedata);		
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);

				$db->getProfiler()->setEnabled(true);
				$qty_on_return = array(
						"qty_onhand"    => $rows["qty_onhand"] - $post["qty_return_".$i],
						"qty_available" => $rows["qty_available"] - $post["qty_return_".$i],
						"last_mod_date"			=> new zend_date()
				);
				//update total stock
				$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
				unset($qty_on_return);
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
				//add return history
				
				$db->getProfiler()->setEnabled(true);
						$data_history = array
						(		'transaction_type'  => 4,
								'pro_id'     		=> $post["item_id_".$i],
								'date'				=> new Zend_Date(),
								//'location_id' 		=> $post["LocationId_".$i],
								'Remark'			=> $post['remark_'.$i],
								'qty_edit'        	=> $post["qty_return_".$i],
								'qty_before'        => $rows["qty"],
								'qty_after'        	=> $rows["qty"]-$post["qty_return_".$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($data_history);
						
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
						$db->getProfiler()->setEnabled(false);
				
			}
			else{
				$db->getProfiler()->setEnabled(true);
				$insertdata=array(
						'pro_id'     			=> 		$post["item_id_".$i],
						'LocationId' 			=> 		$post["LocationId_".$i],
						'qty'        			=> 		-$post["qty_return_".$i],
						'qty_avaliable'        	=> 		-$post["qty_return_".$i]
				);
				//update stock product location
				$db->insert("tb_prolocation", $insertdata);
				unset($insertdata);
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
				//add return history
				
				$db->getProfiler()->setEnabled(true);
				$data_history = array
				(		'transaction_type'  => 4,
						'pro_id'     		=> $post["item_id_".$i],
						'date'				=> new Zend_Date(),
						'location_id' 		=> $post["LocationId_".$i],
						'Remark'			=> $post['remark_'.$i],
						'qty_edit'        	=> $post["qty_return_".$i],
						'qty_before'        => 0,
						'qty_after'        	=> -$post["qty_return_".$i],
						'user_mod'			=> $GetUserId
				);
				$db->insert("tb_move_history", $data_history);
				unset($data_history);
				
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
				$db->getProfiler()->setEnabled(false);
				
				$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
				if($rows_stock){
					
					$db->getProfiler()->setEnabled(true);
					$dataInventory= array(
							'qty_onhand'    => $rows_stock["qty_onhand"]- $post["qty_return_".$i],
							'qty_available' => $rows_stock["qty_available"] - $post["qty_return_".$i],
							'last_mod_date'         => new Zend_date()
					);
					$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
					unset($dataInventory);
					
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
				}
				else{
					$db->getProfiler()->setEnabled(true);
					$addInventory= array(
							'pro_id'            => $post["item_id_".$i],
							'qty_onhand'    => -$post["qty_return_".$i],
							'qty_available' => -$post["qty_return_".$i],
							'last_mod_date'         => new Zend_date()
					);
					$db->insert("tb_product", $addInventory);
					unset($addInventory);
					
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
					$db->getProfiler()->setEnabled(false);
				}
				
			}
  	
  	}
  	$db->commit();
   }catch(Exception $e){
	   	$db->rollBack();
	   	$e->getMessage();
   }
  }
	
}