<?php

class purchase_Model_DbTable_DbReturnItem extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
// 	static $_name="tb_return";
// 	public function setName($name){
// 		$this->_name=$name;
// 	}
	public function returnItem($post)
	{
		try{
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();	
		$db->beginTransaction();
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		
// 		$idrecord=$post['v_name'];
// 		$datainfo=array(
// 				"contact_name" => $post['contact'],
// 				"phone"        => $post['txt_phone'],
// 				"add_name"     => $post["vendor_address"]
// 		);
// 		//updage vendor info
// 		$itemid=$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
// 		unset($datainfo);
		if($post['retun_order']==""){
			$date= new Zend_Date();
			$return_no="RVO".$date->get('hh-mm-ss');
		}
		else{
			$return_no=$post['retun_order'];
		
		}
		$ids=explode(',',$post['identity']);
		foreach($ids as $i){
			
			$rows=$db_global->productInvetoryLocation($post["LocationId"], $post["item_id_".$i]);
			//print_r($rows);exit();
			$qty = $rows["qty"]-$post["qty_return_".$i];
			$qty_onhand = $rows["qty_onhand"]-$post["qty_return_".$i];
			if($rows){
				if($qty_onhand<0){
					Application_Form_FrmMessage::message("Your product stock is less than return");
					Application_Form_FrmMessage::redirectUrl("/purchase/return");
					//exit();
				}elseif($qty<0){
					Application_Form_FrmMessage::message("You Items is less than item return");
					Application_Form_FrmMessage::redirectUrl("/purchase/return");
					//exit();
				}
				else{
					$qty_on_return = array(
							"qty_onhand"    => $rows["qty_onhand"] - $post["qty_return_".$i],
							"qty_available"    => $rows["qty_available"] - $post["qty_return_".$i],
							"last_mod_date"			=> new zend_date()
					);
					//update total stock
					$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
					unset($qty_on_return);
					
				
					$updatedata=array(
							'qty' 				=> $rows["qty"]-$post["qty_return_".$i],
							"last_usermod"		=> $GetUserId,
							"last_mod_date"		=> new Zend_Date()
					);
					//update stock product location
					$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
					unset($updatedata);
				}
				//add return history
						$data_history = array
						(		'transaction_type'  => 4,
								'pro_id'     		=> $post["item_id_".$i],
								'date'				=> new Zend_Date(),
								'location_id' 		=> $post["LocationId"],
								'Remark'			=> $return_no,
								'qty_edit'        	=> $post["qty_return_".$i],
								'qty_before'        => $rows["qty"],
								'qty_after'        	=> $rows["qty"]-$post["qty_return_".$i],
								'user_mod'			=> $GetUserId
						);
						$db->insert("tb_move_history", $data_history);
						unset($data_history);	
				
			}
			else{
				Application_Form_FrmMessage::message("Your product in stock is not exist");
				Application_Form_FrmMessage::redirectUrl("/purchase/return");
				//exit();
				
// 				$insertdata=array(
// 						'pro_id'     => $post["item_id_".$i],
// 						'LocationId' => $post["LocationId"],
// 						'qty'        => -$post["qty_return_".$i]
// 				);
// 				//update stock product location
// 				$db->insert("tb_prolocation", $insertdata);
// 				unset($insertdata);
// 				//add return history
// 				$data_history = array
// 				(		'transaction_type'  => 4,
// 						'pro_id'     		=> $post["item_id_".$i],
// 						'date'				=> new Zend_Date(),
// 						'location_id' 		=> $post["LocationId"],
// 						'Remark'			=> $return_no,
// 						'qty_edit'        	=> $post["qty_return_".$i],
// 						'qty_before'        => 0,
// 						'qty_after'        	=> -$post["qty_return_".$i],
// 						'user_mod'			=> $GetUserId
// 				);
// 				$db->insert("tb_move_history", $data_history);
// 				unset($data_history);
				
// 				$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
// 				if($rows_stock){
// 					$dataInventory= array(
// 							'qty_onhand'    => $rows_stock["qty_onhand"]- $post["qty_return_".$i],
// 							//'QuantityAvailable' => $rows_stock["QuantityAvailable"] - $post["qty_return_".$i],
// 							'last_mod_date'         => new Zend_date()
// 					);
// 					$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
// 					unset($dataInventory);
// 				}
// 				else{
// 					$addInventory= array(
// 							'pro_id'            => $post["item_id_".$i],
// 							'qty_onhand'    => -$post["qty_return_".$i],
// 							//'QuantityAvailable' => -$post["qty_return_".$i],
// 							'last_mod_date'         => new Zend_date()
// 					);
// 					$db->insert("tb_product", $addInventory);
// 					unset($addInventory);
// 				}
				
			}
		}
			
			$data_update = array(
					"vendor_id"		=> $post["v_name"],
					"location_id" 	=> $post["LocationId"],
					"return_no"		=> $return_no,
					"date_return"	=> $post["return_date"],
					"remark"		=> $post["return_remark"],
					"user_mod"		=> $GetUserId,
					"timestamp"		=> new Zend_Date(),
					//"paid"			=> $post["paid"],
					"all_total"		=> $post["all_total"],
					"is_active"		=> 1
					//"balance" 		=> $post["all_total"]-$post["paid"]
			);
			$return_id = $db_global->addRecord($data_update, "tb_return");
			unset($data_update);
			foreach ($ids as $i){
				$add_data = array(
						"return_id" 	=> $return_id,
						"pro_id" 		=> $post["item_id_".$i],
						"qty_return" 	=> $post["qty_return_".$i],
						"price" 		=> $post["price_".$i],
						"sub_total" 	=> $post["sub_total_".$i],
						"return_remark" => $post["remark_".$i]
				);
				$db->insert("tb_return_vendor_item", $add_data);
				
				$return_history = array(
						
						"return_id"		=>	$return_id,
						"return_no" 	=>	$return_no,
						"pro_id"		=>	$post["item_id_".$i],
						"location_id"	=>	$post["LocationId"],
						"return_type"	=>	1,
						"vendor_id"		=>	$post["v_name"],
						"return_date"	=>	$post["return_date"],
						"qty_return"	=>	$post["qty_return_".$i],
						"price"			=>	$post["price_".$i],
						"total_amount"	=>	$post["sub_total_".$i],
						"user_mod"		=> 	$GetUserId,
						"remark"		=>	$post["remark_".$i]
				);
				$this->_name = "tb_return_history";
				$this->insert($return_history);
				unset($return_history);
			}
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
  		 
  		$idrecord=$post['v_name'];
  		$return_id = $post["id"];
  		$old_location = $post["old_location"];
  		//print_r($old_location);exit();
  		
  		$sql_item="SELECT
		  		(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS pro_id
		  		
		  		,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_onorder
		  			
		  		,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_onhand
		  			
		  		,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_available
		  		
		  		, SUM(rvi.`qty_return`) AS qty_return FROM
  			
  		tb_return_vendor_item AS rvi WHERE rvi.return_id = $return_id GROUP BY rvi.pro_id";
  			
  		$rows_return=$db_global->getGlobalDb($sql_item);
  		if($rows_return){
  			foreach ($rows_return as $row){
  				$db->getProfiler()->setEnabled(true);
  				
  				$rowitem_exist=$db_global->porductLocationExist($row["pro_id"], $old_location);
  				
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  				$db->getProfiler()->setEnabled(false);
  				//print_r($rowitem_exist);exit();
  				
  				if($rowitem_exist){
  					$db->getProfiler()->setEnabled(true);
	  				$old_qty_pro = array(
	  						'qty' 				=> $rowitem_exist["qty"]+$row["qty_return"],
	  						"last_usermod"		=> $GetUserId,
	  						"last_mod_date"		=> new Zend_Date()
	  				);
	  				$this->_name='tb_prolocation';
	  				$where="ProLocationID=".$rowitem_exist["ProLocationID"];
	  				$this->update($old_qty_pro, $where);
	  				//$update_qty_pro = $db_global->updateRecord($old_qty_pro, $rowitem_exist["ProLocationID"], "ProLocationID", "tb_prolocation");
	  				
	  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	  				$db->getProfiler()->setEnabled(false);
	  				//exit();
	  					
	  				$db->getProfiler()->setEnabled(true);
	  					
	  				$old_qty = array(
	  						"qty_onhand"		=> $row["qty_onhand"] + $row["qty_return"],
	  						"qty_available"		=> $row["qty_available"] + $row["qty_return"],
	  						"last_mod_date"		=> new Zend_date()
	  				);
	  				$this->_name='tb_product';
	  				$where="pro_id=".$row["pro_id"];
	  				$this->update($old_qty, $where);
	  				//$update_qty = $db_global->updateRecord($old_qty, $row["pro_id"], "pro_id", "tb_product");
	  					
	  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
	  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
	  				$db->getProfiler()->setEnabled(false);
  				//exit();
  				}	
  			}
  		}
  
  		
  //*************************** Update Tb_return *************************
  		$db->getProfiler()->setEnabled(true);
  		
  		$data_update = array(
  				"vendor_id"		=> $post["v_name"],
  				"location_id"	=> $post["LocationId"],
  				"date_return"	=> $post["return_date"],
  				"remark"		=> $post["return_remark"],
  				"user_mod"		=> $GetUserId,
  				"timestamp"		=> new Zend_Date(),
  				"all_total"		=> $post["all_total"],
  		);
  		$this->_name='tb_return';
  		$where = "return_id=".$return_id;
  		$this->update($data_update, $where);
  		unset($data_update);
  		
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  		$db->getProfiler()->setEnabled(false);
  		
  //************************** Delete tb_return_vendor_item*****************************
  		$db->getProfiler()->setEnabled(true);
  		
  		$this->_name='tb_return_vendor_item';
  		$where ='return_id='.$return_id;
  		$this->delete($where);
  		
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  		$db->getProfiler()->setEnabled(false);
  //************************** Delete tb_return_history ****************************
  		$db->getProfiler()->setEnabled(true);
  		
  		$this->_name='tb_return_history';
  		$where ='return_id='.$return_id;
  		$this->delete($where);
  		
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  		$db->getProfiler()->setEnabled(false);
  		
  //************************** Delete tb_move_history ****************************
//   		$db->getProfiler()->setEnabled(true);
  		
//   		$this->_name='tb_move_history';
//   		$where ='return_id='.$return_id;
//   		$this->delete($where);
  		
//   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//   		Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//   		$db->getProfiler()->setEnabled(false);
  		
  //************************** Update new qty ****************************
  
  		$ids=explode(',',$post['identity']);
  		
  		//echo $post['identity'];
  		//print_r($ids);//exit();
  		if($post['identity']==""){
  			//echo "hello";//exit();
   			Application_Form_FrmMessage::message("Please ADD ITEM lees than 1!!");
  			//Application_Form_FrmMessage::redirectUrl($url);
  			//exit();
  		}else{
  		//print_r($ids);exit();
  		foreach ($ids as $i){
  			//echo $post["item_id_".$i];exit();
  			//echo "helo *************abc************".print_r($post["item_id_".$i]);
  			$db->getProfiler()->setEnabled(true);
  			$rows=$db_global->inventoryLocation($post["LocationId"], $post["item_id_".$i]);
  			//print_r($rows);
  			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  			$db->getProfiler()->setEnabled(false);
  			$qty = $rows["qty"] - $post["qty_return_".$i];
  			$qty_onhand = $rows["qty_onhand"] - $post["qty_return_".$i];
  			
  			if($rows){
  				if($qty<0 OR $qty_onhand<0){
  					Application_Form_FrmMessage::message("Product Stock or Product in your Location is less than qty return");
  					$db->rollBack();
  					exit();
  				}else{
  					
  					//echo "Hello *************************************************************";
  					$db->getProfiler()->setEnabled(true);
  					
  					$qty_on_return = array(
  							"qty_onhand"    		=> $qty_onhand,
  							"qty_available"    		=> $qty_onhand,
  							"last_mod_date"			=> new Zend_date()
  					);
  					$this->_name='tb_product';
  					$where="pro_id=".$post["item_id_".$i];
  					$this->update($qty_on_return, $where);
  					unset($qty_on_return);
  					
  					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  					$db->getProfiler()->setEnabled(false);
  					//exit();
  					
  					$db->getProfiler()->setEnabled(true);
  					
  					$updatedata=array(
  							'qty' 				=> $qty,
  							"last_usermod"		=> $GetUserId,
  							"last_mod_date"		=> new Zend_Date()
  					);
  					$this->_name='tb_prolocation';
  					$where="ProLocationID=".$rows["ProLocationID"];
  					$this->update($updatedata, $where);
  					unset($updatedata);
  					
  					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  					$db->getProfiler()->setEnabled(false);
  					
  				}
  				
  		//*************************Insert New return_vendir_item***************************
  				$db->getProfiler()->setEnabled(true);
  				
  				$add_data = array(
  						"return_id" 	=> $return_id,
  						"pro_id" 		=> $post["item_id_".$i],
  						"qty_return" 	=> $post["qty_return_".$i],
  						"price" 		=> $post["price_".$i],
  						"sub_total" 	=> $post["sub_total_".$i],
  						"return_remark" => $post["remark_".$i]
  				);
  				$this->_name='tb_return_vendor_item';
  				$this->insert($add_data);
  				unset($add_data);
  				//$db->insert("tb_return_vendor_item", $add_data);
  				
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  				$db->getProfiler()->setEnabled(false);
  				
  	//******************************Insert New tb_rerturn_history**************************************
  				$db->getProfiler()->setEnabled(true);
  				
  				$history = array(
  						"return_id"		=> $return_id,
  						"return_no"		=> $post["retun_order"],
  						"pro_id"		=> $post["item_id_".$i],
  						"location_id"	=> $post["LocationId"],
  						"return_type"	=>2,
  						"vendor_id"		=>1,
  						"return_date"	=>$post["return_date"],
  						"qty_return"	=> $post["qty_return_".$i],
  						"price"			=> $post["price_".$i],
  						"total_amount"	=> $post["sub_total_".$i],
  						"user_mod"		=> $GetUserId,
  						"remark"		=> $post["return_remark"]
  				);
  				$this->_name='tb_return_history';
  				$this->insert($history);
  				//$db->insert("tb_return_history", $history);
  				unset($history);
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  				$db->getProfiler()->setEnabled(false);
  				
  				
  	//*****************************Insert new tb_move history***************************************
  				$db->getProfiler()->setEnabled(true);
  				
  				$data_history = array(
  						'transaction_type'  => 	4,
  						'pro_id'     		=> 	$post["item_id_".$i],
  						'date'				=> 	new Zend_Date(),
  						'location_id' 		=> 	$post["LocationId"],
  						'Remark'			=> 	$post['remark_'.$i],
  						'qty_edit'        	=> 	$post["qty_return_".$i],
  						'qty_before'        => 	$rows["qty"],
  						'qty_after'        	=> 	$rows["qty"]-$post["qty_return_".$i],
  						'user_mod'			=> 	$GetUserId
  				);
  				$this->_name='tb_move_history';
  				$this->insert($data_history);
  				//$db->insert("tb_move_history", $data_history);
  				unset($data_history);
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
  				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
  				$db->getProfiler()->setEnabled(false);
  				
  	  				//exit();
  			}else {
  				$db->rollBack();
  				return -1;
  				//echo "error";
  				exit();
  			}
  		}
  		}
  	$db->commit();
  	}catch (Exception $e){
  		$db->rollBack();
  		echo $e->getMessage();
  		//exit();
  		
  	}
  }

  
  //get product after item out form stock
  public function getReturnItemIn($post)
  {
  	try{
  		$db_global = new Application_Model_DbTable_DbGlobal();
  		$db = $this->getAdapter();
  		$db->beginTransaction();
  		$session_user=new Zend_Session_Namespace('auth');
  		$userName=$session_user->user_name;
  		$GetUserId= $session_user->user_id;
  		  
  		if($post['retun_order']==""){
  			$date= new Zend_Date();
  			$order_no="RVI".$date->get('hh-mm-ss');
  		}
  		else{
  			$order_no=$post['retun_order'];
  		}
  		$data_update = array(
  				"returnout_id"		=> 		$post["return_reference"],
  				"location_id" 		=>		$post["LocationId"],
  				"returnin_no"		=> 		$order_no,
  				//"date_return"		=> 		$post["return_date"],
  				"remark"			=> 		$post["return_remark"],
  				"user_mod"			=> 		$GetUserId,
  				"timestamp"			=> 		new Zend_Date(),
  				"date_return_out"	=> 		$post["return_date"],
  				"date_return_in"	=> 		$post["return_date"],
  				"vendor_id"			=>		$post["v_name"],
  				"all_total"			=> 		$post["all_total"],
  				//"balance" 		=> $post["all_total"]-$post["paid"]
  		);
  		$return_id = $db_global->addRecord($data_update, "tb_return_vendor_in");
  		unset($data_update);
  		
  		$update_return = array(
  			"is_active" 	=> 0
  		);
  		$update_returns = $db_global->updateRecord($update_return, $post["return_reference"], "return_id", "tb_return");
  		unset($update_return);
  		
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
  			$db->insert("tb_return_vendor_item_in", $add_data);
  			
  			$rows=$db_global->inventoryLocation($post["LocationId"], $post["item_id_".$i]);	
  			if($rows){
  				$updatedata=array(
  						'qty' 				=> $rows["qty"]+$post["qty_return_".$i],
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
  				//add return history
  				$data_history = array
  				(		'transaction_type'  => 5,
  						'pro_id'     		=> $post["item_id_".$i],
  						'date'				=> new Zend_Date(),
  						'location_id' 		=> $post["LocationId"],
  						'Remark'			=> $order_no,
  						'qty_edit'        	=> $post["qty_return_".$i],
  						'qty_before'        => $rows["qty"],
  						'qty_after'        	=> $rows["qty"]+$post["qty_return_".$i],
  						'user_mod'			=> $GetUserId
  				);
  				$db->insert("tb_move_history", $data_history);
  				unset($data_history);
  				
  				$history = array(
  					"return_id"		=> $return_id,
  					"return_no"		=> $order_no,
  					"pro_id"		=> $post["item_id_".$i],
  					"location_id"	=> $post["LocationId"],
  					"return_type"	=>2,
  					"vendor_id"		=>1,
  					"return_date"	=>$post["return_date"],
  					"qty_return"	=> $post["qty_return_".$i],
  					"price"			=> $post["price_".$i],
  					"total_amount"	=> $post["sub_total_".$i],
  					"user_mod"		=> $GetUserId,
  					"remark"		=> $post["return_remark"]
  				);
  				$db->insert("tb_return_history", $history);
  				unset($history);
  
  			}
  			else{
  				$row_location = $db_global->productLocation($post["LocationId"],$post["item_id_".$i]);
  
  				$insertdata=array(
  						'pro_id'     => $post["item_id_".$i],
  						'LocationId' => $post["LocationId"],
  						'qty'        => $post["qty_return_".$i]
  				);
  				//update stock product location
  				$db->insert("tb_prolocation", $insertdata);
  				unset($insertdata);
  				//add return history
  				$data_history = array
  				(		'transaction_type'  => 5,
  						'pro_id'     		=> $post["item_id_".$i],
  						'date'				=> new Zend_Date(),
  						'location_id' 		=> $post["LocationId"],
  						'Remark'			=> $order_no,
  						'qty_edit'        	=> $post["qty_return_".$i],
  						'qty_before'        => 0,
  						'qty_after'        	=> $post["qty_return_".$i],
  						'user_mod'			=> $GetUserId
  				);
  				$db->insert("tb_move_history", $data_history);
  				unset($data_history);
  
  				$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
  				if($rows_stock){
  					$dataInventory= array(
  							'qty_onhand'    => $rows_stock["qty_onhand"]+ $post["qty_return_".$i],
  							'qty_available' => $rows_stock["qty_available"] + $post["qty_return_".$i],
  							'last_mod_date'         => new Zend_date()
  					);
  					$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_prodcut");
  					unset($dataInventory);
  				}
  				else{
  					$addInventory= array(
  							'pro_id'            => $post["item_id_".$i],
  							'qty_onhand'    => $post["qty_return_".$i],
  							'qty_available' => $post["qty_return_".$i],
  							'Timestamp'         => new Zend_date()
  					);
  					$db->insert("tb_prodcut", $addInventory);
  					unset($addInventory);
  				}
  			}
  		}
  		$db->commit();
  	}catch(Exception $e){
  		$db->rollBack();
  		echo $e->getMessage();
  		exit();
  		
  	}
  
  }
   public function updateReturnItemIn($post){
   	try {
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	$db_global = new Application_Model_DbTable_DbGlobal();
   	$session_user=new Zend_Session_Namespace('auth');
   	$userName=$session_user->user_name;
   	$GetUserId= $session_user->user_id;
   	 
   	$idrecord=$post['v_name'];
   	 
   	// 			$datainfo=array(
   	// 					"contact_name" => $post['contact'],
   	// 					"phone"        => $post['txt_phone'],
   	// 					"add_name"     => $post["vendor_address"]
   	// 			);
   	// 			//updage vendor info
   	// 			$db_global->updateRecord($datainfo,$idrecord,"vendor_id","tb_vendor");
   	// 			unset($datainfo);
   	$return_id = $post["id"];
   	$old_location = $post["old_location"];
   		
   	 
   	$sql_item="SELECT
	   	(SELECT p.pro_id FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS pro_id
	   		
	   	,(SELECT p.qty_onorder FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_onorder
	   
	   	,(SELECT p.qty_onhand 	FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_onhand
	   
	   	,(SELECT p.qty_available 	FROM tb_product AS p WHERE p.pro_id = rvi.`pro_id` LIMIT 1) AS qty_available
	   		
	   	, SUM(rvi.`qty_return`) AS qty_return FROM
   
   	tb_return_vendor_item_in AS rvi WHERE rvi.return_id = $return_id GROUP BY rvi.pro_id";
   	 
   	$rows_return=$db_global->getGlobalDb($sql_item);
   //	print_r($rows_return);
   	
   	if($rows_return){
   		foreach ($rows_return as $row_return){
			$qty_stock = $row_return["qty_onhand"] - $row_return["qty_return"];
			
			$rows=$db_global->inventoryLocation($old_location, $row_return["pro_id"]);
			$qty_location = $rows["qty"] - $row_return["qty_return"];
			
			//print_r($rows);exit();
			if($qty_stock<0){
				Application_Form_FrmMessage::message("Your product stock is less than return");
				Application_Form_FrmMessage::redirectUrl("/purchase/return/return-in");
				//exit();
			}
			elseif ($qty_location<0){
				Application_Form_FrmMessage::message("You Items is less than item return");
				Application_Form_FrmMessage::redirectUrl("/purchase/return/return-in");
				//exit();
			}else{
	   			$qty_on_order = array(
	   					"qty_onhand"	=> $row_return["qty_onhand"] - $row_return["qty_return"],
	   					"qty_available"	=> $row_return["qty_available"] - $row_return["qty_return"],
	   					"last_mod_date"			=> new Zend_date()
	   			);
	   			//update total stock
	   			$db_global->updateRecord($qty_on_order,$row_return["pro_id"],"pro_id","tb_product");
	   			unset($qty_on_order);
	   				
	   			$rowitem_exist=$db_global->porductLocationExist($row_return["pro_id"], $old_location);
	   			if($rowitem_exist){
	   				$updatedata=array(
	   						'qty' 				=> $rowitem_exist["qty"]-$row_return["qty_return"],
	   						"last_usermod"		=> $GetUserId,
	   						"last_mod_date"		=> new Zend_Date()
	   				);
	   				//update stock product location
	   				$db_global->updateRecord($updatedata,$rowitem_exist["ProLocationID"],"ProLocationID","tb_prolocation");
	   				unset($updatedata);
	   
	   			}
   			}
   		}
   	}
   	
   	$data_update = array(
   	   		"vendor_id"			=> 	$post["v_name"],
   	   		"date_return_in"	=> 	$post["return_date"],
   	   		"remark"			=> 	$post["return_remark"],
   	   		"user_mod"			=> 	$GetUserId,
   	   		"timestamp"			=> 	new Zend_Date(),
   	   		"location_id"		=> 	$post["LocationId"],
   	   		"all_total"			=> 	$post["all_total"],
   	   	);
   	   	$db_global->updateRecord($data_update, $return_id, "returnin_id", "tb_return_vendor_in");
   	   	unset($data_update);
   	   	
   	$sql= "DELETE FROM tb_return_vendor_item_in WHERE return_id IN ($return_id)";
   	$db_global->deleteRecords($sql);
   
   	$delete_history = "DELETE FROM tb_return_history WHERE return_id IN($return_id)";
   	$db_global->deleteRecords($delete_history);
   	 
   	$ids=explode(',',$post['identity']);
   	//add order in tb_inventory must update code again 9/8/13
   	foreach ($ids as $i){
   		$add_data = array(
   				"return_id" 	=> $return_id,
   				"pro_id" 		=> $post["item_id_".$i],
   				//"location_id"	=> $post["LocationId_".$i],
   				"qty_return" 	=> $post["qty_return_".$i],
   				"price" 		=> $post["price_".$i],
   				"sub_total" 	=> $post["sub_total_".$i],
   				"return_remark" => $post["remark_".$i]
   		);
   		$db->insert("tb_return_vendor_item", $add_data);
   		
   		$add_data = array(
   				"return_id" 	=> $return_id,
   				"pro_id" 		=> $post["item_id_".$i],
   				"qty_return" 	=> $post["qty_return_".$i],
   				"price" 		=> $post["price_".$i],
   				"sub_total" 	=> $post["sub_total_".$i],
   				"return_remark" => $post["remark_".$i]
   		);
   		$db->insert("tb_return_vendor_item_in", $add_data);
   		
   		$rows=$db_global->inventoryLocation($post["LocationId"], $post["item_id_".$i]);
   		if($rows){
   			
   				$qty_on_return = array(
   						"qty_onhand"    		=> $rows["qty_onhand"] + $post["qty_return_".$i],
   						"qty_available"   		=> $rows["qty_available"] + $post["qty_return_".$i],
   						"last_mod_date"			=> new Zend_date()
   				);
   				//update total stock
   				$db_global->updateRecord($qty_on_return,$post["item_id_".$i],"pro_id","tb_product");
   				unset($qty_on_return);
   
   
   				$updatedata=array(
   						'qty' 				=> $rows["qty"]+$post["qty_return_".$i],
   						"last_usermod"		=> $GetUserId,
   						"last_mod_date"		=> new Zend_Date()
   				);
   				//update stock product location
   				$db_global->updateRecord($updatedata,$rows["ProLocationID"],"ProLocationID","tb_prolocation");
   				unset($updatedata);
   			unset($qty_on_return);
   			//add return history
   			$data_history = array
   			(		'transaction_type'  => 4,
   					'pro_id'     		=> $post["item_id_".$i],
   					'date'				=> new Zend_Date(),
   					'location_id' 		=> $post["LocationId_".$i],
   					'Remark'			=> $post['remark_'.$i],
   					'qty_edit'        	=> $post["qty_return_".$i],
   					'qty_before'        => $rows["qty"],
   					'qty_after'        	=> $rows["qty"]-$post["qty_return_".$i],
   					'user_mod'			=> $GetUserId
   			);
   			$db->insert("tb_move_history", $data_history);
   			unset($data_history);
   
   			$history = array(
   					"return_id"		=> $return_id,
   					"return_no"		=> $post["retun_order"],
   					"pro_id"		=> $post["item_id_".$i],
   					"location_id"	=> $post["LocationId"],
   					"return_type"	=>2,
   					"vendor_id"		=>1,
   					"return_date"	=>$post["return_date"],
   					"qty_return"	=> $post["qty_return_".$i],
   					"price"			=> $post["price_".$i],
   					"total_amount"	=> $post["sub_total_".$i],
   					"user_mod"		=> $GetUserId,
   					"remark"		=> $post["return_remark"]
   			);
   			$db->insert("tb_return_history", $history);
   			unset($history);
   
   		}
   		else{
//    			Application_Form_FrmMessage::message("Your product in stock is not exist");
//    			Application_Form_FrmMessage::redirectUrl("/purchase/return");
//    			exit();
   			$row_location = $db_global->productLocation($post["LocationId"],$post["item_id_".$i]);
   			if($row_location){
   				$updatedata=array(
   						'qty' 				=> $rows["qty"]+$post["qty_return_".$i],
   						"last_usermod"		=> $GetUserId,
   						"last_mod_date"		=> new Zend_Date()
   				);
   				//update stock product location
   				$db_global->updateRecord($updatedata,$row_location["ProLocationID"],"ProLocationID","tb_prolocation");
   				unset($updatedata);
   			}else{
	   			$insertdata=array(
	   				'pro_id'     => $post["item_id_".$i],
	   				'LocationId' => $post["LocationId_".$i],
	   				'qty'        => -$post["qty_return_".$i]
	   			);
	   		}
   			//update stock product location
   			$db->insert("tb_prolocation", $insertdata);
   			unset($insertdata);
   							//add return history
   			$data_history = array(		
   					'transaction_type'  => 4,
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
   			
   			$history = array(
   					"return_id"		=> $return_id,
   					"return_no"		=> $post["retun_order"],
   					"pro_id"		=> $post["item_id_".$i],
   					"location_id"	=> $post["LocationId"],
   					"return_type"	=>2,
   					"vendor_id"		=>1,
   					"return_date"	=>$post["return_date"],
   					"qty_return"	=> $post["qty_return_".$i],
   					"price"			=> $post["price_".$i],
   					"total_amount"	=> $post["sub_total_".$i],
   					"user_mod"		=> $GetUserId,
   					"remark"		=> $post["return_remark"]
   			);
   			$db->insert("tb_return_history", $history);
   			unset($history);
   
   			$rows_stock=$db_global->InventoryExist($post["item_id_".$i]);
   			if($rows_stock){
   				$dataInventory= array(
   					'qty_onhand'    => $rows_stock["qty_onhand"]- $post["qty_return_".$i],
   					'qty_available' => $rows_stock["qty_available"] - $post["qty_return_".$i],
   					'last_mod_date'         => new Zend_date()
   			);
   			$db_global->updateRecord($dataInventory,$rows_stock["pro_id"],"pro_id","tb_product");
   			unset($dataInventory);
   			}else{
   				$addInventory= array(
   					'pro_id'            => $post["item_id_".$i],
   					'qty_onhand'    	=> -$post["qty_return_".$i],
   					'qty_available' 	=> -$post["qty_return_".$i],
   					'last_mod_date'     => new Zend_date()
   				);
   			$db->insert("tb_product", $addInventory);
   			unset($addInventory);
   			}
   
   		}
   		 
   	}
   	$db->commit();
   }catch (Exception $e){
   		$db->rollBack();
	   	echo $e->getMessage();
	   
   }
   }
}