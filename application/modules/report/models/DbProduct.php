<?php 
Class report_Model_DbProduct extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getBranch($id){
		$db = $this->getAdapter();
		$sql ="SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`='".$id."'";
		return $db->fetchOne($sql);
	}
	function getAllProduct($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
				  p.`id`,
				  p.`barcode`,
				  p.`item_code`,
				  p.`item_name` ,
	  			  p.`serial_number`,
	  			  p.`status`,
	  			  p.`unit_label`,
				  p.`qty_perunit`,
				   p.`price`,
				  pl.`location_id`,
				   (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=pl.`location_id` LIMIT 1) AS branch,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
				  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
				  (SELECT m.name FROM `tb_model` AS m WHERE m.id=p.`model_id` LIMIT 1) AS model,
				  (SELECT s.name FROM `tb_size` AS s WHERE s.id=p.`size_id` LIMIT 1) AS size,
				  (SELECT c.name FROM `tb_color` AS c WHERE c.id=p.`color_id` LIMIT 1) AS color,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=1 LIMIT 1) AS master_price,
				(SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=2 LIMIT 1) AS dealer_price,
				  SUM(pl.`qty`) AS qty
				FROM
				  `tb_product` AS p ,
				  `tb_prolocation` AS pl
				WHERE p.`id`=pl.`pro_id` ";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
			//$s_where[]= " cate LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["model"]!=""){
			$where.=' AND p.model_id='.$data["model"];
		}
		if($data["size"]!=""){
			$where.=' AND p.size_id='.$data["size"];
		}
		if($data["color"]!=""){
			$where.=' AND p.color_id='.$data["color"];
		}
		if($data["status"]!=-1){
			$where.=' AND p.status='.$data["status"];
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		//echo $location;
		$group = " GROUP BY p.`id` ORDER BY p.`item_name`";
		return $db->fetchAll($sql.$where.$location.$group);
		 
	}
	
	function getQtyProductByProIdLoca($id,$loc_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`qty` FROM `tb_prolocation` AS pl  WHERE pl.`pro_id`=$id AND pl.`location_id`=$loc_id";
		return $db->fetchOne($sql);
	}
	function getAllLOcation(){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix`,s.`id`  FROM `tb_sublocation` AS s WHERE s.`status`=1";
		return $db->fetchAll($sql);
	}
	
	function getAllAdjustStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
				  m.* ,
				  p.`item_name`,
				  p.`barcode`,
				  p.`item_code`,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id` = p.`brand_id`) AS brand ,
				  (SELECT b.`name` FROM `tb_category` AS b WHERE b.`id` = p.`cate_id`) AS cat ,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=4) AS color,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=2) AS model,
				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=3) AS size,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.id=m.`location_id` LIMIT 1) AS location,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=m.`user_mod` LIMIT 1) AS `username`,
				   m.`date`
				FROM
				  `tb_move_history` AS m ,
				  `tb_product` AS p
				WHERE m.`pro_id`=p.`id`";
		$where = '';
// 		if($data["ad_search"]!=""){
// 			$s_where=array();
// 			$s_search = addslashes(trim($data['ad_search']));
// 			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
// 			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
// 			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
// 			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
// 			//$s_where[]= " cate LIKE '%{$s_search}%'";
// 			$where.=' AND ('.implode(' OR ', $s_where).')';
// 		}
		if($data["pro_id"]!=""){
			$where.=' AND m.pro_id='.$data["pro_id"];
		}
		$location = $db_globle->getAccessPermission('m.`location_id`');
		//echo $location;
		return $db->fetchAll($sql.$where.$location);
			
	}
	
}

?>