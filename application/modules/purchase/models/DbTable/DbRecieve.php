<?php

class Purchase_Model_DbTable_DbRecieve extends Zend_Db_Table_Abstract
{
	function getPURCode(){
		$db = $this->getAdapter();
		$sql="";
		//return $db-
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getAllPuCode(){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`order_number` FROM `tb_purchase_order` AS p";
		return $db->fetchAll($sql);
	}
	function getRecieveById($id){
		$db = $this->getAdapter();
		$sql="SELECT r.* FROM `tb_recieve_order` AS r WHERE r.`purchase_id`=$id";
		return $db->fetchRow($sql);
	}
	function getVendorByPuId($id){
		$db = $this->getAdapter();
		$sql="SELECT 
			  v.`v_name`,
			  v.`v_phone`,
			  v.`contact_name`,
			  v.`phone_person`,
			  v.`vendor_id`,
			  v.`email`,
			  v.`is_over_sea`,
			  v.`add_name`,
			  v.`vendor_id`,
			  p.`branch_id` ,
			  DATE_FORMAT(p.`date_order`,'%m/%d/%Y') AS date_order,
			  DATE_FORMAT(p.`date_in`,'%m/%d/%Y') AS date_in,
			  p.`payment_method`,
			  p.`currency_id`,
			  p.`discount_value`,
			  p.`discount_real`,
			  p.`all_total`,
			  p.`tax`,
			  p.`net_total`,
			  p.`paid`,
			  p.`balance`,
			  (SELECT pl.`name` FROM `tb_sublocation` AS pl WHERE pl.id=p.`branch_id`) AS location
			FROM
			  `tb_vendor` AS v,
			  `tb_purchase_order` AS p 
			WHERE p.`vendor_id` = v.`vendor_id` 
			  AND p.`id` = $id";
		return $db->fetchRow($sql);
	}
	function getProductReceiveById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					  r.`order_id`,
					  r.`date_in`,
					  r.`recieve_number`,
					  r.`date_order`,
					  ri.`qty_receive`,
					  ri.`qty_order` ,
					  (SELECT pu.`order_number` FROM `tb_purchase_order` AS pu WHERE pu.id=r.`purchase_id`) AS po_no,
					(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=r.`LocationId`) AS branch,
					  (SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.`vendor_id`) AS vendor_name,
  (SELECT v.`contact_name` FROM `tb_vendor` AS v WHERE v.vendor_id=r.`vendor_id`) AS contact_name,
					   (SELECT v.v_phone FROM `tb_vendor` AS v WHERE v.vendor_id=r.`vendor_id`) AS v_phone,
					   (SELECT v.add_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.`vendor_id`) AS add_name,
					   (SELECT v.email FROM `tb_vendor` AS v WHERE v.vendor_id=r.`vendor_id`) AS email,
					  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=ri.`pro_id`) AS item_name,
					  (SELECT p.item_code FROM `tb_product` AS p WHERE p.id=ri.`pro_id`) AS item_code,
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id=(SELECT p.measure_id FROM `tb_product` AS p WHERE p.id=ri.`pro_id` LIMIT 1) LIMIT 1) AS measure
					FROM
					  `tb_recieve_order` AS r,
					  `tb_recieve_order_item` AS ri 
					WHERE r.`order_id` = ri.`recieve_id`
					AND r.`purchase_id`=$id";
		
		return $db->fetchAll($sql);
	}
	function getItemByPuId($id){
		$db = $this->getAdapter();
		$sql="SELECT p.`pro_id`,p.`qty_order`,p.qty_unit,p.qty_detail,pd.`item_name`,pd.qty_perunit,p.`price`,p.`sub_total`,p.`disc_value` 
				FROM `tb_purchase_order_item` AS p ,`tb_product` AS  pd 
			  WHERE p.`pro_id`=pd.`id` AND p.`purchase_id`=$id";
		return $db->fetchAll($sql);
	}
	
	function getRecieveCode(){
		$db = $this->getAdapter();
		$user = $this->GetuserInfo();
		$location = $user['branch_id'];
		
		$sql_pre = "SELECT pl.`prefix` FROM `tb_sublocation` AS pl WHERE pl.`id`=$location";
		$prefix = $db->fetchOne($sql_pre);
		
		$sql="SELECT r.`order_id` FROM `tb_recieve_order` AS r WHERE r.`LocationId`=$location ORDER BY r.`order_id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = $prefix."RP";
		for($i=$num_lentgh;$i<4;$i++){
			$pre.="0";
		}
		return $pre.$num;
	}
	
	function  add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$user = $this->GetuserInfo();
		$GetUserId = $user['user_id'];
		//print_r($data);
		
		try{
			$identity = $data["identity"];
			if($identity!=""){
				
				$orderdata = array(
					'purchase_id'		=>	$data["pu_code"],
					"vendor_id"      	=> 	$data['vendor'],
					"LocationId"     	=> 	$data["branch"],
					"recieve_number" 	=> 	$data["recieve_no"],
					"date_order"     	=> 	$data['date_order'],
					"date_in"     		=> 	$data['date_in'],
					"purchase_status"   => 	1,
					"payment_method" 	=> 	$data['payment_name'],
					"currency_id"    	=> 	$data['currency'],
					"remark"         	=> 	$data['remark'],
					"all_total"      	=> 	$data['totalAmoun'],
					//"all_total_after"   => 	$data['totalAmoun_after'],
					"tax"				=>	$data["total_tax"],
					"discount_value" 	=> 	$data['dis_value'],
					"discount_real"  	=> 	$data['global_disc'],
					"net_total"      	=> 	$data['all_total'],
					//"net_total_after"   => 	$data['all_total_after'],
					"paid"           	=> 	$data['paid'],
					"balance"        	=> 	$data['remain'],
					//"balance_after"		=>	$data["remain_after"],
					"user_mod"       	=> 	$GetUserId,
					//"date"      		=> 	$data["date_recieve"],
					"date"				=>	date("Y-m-d"),
				);
				
				$this->_name='tb_recieve_order';
				$recieved_order = $this->insert($orderdata);
				
				$ids=explode(',',$data['identity']);
				$locationid=$data['branch'];
				foreach ($ids as $i){
					//calcualte cost average
					$rsproduct = $this->getProductCostAndQty($data['item_id_'.$i]);
					$cost_avg = (($rsproduct['qty']*$rsproduct['price'])+$data['cost_price'.$i]) / ($rsproduct['qty']+$data['qty_recieve_'.$i]);
					$array=array(
							'price'=>$cost_avg
					);
					$this->_name="tb_product";
					$where = " id= ".$rsproduct['id'];
					$this->update($array, $where);
					
					$recieved_item = array(
							'recieve_id'	  	=> 	$recieved_order,
							'pro_id'	  		=> 	$data['item_id_'.$i],
							'qty_order'	  		=> 	$data['qty_order_'.$i],
							'qty_receive'	  	=> 	$data['qty_recieve_'.$i],
							'qty_detail'  		=> 	$data['qty_per_unit_'.$i],
							'qty_unit'  		=> 	$data['qty_unit_'.$i],
							'price'		  		=> 	$data['price_'.$i],
							'disc_value'	  	=> $data['dis_value'.$i],
							'sub_total'	  		=> $data['total_'.$i],
							'sub_total_after'	=> $data['total_after_'.$i],
							'remark'			=>	$data["remark_".$i],
							'cost_avg'=>	$cost_avg,
					);
					$this->_name="tb_recieve_order_item";
					$this->insert($recieved_item);
					
					$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						$datatostock   = array(
								'qty'   			=> 		$rows["qty"]+$data['qty_recieve_'.$i],
								'last_mod_date'		=>		date("Y-m-d"),
								'last_mod_userid'	=>		$GetUserId
						);
						$this->_name="tb_prolocation";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					}
				}
				
			}else{
			}
			$arr = array(
				
				'purchase_status'	=>	5,
			);
			$where = "id=".$data["id"];
			$this->_name = "tb_purchase_order";
			$db->getProfiler()->setEnabled(true);
			$this->update($arr, $where);
			
			$db->commit();
			return $recieved_order;
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			
			Application_Model_DbTable_DbUserLog::writeMessageError($err);	
			echo $err;exit();			
		}
	}
	
	function getProductForCost($pro_id,$location){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`price`,pl.`qty` FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND pl.`location_id`=$location AND p.id=$pro_id";
		return $db->fetchRow($sql);
	}
	function getProductCostAndQty($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`price`,sum(pl.`qty`) as qty FROM `tb_product` AS p ,`tb_prolocation` AS pl WHERE p.id=pl.`pro_id` AND p.id=$pro_id ";
		return $db->fetchRow($sql);
	}
	public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"			=>	$pro_id,
    				"location_id"		=>	$location_id,
    				"qty"				=>	0,
    				"qty_warning"		=>	0,
    				"last_mod_userid"	=>	$GetUserId,
    				"user_id"			=>	$GetUserId,
    				"last_mod_date"		=>	date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
	function getAllReceivedOrder($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
				  ro.order_id AS id,
				  ro.recieve_number,
				  ro.`order_number`,
				  ro.date_order,
				  date_in,
				  (SELECT pl.name FROM `tb_sublocation` AS pl WHERE pl.id=ro.`LocationId`) AS branch,
				  (SELECT v.v_name FROM tb_vendor AS v WHERE v.vendor_id = ro.vendor_id) AS vendor_name,
				  ro.all_total,
				  (SELECT  u.username FROM tb_acl_user AS u WHERE u.user_id = ro.user_mod) AS user_name 
				FROM
				  tb_recieve_order AS ro 
				WHERE `status` = 1 ";
		$order=" ORDER BY ro.order_id DESC  ";
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$user = $this->GetuserInfoAction();
// 		$str_condition = " AND p.LocationId" ;
// 		$vendor_sql .= $db->getAccessPermission($user["level"], $str_condition, $user["location_id"]);
// 		if($post['order'] !=''){
// 			$vendor_sql .= " AND ro.recieve_no LIKE '%".$post['order']."%'";
// 		}
// 		if($post['vendor_name'] !='' AND $post['vendor_name'] !=0){
// 			$vendor_sql .= " AND ro.user_recieve =".$post['vendor_name'];
// 		}
// 		$start_date = $post['search_start_date'];
// 		$end_date = $post['search_end_date'];
// 		if($start_date != "" && $end_date != "" && strtotime($end_date) >= strtotime($start_date)) {
// 			$vendor_sql .= " AND ro.date_recieve BETWEEN '$start_date' AND '$end_date'";
// 		}
		return $db->fetchAll($sql.$order);
	}
	
	public function vendorPurchaseOrderPayment($data)
	{
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$db = $this->getAdapter();	
			$db->beginTransaction();
		
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$idrecord=$data['v_name'];
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
					"date_in"     	 => $data['date_in'],
					"status"         => 4,
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
						'status'	 => 4,
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
				//print_r($data_item);
				unset($data_item[$i]);
				
				
				
				
				//check stock product location
				
				
				$rows=$db_global -> productLocationInventoryCheck($itemId, $locationid);
				if($rows)
				{
					
					$qtyold       = $rows['qty_onorder'];
					$getrecord_id = $rows["ProLocationID"];
					//$qty_onhand   = $rows["QuantityOnHand"]+$qtyrecord;
					$itemOnHand   = array(
							'qty_onorder'   => $qtyold+$qtyrecord,
							//'qty_available'=> $rows["qty_available"]+$qtyrecord
					);
					
					
					//update total stock
					$itemid=$db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
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
						$itemOnHand   = array(
								'qty_onorder'    => $rows["qty_onorder"]+$data['qty'.$i],
								//"qty_available" => $rows["qty_available"]+$data['qty'.$i],
								'pro_id'		    => $itemId
						);
						//update total stock
						 $db_global->updateRecord($itemOnHand,$itemId,"pro_id","tb_product");
					}
					else
					{
						$dataInventory= array(
								'ProdId'            => $itemId,
								'qty_onorder'    => $data['qty'.$i],
								//'qty_available' => $data['qty'.$i],
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