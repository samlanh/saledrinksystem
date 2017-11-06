<?php

class Application_Model_GlobalClass  extends Zend_Db_Table_Abstract
{
	public function getImgStatus($rows,$base_url, $case=''){
		if($rows){			
			$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
			$imgtick='<img src="'.$base_url.'/images/icon/tick.png"/>';
			 
			foreach ($rows as $i =>$row){
				if($row['status'] == 1){
					$rows[$i]['status'] = $imgtick;
				}
				else{
					$rows[$i]['status'] = $imgnone;
				}
			}
		}
		return $rows;
	}
	public function getImgActive($rows,$base_url, $case=''){
		if($rows){
			$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
			$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
	
			foreach ($rows as $i =>$row){
				if($row['is_avaliable'] == 1){
					$rows[$i]['is_avaliable'] = $imgtick;
				}
				else{
					$rows[$i]['is_avaliable'] = $imgnone;
				}
			}
		}
		return $rows;
	}
	public function getStockType($rows,$base_url, $case=''){
		if($rows){
			$stockable = "Stockable";
			$nonestock = "None Stock";
			$service = "Service";
	
			foreach ($rows as $i =>$row){
				if($row['type'] == 1){
					$rows[$i]['type'] = $stockable;
				}
				elseif($row['type'] == 2){
					$rows[$i]['type'] = $nonestock;
				}
				else{
					$rows[$i]['type'] = $service;
				}
			}
		}
		return $rows;
	}
	public function getActive($rows,$base_url, $case=''){
		if($rows){
			$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
			$imgtick='<img src="'.$base_url.'/images/icon/tick.png"/>';
	
			foreach ($rows as $i =>$row){
				if($row['IsActive'] == 1){
					$rows[$i]['IsActive'] = $imgtick;
				}
				else{
					$rows[$i]['IsActive'] = $imgnone;
				}
			}
		}
		return $rows;
	}
	public function getpublic($rows,$base_url, $case=''){
		if($rows){
			$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
			$imgtick='<img src="'.$base_url.'/images/icon/tick.png"/>';
	
			foreach ($rows as $i =>$row){
				if($row['public'] == 1){
					$rows[$i]['public'] = $imgtick;
				}
				else{
					$rows[$i]['public'] = $imgnone;
				}
			}
		}
		return $rows;
	}
	public function getTransactionType($rows,$base_url, $case=''){
		if($rows){
			//$adjust    = "Stock Adjustment";
			//$transfer_stock = "Stock Transfer";
			//$received  = "Received";
			//$return  = "Return Stock Out(V)";
			foreach ($rows as $i =>$row){
				if($row['transaction_type'] == 1){
					$rows[$i]['transaction_type'] = "Stock Adjustment";
				}
				elseif($row['transaction_type'] == 2){
					$rows[$i]['transaction_type'] = "Stock Transfer";
				}
				elseif($row['transaction_type'] == 3){
					$rows[$i]['transaction_type'] = "Received";
				}
				elseif($row['transaction_type'] == 4){
					$rows[$i]['transaction_type'] = "Return Stock Out(V)";
				}
				elseif($row['transaction_type'] == 5){
					$rows[$i]['transaction_type'] = "Return Stock In(V)";
				}
				elseif($row['transaction_type'] == 6){
					$rows[$i]['transaction_type'] = "Return Stock Out(C)";
				}
				else{
					$rows[$i]['transaction_type'] = "Return Stock In(C)";
				}
				
			}
		}
		return $rows;
	}
	public function getStatusType($rows,$base_url, $case=''){
		if($rows){
			foreach ($rows as $i =>$row){
				if($row['status'] == 1){
					$rows[$i]['status'] = "Quote";
				}
				elseif($row['status'] == 2){
					$rows[$i]['status'] =  "Open";	
				}
				elseif($row['status'] == 3){
					$rows[$i]['status'] = "In Progress";		
				}
				elseif($row['status'] == 4){
					$rows[$i]['status'] = "Paid";
				}
				elseif($row['status'] == 5){
					$rows[$i]['status'] = "Fully Received";
				}
				else{
					$rows[$i]['status'] = "Cancelled";
				}
			}
		}
		return $rows;
	}
	public function getSaleStatusType($rows,$base_url, $case=''){
		if($rows){
			foreach ($rows as $i =>$row){
				if($row['status'] == 1){
					$rows[$i]['status'] = "Quote";
				}
				elseif($row['status'] == 2){
					$rows[$i]['status'] =  "Open";
				}
				elseif($row['status'] == 3){
					$rows[$i]['status'] = "In Progress";
				}
				elseif($row['status'] == 4){
					$rows[$i]['status'] = "Paid";
				}
				elseif($row['status'] == 5){
					$rows[$i]['status'] = "Fully Delivery";
				}
				else{
					$rows[$i]['status'] = "Cancelled";
				}
			}
		}
		return $rows;
	}
	public function getReturnStatusType($rows,$base_url, $case=''){
		if($rows){
			foreach ($rows as $i =>$row){
				if($row['status'] == 1){
					$rows[$i]['status'] = "Open";
				}
				elseif($row['status'] == 0){
					$rows[$i]['status'] =  "Returned";
				}
				
				else{
					$rows[$i]['status'] = " ";
				}
			}
		}
		return $rows;
	}
	public function getTypeHistory($rows,$base_url, $case=''){
		if($rows){
			$purchase_order = "Purchase Order";
			$sales_order = "Sales Order";
			$service = "Service";
			foreach ($rows as $i =>$row){
				if($row['type'] == 1){
					$rows[$i]['type'] = $purchase_order;
				}
				elseif($row['type'] == 2){
					$rows[$i]['type'] = $sales_order;
				}
				else{
					$rows[$i]['type'] = $service;
				}
			}
		}
		return $rows;
	}
	public function getDayName($key = ''){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$day_name = array(
							'su' => $tr->translate('SU'),
							'mo' => $tr->translate('MO'),
							'tu' => $tr->translate('TU'),
							'we' => $tr->translate('WE'),
							'th' => $tr->translate('TH'),
							'fr' => $tr->translate('FR'),
							'sa' => $tr->translate('SA')							
						 );
		if(empty($key)){
			return $day_name;
		}
		return  $day_name[$key];
	}
	 public function getYesNoOption(){
		//Select Public for report
			$myopt = '<option value="" label="---Select----">---Select----</option>';
			$myopt .= '<option value="Yes" label="Yes">Yes</option>';
			$myopt .= '<option value="No" label="No">No</option>';
	    	return $myopt;
		} 
		protected function GetuserInfoAction(){
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			return $result;
		}
	//get location on select
	public function getLocationOption(){
		$db = $this->getAdapter();
		$user = $this->GetuserInfoAction();
		$sql = "SELECT LocationId,Name FROM tb_sublocation WHERE Name!='' AND status = 1 ";
		
		if($user["level"]!=1 AND $user["level"]!=2){
			$sql .= "AND LocationId = ".$user["location_id"];
		}
			$sql.=" ORDER BY LocationId DESC";
		$rows = $db->fetchAll($sql);
		$option ="";
		foreach($rows as $value){
			$option .= '<option label="'.htmlspecialchars($value['Name'], ENT_QUOTES).'" value="'.$value['LocationId'].'">'.htmlspecialchars($value['Name'], ENT_QUOTES).'</option>';
		}
		if($user["level"]==1 OR $user["level"]==2){
			//$option = '<option label="Select Location" value="">Select Location</option>';
			$option.= '<option label="Add New Location" value="-1">Add New Location</option>';
		}
		return $option;
	}
	public function getLocationAssign(){
		$db = $this->getAdapter();
		$user = $this->GetuserInfoAction();
		$sql = "SELECT id,name FROM tb_sublocation WHERE name!='' AND status=1 ";
		$sql.=" ORDER BY id DESC";
		$rows = $db->fetchAll($sql);
		$option ="";
		foreach($rows as $value){
			$option .= '<option label="'.htmlspecialchars($value['name'], ENT_QUOTES).'" value="'.$value['id'].'">'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $option;
	}
	
// 	public function getLocationSelected($id, $currentItem = null){
// 		$user = $this->GetuserInfoAction();		
// 		$db = $this->getAdapter();
// 		$sql = "SELECT l.LocationId,l.Name FROM tb_sublocation AS l ";
// 		if($user["level"]!=1){
// 			$sql .= "WHERE l.LocationId = ".$user["location_id"];
// 		}		
// 		$rows = $db->fetchAll($sql);
// 		$option = '';
// 		foreach($rows as $value){
// 			$option .= '<option value="'.$value['LocationId'].'" label="'.htmlspecialchars($value['Name'], ENT_QUOTES).'">'.htmlspecialchars($value['Name'], ENT_QUOTES).'</option>';
// 		}
// 		return $option;
// 	}
	
// 	public function tolocationOption(){
// 		$user = $this->GetuserInfoAction();
// 		$db = $this->getAdapter();
// 		$sql = "SELECT l.LocationId,l.Name FROM tb_sublocation AS l WHERE l.Name!='' AND l.status = 1";
// 		$rows = $db->fetchAll($sql);
// 		$options = '';
// 		foreach($rows as $value){
// 			$options .= '<option value="'.$value['LocationId'].'" label="'.htmlspecialchars($value['Name'], ENT_QUOTES).'">'.htmlspecialchars($value['Name'], ENT_QUOTES).'</option>';
// 		}
// 		return $options;
// 	}
	
	public function getProductOption(){
		$db = $this->getAdapter();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$sql_cate = 'SELECT `id`,name FROM tb_category WHERE status = 1 AND name!="" ORDER BY name ';
		
		$row_cate = $db->fetchAll($sql_cate);
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$option="";		
		if($result["level"]==1 OR $result["level"]==2){
			$option .= '<option value="-1">Please Select Product</option>';
		}
		foreach($row_cate as $cate){
			$option .= '<optgroup  label="'.htmlspecialchars($cate['name'], ENT_QUOTES).'">';
			if($result["level"]==1){
				$sql = "SELECT id,item_name,
				(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id LIMIT 1) as measue_name,
				unit_label,qty_perunit,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=brand_id limit 1) As brand_name,
				barcode AS item_code FROM tb_product WHERE cate_id = ".$cate['id']." 
						AND item_name!='' AND status=1 ORDER BY item_name ASC";
			}else{
				$sql = " SELECT p.id,p.item_name,p.barcode AS item_code ,
				(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=p.measure_id LIMIT 1) as measue_name,
				p.unit_label,p.qty_perunit,
				(SELECT tb_brand.name FROM `tb_brand` WHERE tb_brand.id=p.brand_id limit 1) As brand_name
				 FROM tb_product AS p				
				WHERE p.cate_id = ".$cate['id']."
				AND p.item_name!='' AND p.status=1 ORDER BY p.item_name ASC ";
			}//AND p.item_name!='' AND p.status=1  ORDER BY p.item_name ASC ";
			//INNER JOIN tb_prolocation As pl ON p.id = pl.pro_id
			//AND pl.location_id =".$result['branch_id']." 
			
				$rows = $db->fetchAll($sql);
				if($rows){
					foreach($rows as $value){
						$option .= '<option value="'.$value['id'].'" >'.
							htmlspecialchars($value['item_name']." ".$value['brand_name'], ENT_QUOTES)." ".htmlspecialchars($value['item_code'].'(1'.$value['measue_name'].'='.$value['qty_perunit'].$value['unit_label'].')', ENT_QUOTES)
						.'</option>';
					}
				}
			$option.="</optgroup>";
		}
		
		return $option;
	}
	public function selectProductOption(){//not add item to this select box
		$db = $this->getAdapter();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$sql_cate = 'SELECT `id`,name FROM tb_category WHERE status = 1 AND name!=""';
	
		$row_cate = $db->fetchAll($sql_cate);
		$option="";
		foreach($row_cate as $cate){
			$option .= '<optgroup  label="'.htmlspecialchars($cate['name'], ENT_QUOTES).'">';
			if($result["level"]==1 OR $result["level"]==2){
				$sql = "SELECT pro_id,item_name,item_code FROM tb_product WHERE is_avaliable = 1 AND cate_id = ".$cate['CategoryId']."
				AND item_name!='' ORDER BY last_mod_date DESC ";
			}else{
				$sql = "SELECT p.pro_id,p.item_name,p.item_code FROM tb_product AS p
						INNER JOIN tb_prolocation  As pl ON p.id= pl.pro_id
				 WHERE p.is_avaliable = 1 AND p.cate_id = ".$cate['id']."
				AND p.item_name!='' AND pl.LocationId =".$result['location_id']." ORDER BY p.last_mod_date DESC ";
			}
			$rows = $db->fetchAll($sql);
			if($rows){
				foreach($rows as $value){
					$option .= '<option value="'.$value['pro_id'].'" label="'.htmlspecialchars($value['item_name'], ENT_QUOTES).'">'.
							htmlspecialchars($value['item_name'], ENT_QUOTES)." ".htmlspecialchars($value['item_code'], ENT_QUOTES)
							.'</option>';
				}
			}
			$option.="</optgroup>";
		}
		return $option;
	}
	
	public function getTypePriceOption(){
		$db = $this->getAdapter();
		$sql = 'SELECT type_id, price_type_name FROM tb_price_type WHERE public = 1 AND price_type_name!=""';
		$rows = $db->fetchAll($sql);
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$option="";
		if(!empty($rows))foreach($rows as $price){
					$option .= '<option value="'.$price['type_id'].'" label="'.htmlspecialchars($price['price_type_name'], ENT_QUOTES).'">'.htmlspecialchars($price['price_type_name'], ENT_QUOTES).'</option>';
		}
		if($result["level"]==1 OR $result["level"]==2){
			$option .= '<option value="-1" label="Add Price Type">Add Price Type</option>';
		}
		return $option;
	}
	public function getOptonsHtml($sql, $display, $value){
		$db = $this->getAdapter();
		$option = '<option value="" label="--- Select ---">--- Select ---</option>';
		foreach($db->fetchAll($sql) as $r){
				
			$option .= '<option value="'.$r[$value].'" label="'.htmlspecialchars($r[$display], ENT_QUOTES).'">'.htmlspecialchars($r[$display], ENT_QUOTES).'</option>';
		}
		return $option;
	}
	
}

