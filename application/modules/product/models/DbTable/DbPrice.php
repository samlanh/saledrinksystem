<?php

class Product_Model_DbTable_DbPrice extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_price_type";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function addPriceType($data){
		$db  = $this->getAdapter();
		
		try {
			$_arr = array(
					"name" => $data["price_name"],
					"desc" 			  => $data["price_decs"],
					"status" 		  => $data["status"]
					);
		   
			if(@$data["id"]!=""){
				//print_r($data);exit();
				$where = $db->quoteInto("id=?", $data["id"]);
				$this->update($_arr, $where);
			}else{
				$this->insert($_arr);
			}
		}catch(Exception $e){
			
		}
	}
	
	public function setPriceItem($data){
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$identity = explode(',',$data['identity']);
			foreach ($identity as $i){
				$_arr = array(
						"product_id" => $data["pro_id"],
						"type_price" => $data["type_price_".$i],
						"price"		 => $data["price_".$i],
						"remark"	 => $data["remark_".$i],
						"desc"		 => $data["price_desc"],
				);
				$exist=$this->setPriceExist($data["pro_id"], $data["type_price_".$i]);
				if(!empty($exist)){//update if product price have exist
					$where=$this->getAdapter()->quoteInto('price_id=?',$exist['price_id']);
					$db->update("tb_product_price", $_arr,$where);
				}
				else{// add new if produt price doesn't exist
					$db->insert("tb_product_price", $_arr);
				}
			}
				$db->commit();			
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	public function updateItemPrice($data){
		try{
			$db= $this->getAdapter();
			$db->beginTransaction();
			$identity = explode(',', $data["identity"]);
			$sql = "DELETE FROM tb_product_price WHERE product_id = ".$data["id"];
			$db->query($sql);
			
			foreach ($identity As $i){
				$exist=$this->setPriceExist($data["pro_id"], $data["type_price_".$i]);
				if(!empty($exist)){
					$_arr = array(
							"product_id"=>$data["pro_id"],
							"type_price"=>$data["type_price_".$i],
							"price"=>$data["price_".$i],
							"remark"=>$data["remark_".$i],
							"desc"=>$data["price_desc"],
					);
					$where=$this->getAdapter()->quoteInto('price_id=?',$exist['price_id']);
					$db->update("tb_product_price", $_arr,$where);
				}
				else{
					$_arr = array(
							"product_id"=>$data["pro_id"],
							"type_price"=>$data["type_price_".$i],
							"price"=>$data["price_".$i],
							"remark"=>$data["remark_".$i],
							"desc"=>$data["price_desc"],
							);
					$db->insert("tb_product_price", $_arr);
				}
				
// 				$where=$this->getAdapter()->quoteInto('product_id=?',$data['id']);
// 				$db->update("tb_product_price", $_arr,$where);
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
		}
		
	}
	 function setPriceExist($pro_id,$type_price){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM tb_product_price WHERE product_id = $pro_id AND type_price = ".$type_price." LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	
	public function getPriceByItem($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM tb_product_price WHERE product_id = $id";
		return ($db->fetchAll($sql));
	}
	
	public function getTypePrice($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_price_type WHERE id = ".$id." LIMIT 1";
		return ($db->fetchRow($sql));
	}
	public function updatePricetype($post){
		$item_id = $post['item_id_'.$i];
		$_arr = array(
					"price_type_name" => $post["price_name"],
					"desc" 			  => $post["price_decs"],
					"public" 		  => $post["status"]
					);
		$where=$this->getAdapter()->quoteInto('type_id=?',$post['type_id']);
		$this->update($_arr,$where);
	}
	public function addMessageAlertItem($_data){
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$ids = explode(',',$_data['identity']);
			foreach ($ids as $i){
				$exist = $this->assMessageIsExist($_data["item_".$i]);
				if(empty($exist)){
					$_arr = array(
							"pro_id"=>$_data["item_".$i],
							"min_qty"=>$_data["min_qty_".$i],
							"message"=>$_data["sms_".$i],
							"remark"=>$_data["remark_".$i],
					);
					$db->insert("tb_qty_setting", $_arr);
				}
			}
			$db->commit();
				
		}catch(Exception $e){
			$db->rollBack();
				
				
		}
	
	}
	public function assMessageIsExist($id){
		$db=$this->getAdapter();
		$sql = "SELECT pro_id FROM tb_qty_setting WHERE pro_id = $id LIMIT 1";
		return ($db->fetchRow($sql));
	}
	public function updateAlertItem($_data){
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$_arr = array(
					"min_qty"=>$_data["min_qty_1"],
					"message"=>$_data["sms_1"],
					"remark"=>$_data["remark_1"],
			);
			$where = $this->getAdapter()->quoteInto("pro_id=?", $_data["id"]);
			$db->update("tb_qty_setting", $_arr,$where);
			$db->commit();
	
		}catch(Exception $e){
			$db->rollBack();
		}
	
	}
	public function getAlertbyItem($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM tb_qty_setting WHERE pro_id = $id LIMIT 1";
		return ($db->fetchAll($sql));
	}
			

// 	public function adjustPricing($post){
		
// 		$identity=explode(',',$post['identity']);
// 		foreach($identity as $i)
// 		{
// 			$item_id = $post['item_id_'.$i];
// 			$array = array("price" => $post["new_price_".$i]);
			
// 			$where=$this->getAdapter()->quoteInto('pro_id=?',$post['item_id_'.$i]);
// 			$this->update($array,$where);
// 		}
// 	}
}