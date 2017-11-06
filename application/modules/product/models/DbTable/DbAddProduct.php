<?php

class Product_Model_DbTable_DbAddProduct extends Zend_Db_Table_Abstract
{
	protected $_name = 'tb_product';
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function addProduct($post)
	{
		$db=$this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global= new Application_Model_DbTable_DbGlobal();
			$photoname = str_replace(" ", "_", $post['txt_name']) . '.jpg';
			$upload = new Zend_File_Transfer();
			$upload->addFilter('Rename',
					array('target' => PUBLIC_PATH . '/images/fi-upload/'. $photoname, 'overwrite' => true) ,'photo');
			$receive = $upload->receive();
			if($receive)
			{
				$post['photo'] = $photoname;
			}else{
				$post['photo']="";
			}
			
			if(empty($post['unit_sale_price'])){
				$post['unit_sale_price']=0;
			}
			unset($post['MAX_FILE_SIZE']);
			$data=array(
					'item_name'			=> 		$post['txt_name'],
					'item_code'			=> 		$post['txt_code'],
					'item_size'			=> 		$post['product_size'],
					'photo'				=> 		$post['photo'],
					'brand_id'			=> 		$post['branch_id'],
					'cate_id'			=> 		$post['category'],
					'stock_type'		=> 		$post['stock_type'],
					'measure_id'		=> 		$post['measure_unit'],
					'qty_perunit'		=> 		$post['qty_perunit'],
					'label'				=> 		$post['label_perunit'],
					'is_avaliable'		=> 		$post['status'],
					'unit_sale_price'	=> 		$post['unit_sale_price'],
					'price_per_qty'		=> 		$post['unit_sale_price']/$post['qty_perunit'],
					'purchase_tax'		=>		$post["pur_tax"],
					'sale_tax'			=>		$post["sale_tax"],
					'remark'			=> 		$post['remark'],
					'last_usermod'		=> 		$this->getUserId(),
					'last_mod_date'		=> 		new Zend_Date(),//test
			);
			$item_id = $this->insert($data);
			unset($data);
			$qtyonhand=0;
				if(!empty($post['identity'])){
					$identitys = explode(',',$post['identity']);
					foreach($identitys as $i)
					{
						//for get all value in stock inventory
						$qtyonhand=$qtyonhand + $post['qty'.$i];
						if(empty($post['unit_price'.$i])){
							$post['unit_price'.$i]=0;
						}
						//check if product location exist
						$rows_exist = $db_global->porductLocationExist($item_id, $post['location_id_'.$i]);
						
						if($rows_exist){
							$datatotal= array(
									'qty'      => $rows_exist['qty']+ $post['qty'.$i],
									'qty_warn' => $post['qty_warnning'.$i]
									);
							$db_global->updateRecord($datatotal, $rows_exist['ProLocationID'], "ProLocationID","tb_prolocation");
							//add history
							$data_history = array(
									'transaction_type'  => 1,
									'pro_id'     		=> $item_id,
									'date'				=> new Zend_Date(),
									'location_id' 		=> $post['location_id_'.$i],
									'qty_edit'        	=> $post['qty'.$i]+$rows_exist['qty'],
									'qty_before'        => $rows_exist['qty'],
									'qty_after'        	=> $post['qty'.$i],
									'user_mod'			=>  $this->getUserId(),
							);
							$db->insert("tb_move_history", $data_history);
							unset($rows_exist); unset($data_history);
						}
						else{
							
							$dataproduct=array(
									'pro_id'     	 => $item_id,
									'LocationId' 	 => $post['location_id_'.$i],
									'qty'        	 => $post['qty'.$i],
									'qty_warn'       => $post['qty_warnning'.$i],
									'unit_sale_price'=> $post['unit_price'.$i],
									'price_per_qty'  => $post['unit_price'.$i]/$post['qty_perunit'],
									'last_usermod'   =>  $this->getUserId(),
									'last_mod_date'	 => new Zend_Date()
									
							);
							//add qty to product location
							$db->insert("tb_prolocation", $dataproduct);	
							//add history
							$data_history = array
							(		'transaction_type'  => 1,
									'pro_id'     		=> $item_id,
									'date'				=> new Zend_Date(),
									'location_id' 		=> $post['location_id_'.$i],
									'qty_before'        => 0,
									'qty_edit'        	=> $post['qty'.$i],
									'qty_after'        	=> $post['qty'.$i],
									'user_mod'			=> $this->getUserId(),
							);
							$db->insert("tb_move_history", $data_history);
							unset($dataproduct);unset($data_history);
						}
					}
					//add product on stock
					$_qtydata= array(
							'qty_onhand'   => $qtyonhand,
							'qty_onsold'   => 0,
							'qty_onorder'  =>0,
							'qty_available'=> $qtyonhand,
					);
					$where = $db->quoteInto("pro_id=?", $item_id);
					$this->update($_qtydata, $where);
			   }	
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	//for update produt 
	public function updateProduct($post){
			try{//use try on 21-11-13
				$db=$this->getAdapter();
				$db->beginTransaction();
				$db_global= new Application_Model_DbTable_DbGlobal();
				$session_user=new Zend_Session_Namespace('auth');
				$userName=$session_user->user_name;
				$GetUserId= $session_user->user_id;
				$GetUserLever = $session_user->level;
				$user_location = $session_user->location_id;
				
				$photoname = str_replace(" ", "_", $post['txt_name']) .'.jpg';
				$upload = new Zend_File_Transfer();
				$upload->addFilter('Rename',
						array('target' => PUBLIC_PATH .'/images/fi-upload/'. $photoname, 'overwrite' => true) ,'photo');
				$receive = $upload->receive();
				$files = $upload->getFileInfo();
				//print_r($files);exit();
				if($receive)
				{
					$post['photo'] = $photoname;
				}
				unset($post['MAX_FILE_SIZE']);
				$data=array(
						'item_name'			=> 		$post['txt_name'],
						'item_code'			=> 		$post['txt_code'],
						'item_size'			=> 		$post['product_size'],
						'photo'				=> 		$post['photo'],
						'brand_id'			=> 		$post['branch_id'],
						'cate_id'			=> 		$post['category'],
						'stock_type'		=> 		$post['stock_type'],
						'measure_id'		=> 		$post['measure_unit'],
						'qty_perunit'		=> 		$post['qty_perunit'],
						'label'				=> 		$post['label_perunit'],
						'is_avaliable'		=> 		$post['status'],
						'unit_sale_price'	=> 		$post['unit_sale_price'],
						'price_per_qty'		=> 		$post['unit_sale_price']/$post['qty_perunit'],
						'purchase_tax'		=>		$post["pur_tax"],
						'sale_tax'			=>		$post["sale_tax"],
						'remark'			=> 		$post['remark'],
						'last_usermod'		=> 		$GetUserId,
						'last_mod_date'		=> 		new Zend_Date(),//test
				);
				
				$id = $post['id'];			
				$itemid=$db_global->updateRecord($data,$id,"pro_id","tb_product");
				//for update product by user level
				if($GetUserLever==1 OR $GetUserLever==2){
					$_model = new Application_Model_DbTable_DbGlobal();
					$identitys = explode(',',$post['identity']);
					$qtyonhand = 0;
					foreach($identitys as $i){
							$qtyonhand=$qtyonhand + $post['qty_'.$i];
							if(empty($post['unit_price'.$i])){
								$post['unit_price'.$i]=0;
							}
							$_rs = $_model->QtyProLocation($id, $post['location_id_'.$i]);
							if($_rs!==""){
								if($_rs['qty']!==$post['qty_'.$i]){
									$_arr= array(
											'qty'  => $post['qty_'.$i],
											'qty_warn'       => $post['qty_warnning'.$i],
											'unit_sale_price'=> $post['unit_price'.$i],
											'price_per_qty'  => $post['unit_price'.$i]/$post['qty_perunit'],
											'last_usermod'  =>  $this->getUserId(),
											'last_mod_date'	=> new Zend_Date()
									);
									$db_global->updateRecord($_arr, $_rs['ProLocationID'], "ProLocationID","tb_prolocation");
									//code here
									$_arr_history = array(
											'transaction_type'  => 1,
											'pro_id'     		=> $id,
											'date'				=> new Zend_Date(),
											'location_id' 		=> $post['location_id_'.$i],
											'qty_edit'        	=> $_rs['qty']." -> ".$post['qty_'.$i],
											'qty_before'        => $_rs['qty'],
											'qty_after'        	=> $post['qty_'.$i],
											'user_mod'			=> $this->getUserId(),
									);
									$db->insert("tb_move_history", $_arr_history);
								}
							}
							else{
								$dataproduct=array(
										'pro_id'     	 => $id,
										'LocationId' 	 => $post['location_id_'.$i],
										'qty'        	 => $post['qty'.$i],
										'qty_warn'       => $post['qty_warnning'.$i],
										'unit_sale_price'=> $post['unit_price'.$i],
										'price_per_qty'  => $post['unit_price'.$i]/$post['qty_perunit'],
										'last_usermod'   =>  $this->getUserId(),
										'last_mod_date'	 => new Zend_Date()
										
								);
								//add qty to product location
								$db->insert("tb_prolocation", $dataproduct);	
								//add history
								$data_history = array
								(		'transaction_type'  => 1,
										'pro_id'     		=> $id,
										'date'				=> new Zend_Date(),
										'location_id' 		=> $post['location_id_'.$i],
										'qty_before'        => 0,
										'qty_edit'        	=>"0 -> ".$post['qty'.$i],
										'qty_after'        	=> $post['qty'.$i],
										'user_mod'			=> $this->getUserId(),
								);
								$db->insert("tb_move_history", $data_history);
								unset($dataproduct);unset($data_history);
							}
						}
													
						$_rs=$db_global->getQtyFromProductById($id);
						if(!empty($_rs)){
							$_qty_deffer=$qtyonhand-$_rs['qty_onhand'];
							$_qtydata= array(
									'qty_onhand'   => $qtyonhand,
									'qty_available'   => $_rs['qty_available']+$_qty_deffer,
							);
							$where = $db->quoteInto("pro_id=?", $id);
							$this->update($_qtydata, $where);
						}
			}else{
					$identitys = explode(',',$post['identity']);
					$qty_onhand = 0;
					foreach($identitys as $i){
						$qty=$post['qty_'.$i];//not yet
						if(empty($post['item_price'.$i])){
							$post['item_price'.$i]=0;
						}
						$rows_exist = $db_global->porductLocationExist($id,$post["location_id_".$i]);
						$_arr= array(
								'qty'            => $post['qty_'.$i],
								'qty_warn'       => $post['qty_warnning'.$i],
								'unit_sale_price'=> $post['unit_price'.$i],
								'price_per_qty'  => $post['unit_price'.$i]/$post['qty_perunit'],
								'last_usermod'   =>  $this->getUserId(),
								'last_mod_date'	 => new Zend_Date()
						);
					}
						if($rows_exist!==""){
							$db_global->updateRecord($_arr, $rows_exist["ProLocationID"], "ProLocationID","tb_prolocation");
							unset($arr);
							if($rows_exist["qty"]!== $qty){
								$data_history = array(
										'transaction_type'  => 1,
										'pro_id'     		=> $id,
										'date'				=> new Zend_Date(),
										'location_id' 		=> $user_location,
										'Remark'			=> $post['remark'],
										'qty_edit'        	=> $rows_exist["qty"]." -> ".$qty,
										'qty_before'        => $rows_exist["qty"],//qty have in recode table
										'qty_after'        	=> $qty,
										'user_mod'			=> $GetUserId
								);
								$db->insert("tb_move_history", $data_history);
								unset($data_history);
									$_qty_deffer = $qty-$rows_exist["qty"];
								$_rs=$db_global->getQtyFromProductById($id);
								if(!empty($_rs)){
									$_qtydata= array(
											'qty_onhand'   => $_rs['qty_onhand']+$_qty_deffer,
											'qty_available'=> $_rs['qty_available']+$_qty_deffer,
									);
									$where = $db->quoteInto("pro_id=?", $id);
									$this->update($_qtydata, $where);
								}
								
							}
						}
				}
				$db->commit();
				return true;
			}catch (Exception $e){
				$db->rollBack();
				Application_Form_FrmMessage::messageError("UPDATE_FAIL", $e->getMessage());
			}
			
	}
	//for add new product 
	function addNewItem($post){
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user = new Zend_Session_Namespace('auth');
			$GetUserId= $session_user->user_id;
			$data=array(
					'cate_id'		=> $post["pro_name"],
					//'type'			=> $post["pro_name"],
					
					'item_name'		=> $post["pro_name"],
					'p_code'		=> $post["pro_code"],
					'barcode'=>
// 					'brand_id'
					'cate_id'		=> $post["category_id"],//test
					'branch_id'	 	=> 
					'remark'		=> $post['remark'],
					'last_usermod'	=> $GetUserId,
					'last_mod_date'	=> new Zend_Date(),//test
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
 final function addStockLocation($post){
		$db_global = new Application_Model_DbTable_DbGlobal();
		$session_user=new Zend_Session_Namespace('auth');
		$GetUserId= $session_user->user_id;
		$datalocation = array(
				"Name"			=> $post["location_name"],
				"user_id"		=> $GetUserId,
				"last_mod_date"	=> new Zend_Date(),
				"contact"		=> $post["ContactName"],
				"phone"			=> $post["ContactNumber"],
				"stock_add"		=> $post["location_add"],
				"remark"		=> $post["remark_add"]
				);
		$GetLocationId = $db_global->addRecord($datalocation, "tb_sublocation");
		return $GetLocationId;
 	
 }
}