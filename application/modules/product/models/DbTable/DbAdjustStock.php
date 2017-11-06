<?php

class Product_Model_DbTable_DbAdjustStock extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product";
	public function setName($name)
	{
		$this->_name=$name;
	}
	function getAllDamagedStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$start_date = date("Y-m-d",strtotime($data["start_date"]));
		$end_date = date("Y-m-d",strtotime($data["end_date"]));
		$sql ="SELECT 
				   d.id,
				  (SELECT p.`item_code` FROM `tb_product` AS p WHERE p.id=d.`pro_id` LIMIT 1) AS item_code,
				  (SELECT p.`item_name` FROM `tb_product` AS p WHERE p.id=d.`pro_id` LIMIT 1) AS item_name,
				  d.`cur_qty`,
				  d.`qty_adjust`,
				  d.`defer_qty`,
				  (d.`qty_adjust`-d.`cur_qty`) AS defer_qty,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=d.`pro_id` LIMIT 1) LIMIT 1) AS measure,
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id=d.`location_id`)AS location,
				  d.`date`,
				  (SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=d.`user_id`) AS `user`
				FROM
				  `tb_product_adjust` AS d  WHERE d.`date` BETWEEN '".$start_date."' AND '".$end_date."'";
		$where = '';
		 		/*if($data["ad_search"]!=""){
		 			$s_where=array();
		 			$s_search = addslashes(trim($data['ad_search']));
		 			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
		 			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
		 			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
		 			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
		 			//$s_where[]= " cate LIKE '%{$s_search}%'";
		 			$where.=' AND ('.implode(' OR ', $s_where).')';
		 		}*/
		$location = $db_globle->getAccessPermission('m.`location_id`');
		//echo $location;
		//echo $sql;
		return $db->fetchAll($sql);
			
	}
	public function add($data){
		$db = $this->getAdapter();
		 
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			$date =new Zend_Date();
			//print_r($result);exit();
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr = array(
							'pro_id'		=>	$data["pro_id_".$i],
							'location_id'	=>	$data["from_loc"],
							'cur_qty'		=>	$data["current_qty_".$i],
							'qty_unit'		=>	$data["qty_unit_".$i],
							'qty_per_unit'	=>	$data["qty_per_unit_".$i],
							'qty_measure'	=>	$data["qty_measure_".$i],
							'qty_adjust'	=>	$data["qty_".$i],
							'defer_qty'	    =>	$data["remain_qty_".$i],
							'date'			=>	date('Y-m-d'),
							'remark'		=>	$data["remark_".$i],
							'user_id'		=>	$result["user_id"],
					);
					$this->_name="tb_product_adjust";
					$this->insert($arr);
	
					$rs = $this->getProductQtyById($data["pro_id_".$i],$data["from_loc"]);
	
					if(!empty($rs)){
						$arr_p = array(
								'qty'			=>	$data["qty_".$i],
								//'damaged_qty'	=>	$rs["damaged_qty"]+$data["qty_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["from_loc"]);
						$this->update($arr_p, $where);
					}else{
						$arr_p = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$result["branch_id"],
								'qty'				=>	$data["qty_".$i],
								'damaged_qty'		=>	0,
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	date('Y-m-d'),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_p);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id` limit 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id` limit 1) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`model_id` and type=2 limit 1) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`color_id` and type=4 limit 1) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.key_code=p.`size_id` and type=3 limit 1) AS size
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE p.`id` = pl.`pro_id` AND p.status=1 ";
		//$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchRow($sql.$location);
	}
	
	function getProductQtyById($id,$location){
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`qty_perunit` ,
				  p.`item_code`,
				  p.`unit_label`,
				  (SELECT m.`name` FROM `tb_measure` AS m WHERE m.id=p.`measure_id` LIMIT 1) AS measure,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`,
				  pl.damaged_qty
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id AND pl.`location_id` = $location ";
		
		return $db->fetchRow($sql);
	}
	
	//for get current qty time /26-8-13
	public function getCurrentItem($post){
		$db=$this->getAdapter();
		$sql = "SELECT qty FROM tb_prolocation WHERE pro_id =" .$post['item_id'] ." AND LocationId = ".$post['location_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return($row);
	}
	//for transfer qty in location 26-8-13
	public function addAdjustStock($post)
	{
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$session_user = new Zend_Session_Namespace('auth');
			$userName  = $session_user->user_name;
			$GetUserId = $session_user->user_id;
		    $identity  = explode(',',$post['identity']);
		    $db_global= new Application_Model_DbTable_DbGlobal();
		    $db_global= new Application_Model_DbTable_DbGlobal();
		    foreach($identity as $i){
		    	$rows=$db_global -> porductLocationExist($post['item_id_'.$i], $post['location_id_'.$i]);//to check product location
		    	if($rows){
			    		$data_history = array
			    		(		
		    				'transaction_type'  => 1,
		    				'pro_id'     		=> $post['item_id_'.$i],
		    				'date'				=> new Zend_Date(),
		    				'location_id' 		=> $post['location_id_'.$i],
			    			'Remark'			=> $post['remark_'.$i],
			    			'qty_before'        => $rows['qty'],
			    			'qty_edit'        	=> $post['differ_'.$i],
		    				'qty_after'        	=> $post['qty_after_'.$i],
		    				'user_mod'			=> $GetUserId
			    		);
				    	$db->insert("tb_move_history", $data_history);
				    	unset($data_history);
			    		//update poduct location 
					   $data=array(
							'qty' 			=> $rows['qty']+$post['differ_'.$i],
					   		'qty_avaliable'	=>$rows["qty_avaliable"]+$post["differ_".$i],
							'last_usermod' 	=> $GetUserId,
							'last_mod_date'	=> new Zend_Date()//edited + $post['differ_'.$i]
					    );
		 				$itemid= $db_global->updateRecord($data, $rows['ProLocationID'], "ProLocationID","tb_prolocation");
		 				//update qty in stock inventory
		 				$row_exist = $db_global->InventoryExist($post['item_id_'.$i]);
		 				if($row_exist){
		 						$datatotal= array(
		 								'qty_onhand' 		=> $row_exist['qty_onhand']+ $post['differ_'.$i],
		 								'qty_available'		=> $row_exist['qty_available']+$post['differ_'.$i],
		 								'last_mod_date'		=> new Zend_Date()
		 						);
		 						$db_global->updateRecord($datatotal, $post['item_id_'.$i] ,"pro_id", "tb_product");
		 						unset($datatotal);
	
	 				}else{
	 					$dataInventory = array(
	 							'pro_id'            => $post['item_id_'.$i],
	 							'qty_onhand'    	=> $post['qty_after_'.$i],
	 							'qty_available' 	=> $post['qty_after_'.$i],
	 							'last_mod_date'		=> new Zend_Date()
	 					);
	 					$db->insert("tb_product", $dataInventory);
	 					unset($dataInventory);					
	 				}
		    	}
		    	else{//add add qty into pro location 26-8-13
		    		
			    		$add_pro_location = array(
			    				'pro_id'        => $post['item_id_'.$i],
			    				'LocationId'    => $post['location_id_'.$i],
			    			    'qty'           => $post['qty_after_'.$i],
			    				'qty_avaliable'	=> $post["qty_after_".$i],
			    			    'last_usermod'  => $GetUserId,
			    				'last_mod_date' => new Zend_Date()
			    			);
				    	 $db->insert("tb_prolocation", $add_pro_location);
				    	 unset($add_pro_location);
				    	 //add move history 26-8-13
				    	 $data_history = array(
				    	 		'transaction_type'  => 1,
				    	 		'pro_id'     		=> $post['item_id_'.$i],
				    	 		'date'				=> new Zend_Date(),
				    	 		'location_id' 		=> $post['location_id_'.$i],
				    	 		'Remark'			=> $post['remark_'.$i],
				    	 		'qty_edit'        	=> $post['differ_'.$i],
				    	 		'qty_after'        	=> $post['qty_after_'.$i],
				    	 		'user_mod'			=> $GetUserId
				    	 );
				    	 $db->insert("tb_move_history", $data_history);
			    	     unset($data_history);
				    	 $row_exist = $db_global->InventoryExist($post['item_id_'.$i]);
				    	 if($row_exist){
				    	 	$datatotal= array(
				    	 			'qty_onhand' 		=> $row_exist['qty_onhand']+ $post['differ_'.$i],
				    	 			'qty_available'		=> $row_exist['qty_available']+$post['differ_'.$i],
				    	 			'last_mod_date'		=> new Zend_Date()
				    	 	);
				    	 	$db_global->updateRecord($datatotal, $post['item_id_'.$i] ,"pro_id", "tb_product");
				    	 	unset($datatotal);	 	
				    	 }
				    	 else{//add to pro total inventory
				    	 	
				    	 	$dataInventory = array(
				    	 			'pro_id'            => $post['item_id_'.$i],
				    	 			'qty_onhand'    	=> $post['qty_after_'.$i],
				    	 			'qty_available' 	=> $post['qty_after_'.$i],
				    	 			'last_mod_date'		=> new Zend_Date()
				    	 	);
				    	 	$db->insert("tb_product", $dataInventory);
				    	 	unset($dataInventory); 	
				    	 }
		    	}
		    	//add data to adjust stock 26-8-13//not yet use cos have not in move history 
	// 	    	$data_adjust= array(
	// 	    			'LocationId'     => $post['location_id_'.$i],
	// 	    			'QuantityBefore' => $post['qty_before_'.$i],
	// 	    			'QuantityAfter'  => $post['qty_after_'.$i],
	// 	    			'Difference'     => $post['differ_'.$i],
	// 	    			'Timestamp'      => new Zend_Date(),
	// 	    			'last_usermod'   => $GetUserId,
	// 	    			'ProdId'         => $post['item_id_'.$i],
	// 	    			'remark'         => $post['remark']
	// 	    	);
	// 	    	$adjust=$db->insert("tb_stockadjust", $data_adjust);	
		    }
		    $db->commit();	
		}catch(Exception $e){
			$db->rollBack();
	    	
	    }
	}
	///new way to use
	public function TransferStockTransaction($post){
		$db=$this->getAdapter();
		$session_user = new Zend_Session_Namespace('auth');
		$userName  = $session_user->user_name;
		$GetUserId = $session_user->user_id;
	
		$db_global = new Application_Model_DbTable_DbGlobal();
		
		if($post['from_location']!== $post['to_location']){
			//try{
			
					if($post['invoce_num']!=""){
						
						$tr_no=$post['invoce_num'];
					}
					else{
						$date= new Zend_Date();
						$tr_no="TR".$date->get('hh-mm-ss');
					}
				   $data_transfer=array(
										'invoice_num'	=> $tr_no,
										'transfer_date' => $post['transfer_date'],
										'from_location'	=> $post['from_location'],
										'to_location'	=> $post['to_location'],
										'user_id' 		=> $GetUserId,
										'mod_date'		=> new Zend_Date(),
										'remark'	    => $post['remark_transfer']
								       );
					$transfer_id = $db_global->addRecord($data_transfer, "tb_stocktransfer");
				    unset($data_transfer);
				    $identity  = explode(',',$post['identity']);
					foreach($identity as $i){
					 				$data_item=array(
										'transfer_id'	 => $transfer_id,
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
								'qty' 			=>	$rows['qty']- $post['qty_id_'.$i],
								'qty_avaliable'	=>  $rows["qty_avaliable"]- $post['qty_id_'.$i],
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
							
						unset($data_qty_location);unset($rows);unset($data_history);
						//update product location to
						$rows_gets_qty=$db_global -> porductLocationExist($post['item_id_'.$i], $post['to_location']);
		
						if($rows_gets_qty){
							$data_qty_location=array(
									'qty' 			=>	$rows_gets_qty['qty']			+ $post['qty_id_'.$i],
									'qty_avaliable'	=>  $rows_gets_qty["qty_avaliable"]	+ $post['qty_id_'.$i],
							);
							$itemid=$db_global->updateRecord($data_qty_location, $rows_gets_qty['ProLocationID'], "ProLocationID","tb_prolocation");
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
							unset($rows_gets_qty);unset($data_history);
						}//if recieve deosn't exist in product location
						else{
							$add_pro_location = array(
									'pro_id'        => $post['item_id_'.$i],
									'LocationId'    => $post['to_location'],
									'qty'           => $post['qty_id_'.$i],
									'qty_avaliable'	=> $post['qty_id_'.$i],
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
							unset($add_pro_location); unset($data_history);
						}
					}
					else{//if from doesn't exist
						//add qty in location if from doesn't exist
						$add_pro_location = array(
								'pro_id'        => $post['item_id_'.$i],
								'LocationId'    => $post['from_location'],
								'qty'           => -$post['qty_id_'.$i],
								'qty_avaliable'	=>  - $post['qty_id_'.$i],
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
							unset($rows_gets_qty);unset($data_qty_location);
						}//if recieve deosn't exist in product location
						else{ //if doesn't exist from and to
							$add_pro_location = array(
									'pro_id'        => $post['item_id_'.$i],
									'LocationId'    => $post['to_location'],
									'qty'           => $post['qty_id_'.$i],
									'qty_avaliable'	=> $post['qty_id_'.$i],
									'last_usermod'  => $GetUserId,
									'last_mod_date' => new Zend_Date()
							);
							$db->insert("tb_prolocation", $add_pro_location);
							unset($add_pro_location);
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
							unset($data_history);
						}
					}
				}//forforeach
				//$db->commit();
		   /*}//try
		   catch (Exception $e) {
		   	$db->rollBack();
		   	$this->view->msg = $e->getMessage();
		   }*/
		}//for if
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////
	///old way to use
	public function addTransferStock($post){
		$db=$this->getAdapter();
		$session_user = new Zend_Session_Namespace('auth');
		$userName  = $session_user->user_name;
		$GetUserId = $session_user->user_id;
		
		$db_global = new Application_Model_DbTable_DbGlobal();
		$identity  = explode(',',$post['identity']);
		foreach($identity as $i){
			if($post['from_location_id_'.$i]!== $post['to_location_id_'.$i]){
				
// 				$data_transfer=array(
// 						'pro_id'		=> $post['item_id_'.$i],
// 						'FromLocationId'=> $post['from_location_id_'.$i],
// 						'ToLocationId'	=> $post['to_location_id_'.$i],
// 						'qty'			=> $post['qty_id_'.$i],
// 						'user_id' 	=> $GetUserId,
// 						'date_transfer'	=> new Zend_Date(),
// 						'remark'	    => $post['remark']
				
// 				);
// 				$db->insert("tb_stocktransfer", $data_transfer);
// 				unset($data_transfer);				
				
				$rows = $db_global ->porductLocationExist($post['item_id_'.$i], $post['from_location_id_'.$i]);
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
										'location_id' 		=> $post['from_location_id_'.$i],
								//		'Remark'			=> $post['remark'],
										'qty_edit'        	=> $post['qty_id_'.$i],
										'qty_before'        => $rows['qty'],
										'qty_after'        	=> $rows['qty']- $post['qty_id_'.$i],
										'user_mod'			=> $GetUserId
									);
					$db->insert("tb_move_history", $data_history);
					
					unset($data_qty_location);unset($rows);
					//update product location to
					$rows_gets_qty=$db_global -> porductLocationExist($post['item_id_'.$i], $post['to_location_id_'.$i]);
						
						if($rows_gets_qty){
							$data_qty_location=array(
									'qty' =>$rows_gets_qty['qty']+ $post['qty_id_'.$i]
							);
							$itemid=$db_global->updateRecord($data_qty_location, $rows_gets_qty['ProLocationID'], "ProLocationID","tb_prolocation");	
							//add move history
								$data_history = array
								(
										'transaction_type'  => 2,
										'pro_id'     		=> $post['item_id_'.$i],
										'date'				=> new Zend_Date(),
										'location_id' 		=> $post['to_location_id_'.$i],
									//	'Remark'			=> $post['remark'],//can't add remark cos short table in form
										'qty_edit'        	=> $post['qty_id_'.$i],
										'qty_before'        => $rows_gets_qty['qty'],
										'qty_after'        	=> $rows_gets_qty['qty']+ $post['qty_id_'.$i],
										'user_mod'			=> $GetUserId
								);
								$db->insert("tb_move_history", $data_history);
						}//if recieve deosn't exist in product location 
						else{
							$add_pro_location = array(
									'pro_id'        => $post['item_id_'.$i],
									'LocationId'    => $post['to_location_id_'.$i],
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
									'location_id' 		=> $post['to_location_id_'.$i],
									//'Remark'			=> $post['remark'],
									'qty_edit'        	=> $post['qty_id_'.$i],
									'qty_before'        => 0,
									'qty_after'        	=> $post['qty_id_'.$i],
									'user_mod'			=> $GetUserId
							);
							$db->insert("tb_move_history", $data_history);
						}					
				}
				else{//if from doesn't exist
					//add qty in location if from doesn't exist
					$add_pro_location = array(
							'pro_id'        => $post['item_id_'.$i],
							'LocationId'    => $post['from_location_id_'.$i],
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
							'location_id' 		=> $post['from_location_id_'.$i],
						//	'Remark'			=> $post['remark_i'],
							'qty_edit'        	=> $post['qty_id_'.$i],
							'qty_after'        	=> -$post['qty_id_'.$i],
							'user_mod'			=> $GetUserId
					);
					$db->insert("tb_move_history", $data_history);
					unset($data_history);
					
					//for get stock 
						$rows_gets_qty=$db_global -> porductLocationExist($post['item_id_'.$i], $post['to_location_id_'.$i]);
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
									'location_id' 		=> $post['to_location_id_'.$i],
							//		'Remark'			=> $post['remark'],
									'qty_edit'        	=> $post['qty_id_'.$i],
									'qty_before'        => $rows_gets_qty['qty'],
									'qty_after'        	=> $rows_gets_qty['qty']+ $post['qty_id_'.$i],
									'user_mod'			=> $GetUserId
							);
							$db->insert("tb_move_history", $data_history);
							
						}//if recieve deosn't exist in product location
						else{ //if doesn't exist from and to
							$add_pro_location = array(
									'pro_id'        => $post['item_id_'.$i],
									'LocationId'    => $post['to_location_id_'.$i],
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
									'location_id' 		=> $post['to_location_id_'.$i],
							//		'Remark'			=> $post['remark'],
									'qty_edit'        	=> $post['qty_id_'.$i],
									'qty_after'        	=> $post['qty_id_'.$i],
									'user_mod'			=> $GetUserId
							);
							$db->insert("tb_move_history", $data_history);
						}
				}
			}
		}
	}
	
	public function adjustPricing($post){
		
		$identity=explode(',',$post['identity']);
		foreach($identity as $i)
		{
			$item_id = $post['item_id_'.$i];
			$array = array("price" => $post["new_price_".$i]);
			
			$where=$this->getAdapter()->quoteInto('pro_id=?',$post['item_id_'.$i]);
			$this->update($array,$where);
		}
	}
}