<?php 
Class report_Model_DbQuery extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getInvoiceNumRow($cu_id){
		$db= $this->getAdapter();
		$sql="SELECT COUNT(i.id) FROM `tb_invoice` AS i,`tb_sales_order` AS s WHERE i.`sale_id`=s.`id` AND s.`customer_id`=$cu_id";
		return $db->fetchOne($sql);
	}
		
	public function getAllPurchaseReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,invoice_no,date_order,date_in,
		(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
		currency_id,
		net_total,paid,balance,balance_after,
		(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=payment_method LIMIT 1 ) as payment_method,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1 ) As purchase_status,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=2 LIMIT 1),
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
		FROM `tb_purchase_order`  ";
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE status=1 and ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		
		if($search['status_paid']>0){
			if($search['status_paid']==1){
				$where .= " AND balance <=0 ";
			}
			elseif($search['status_paid']==2){
				$where .= " AND balance >0 ";
			}
				
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductPruchaseById($id){//2
		$db = $this->getAdapter();
		$sql=" SELECT 
				 (SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
				 p.order_number,p.date_order,p.date_in,p.remark,
				 p.commission,p.commission_ensur,p.bank_name,p.date_issuecheque,
				 (SELECT item_name FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS item_name,
				 (SELECT item_code FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS item_code,
				 
				(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= po.pro_id LIMIT 1)) as measue_name,
				(SELECT qty_perunit FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS qty_perunit,
				(SELECT unit_label FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS unit_label,
				 (SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=p.payment_method) as payment_method,
				 p.payment_number,

				 (SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
				 (SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
				 (SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
				 (SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
				 (SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
				 (SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
				 (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
				 po.qty_order,po.qty_unit,po.qty_detail,po.price,po.sub_total,p.net_total,
				 
				 p.paid,p.discount_real,p.tax,
				 p.balance
				 FROM `tb_purchase_order` AS p,
				`tb_purchase_order_item` AS po WHERE p.id=po.purchase_id
				 AND po.status=1 AND p.id = $id ";
		return $db->fetchAll($sql);
	}
	function getPruchaseProductDetail($search){//3
		$db = $this->getAdapter();
		$sql=" SELECT
			(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
			it.item_name,
		    it.item_code,
			(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		    (SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
			(SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
			(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
			(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
			(SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
			(SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
			(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
			(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
			po.qty_order,po.price,po.sub_total,p.currency_id,p.net_total,
			p.id,p.order_number,p.date_order,p.date_in,p.remark,
			p.paid,p.discount_real,p.tax,
			p.balance
			FROM `tb_purchase_order` AS p,
			`tb_purchase_order_item` AS po,
             tb_product AS it
			 WHERE p.id=po.purchase_id AND it.id=po.pro_id 
			AND po.status=1  AND p.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " p.date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.date_order <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$s_where[] = " p.net_total LIKE '%{$s_search}%'";
			$s_where[] = " p.paid LIKE '%{$s_search}%'";
			$s_where[] = " p.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['brand_id']>0){
			$where .= " AND it.brand_id =".$search['brand_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND p.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllSaleOrderReport($search){//4
		$db= $this->getAdapter();
		$sql="  SELECT s.id,
				(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
				(SELECT CONCAT(cust_name,' ',contact_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
				(SELECT NAME FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
				s.sale_no,s.date_sold,s.all_total,s.tax,s.discount_value,
				(s.net_total+s.transport_fee) AS net_total,s.paid,s.balance,
				(SELECT block_name FROM tb_zone WHERE tb_zone.id=c.zone_id LIMIT 1) AS zone,
				(SELECT p.province_en_name FROM ln_province AS p WHERE p.province_id=c.province_id LIMIT 1)AS province,
				(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name
				FROM `tb_sales_order` AS s,tb_customer AS c
				WHERE s.customer_id=c.id
				";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_search = str_replace(' ', '',$s_search);
			$s_where[] = "REPLACE(s.sale_no,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.net_total,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.paid,' ','')  		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.balance,' ','')  	LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		
		if($search['province_id']>0){
			$where .= " AND c.province_id = ".$search['province_id'];
		}
		
		if($search['zone_id']>0){
			$where .= " AND c.zone_id = ".$search['zone_id'];
		}
		
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id = ".$search['saleagent_id'];
		}
 
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.sale_no,s.date_sold,s.remark,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_value,s.tax,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	function getSaleProductDetail($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT s.saving_id,
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		it.qty_perunit AS qty_perunit,
		it.unit_label AS unit_label,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=it.measure_id LIMIT 1) as measue_name,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_unit,so.qty_detail,so.qty_order,so.price,so.sub_total,s.net_total,
		s.id,s.sale_no,s.date_sold,s.remark,
		s.paid,s.tax,s.discount_value,
		s.balance,so.saleorder_id
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so,
		tb_product AS it
		WHERE s.id=so.saleorder_id AND it.id=so.pro_id
		AND s.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_search = str_replace(' ', '',$s_search);
			$s_where[] = "REPLACE(it.item_name,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(it.item_code,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(it.barcode,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.sale_no,' ','') 	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.net_total,' ','') 	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(s.paid ,' ','')   	LIKE  '%{$s_search}%'";
			$s_where[] = "REPLACE(s.balance,' ','')     LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['point']>-1 && $search['point']!=''){
			$where .= " AND s.saving_id =".$search['point'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id =".$search['customer_id'];
		}
// 		$dbg = new Application_Model_DbTable_DbGlobal();
// 		$where.=$dbg->getAccessPermission();
		//$order=" ORDER BY so.saleorder_id DESC";
		//echo $sql.$where;exit();
		return $db->fetchAll($sql.$where);
	}
	function getAllCustomer($search){//7
		$db = $this->getAdapter();
	
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE id=branch_id LIMIT 1) AS branch_name,
		cust_name,phone,
		(SELECT NAME FROM `tb_price_type` WHERE id=customer_level LIMIT 1) As level,
		contact_name,contact_phone,address,email,fax,website,remark,
		( SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=status LIMIT 1) status,
		( SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=user_id LIMIT 1) AS user_name
		FROM `tb_customer` WHERE cust_name!=''  ";
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " cust_name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			
			$s_where[] = " contact_name LIKE '%{$s_search}%'";
			$s_where[] = " contact_phone LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
				
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " fax LIKE '%{$s_search}%'";
			$s_where[] = " website LIKE '%{$s_search}%'";
			$s_where[] = " remark LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
	
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND id = ".$search['customer_id'];
		}
		if($search['level']>0){
			$where .= " AND customer_level = ".$search['level'];
		}
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllSaleAgent($search){
		$sql = "SELECT sg.id,l.name AS branch_name,
			   sg.name, sg.phone, sg.email, sg.address, sg.job_title, sg.description
		FROM tb_sale_agent AS sg
		INNER JOIN tb_sublocation As l ON sg.branch_id = l.id WHERE 1 ";
		$order=" ORDER BY sg.id DESC ";
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " l.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.phone LIKE '%{$s_search}%'";
			$s_where[] = " sg.email LIKE '%{$s_search}%'";
			$s_where[] = " sg.address LIKE '%{$s_search}%'";
			$s_where[] = " sg.job_title LIKE '%{$s_search}%'";
			$s_where[] = " sg.description LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		$order=" ORDER BY id DESC ";
		$db =$this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getProductPurchaseDetail($search){
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql= " SELECT
		pur.id,
		pur.`order_number`,
		pur.`date_in`,
		p.item_name,
		p.item_code,
		SUM(pio.qty_order) AS qty_order,
		br.Name AS Brand,
		cg.Name AS cate_name,
		(SELECT
		NAME
		FROM
		tb_sublocation
		WHERE `id` = pur.`branch_id`) AS branch ,
		pl.qty AS qty_location
		FROM
		tb_product AS p,
		tb_category AS cg,
		tb_brand AS br,
		tb_purchase_order_item AS pio,
		tb_purchase_order AS pur,
		tb_prolocation AS pl
		WHERE p.id = pio.pro_id
		AND cg.id = p.cate_id
		AND br.id = p.brand_id
		AND pl.`id`=pur.`id`
		AND pl.`pro_id`=pio.`pro_id`
		AND pio.purchase_id = pur.id
		AND pur.`date_in` BETWEEN '$start_date' AND '$end_date'";
	}
	
	public function getItem($data){
		$db = $this->getAdapter();
    	$sql = "SELECT p.pro_id, p.item_name,p.item_code FROM tb_product AS p ";
			
    		if($data["LocationId"]!="" OR $data["LocationId"]!=0){
    			$sql.= " INNER JOIN tb_prolocation AS pl ON pl.pro_id = p.pro_id AND pl.`LocationId`= ".trim($data["LocationId"]);
    		}
    		$sql.="WHERE p.item_name!=''";
    		
			if($data["branch_id"]!="" OR $data["branch_id"]!=0){
				$sql.= " AND p.`brand_id`=".trim($data["branch_id"]);
			}
			if($data["category_id"]!="" OR $data["category_id"]!=0){
				$sql.=" AND p.`cate_id`=".trim($data["category_id"]);
			}
			$sql.=" ORDER BY p.item_name";
			
			return $db->fetchAll($sql);
	}
	
	public function getSalesItem($data){
		$db=$this->getAdapter();
		
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql= "SELECT 
					p.item_name,
					p.item_code,
					SUM(si.qty_order) AS qty_order,
					br.Name AS Brand,
					cg.Name AS cate_name,
					s.`sale_no`,
					s.`date_sold` , 
					(SELECT NAME FROM tb_sublocation WHERE `id` = s.`branch_id`) AS branch,
					pl.qty 
					FROM tb_product AS p,
					tb_category AS cg,
					tb_brand AS br,
					`tb_salesorder_item` AS si,
					tb_sales_order AS s ,
					tb_prolocation AS pl 
					WHERE p.id = si.pro_id AND cg.id = p.cate_id 
					AND br.id = p.brand_id 
					AND si.saleorder_id = s.id 
					AND pl.`pro_id`=si.`pro_id` 
					AND si.saleorder_id = s.id ";
				  

// 				  AND '$end_date' ";
			if(($data["item"]!="" AND $data["item"]!=0 )){
				//$sql.=" AND "."(p.item_name LIKE '%".trim($data["item"])."%' OR p.item_code LIKE '%".trim($data["item"]."%')");
				$sql.=" AND p.pro_id = ".trim($data["item"]);
			}
			if($data["category_id"]!="" AND $data["category_id"]!=0){
				$sql.=" AND cg.CategoryId = ".trim($data["category_id"]);
			}
			if($data["branch_id"]!="" AND $data["branch_id"]!=0){
				$sql.=" AND br.branch_id = ".trim($data["branch_id"]);
			}
			if($data["LocationId"]!="" AND $data["LocationId"]!=0){
				$sql.=" AND s.LocationId = ".trim($data["LocationId"]);
			}
			$result =  $this->GetuserInfo();
			if ($result["level"]!=1 AND $result["level"]!=2 ){
				$sql.=" AND s.LocationId = ".trim($result["location_id"]);
			}
// 			echo $sql;exit();
			
// 		$sql.=" GROUP BY si.id ORDER BY s.date_order DESC";
		return $db->fetchAll($sql);
	}
	public function getProductSummary($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		
		$sql_po=" SELECT po.LocationId, po.date_in, po.status, po.is_active, pi.pro_id, SUM( pi.qty_order ) AS qty_order
							FROM tb_purchase_order po, tb_purchase_order_item pi
							WHERE po.order_id = pi.order_id
							AND po.date_in
							BETWEEN  '$start_date'
							AND  '$end_date' ";
		$sql_so = " SELECT so.LocationId, so.date_order, so.status, so.is_active, si.pro_id, SUM( si.qty_order ) AS qty_sales
							FROM tb_sales_order so, tb_sales_order_item si
							WHERE so.order_id = si.order_id
							AND so.date_order
							BETWEEN  '$start_date'
							AND  '$end_date'  ";
		
		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
			$sql_po.=" AND po.LocationId = ".trim($data["LocationId"]);
			$sql_so.=" AND so.LocationId = ".trim($data["LocationId"]);
		}
		
		$sql_po.=" GROUP BY pi.pro_id ";
		$sql_so.=" GROUP BY si.pro_id";
		
		$sql_vpo = " CREATE OR REPLACE VIEW v_po AS ".$sql_po;
		$sql_vso = " CREATE OR REPLACE VIEW v_so AS ".$sql_so;
						
		$db->query($sql_vpo);
		$db->query($sql_vso);
		
		$sql=" SELECT p.`pro_id` , p.item_name, p.item_code, po.qty_order AS qty_po, so.qty_sales AS qty_so,p.`qty_onhand`,p.`qty_onorder`,p.`qty_onsold`
					FROM tb_product AS p
					LEFT JOIN v_so AS so ON p.pro_id = so.pro_id ";
		if(($data["item"]!="" AND $data["item"]!=0 )){
			$sql.=" INNER JOIN v_po AS po ON p.pro_id = po.pro_id AND p.pro_id = ".trim($data["item"]);
		}
		else{
			$sql.=" LEFT JOIN v_po AS po ON p.pro_id = po.pro_id ";
		}
		if($data["category_id"]!="" AND $data["category_id"]!=0){
			$sql.=" AND p.cate_id = ".trim($data["category_id"]);
		}
		if($data["branch_id"]!="" AND $data["branch_id"]!=0){
			$sql.=" AND p.brand_id = ".trim($data["branch_id"]);
		}
		
			
		$sql.=" GROUP BY p.pro_id ORDER BY p.item_name  ";
		return $db->fetchAll($sql);
	}
	//get location name for report at other location
	public function getLocationName($location_id){
		$db=$this->getAdapter();
		$sql="SELECT name FROM tb_sublocation WHERE id = ".$location_id;
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getBrandByUser(){
		$db=$this->getAdapter();
		$user = $this->GetuserInfo();
		if($user["level"]==3 OR $user["level"]==4 ){
			$sql="SELECT Name FROM tb_sublocation WHERE LocationId = ".$user["location_id"];
			$row=$db->fetchRow($sql);
			return $row;
		}
		else{
			return false;
		}
		
	}
	
	public function getQtyTransfer($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
	
// 		$sql = "SELECT p.item_name, p.item_code, SUM( ti.qty) AS qty,br.Name AS Brand,cg.Name AS cate_name
// 		FROM tb_product AS p,tb_category As cg,tb_branch AS br, tb_transfer_item AS ti, tb_stocktransfer AS t
// 			WHERE 
// 	             ti.transfer_id= t.transfer_id
// 	             AND p.pro_id = ti.pro_id
// 				 AND cg.CategoryId=p.cate_id
// 				 AND br.branch_id=p.brand_id
// 		AND t.transfer_date  BETWEEN '$start_date' AND '$end_date'";
		$sql = " SELECT 
					  p.item_name,
					  p.item_code,
					  SUM(ti.qty) AS qty,
					  t.`invoice_num`,
					  br.Name AS Brand,
					  cg.Name AS cate_name,
					  t.`transfer_date`,
					  (SELECT 
					    sl.`name` 
					  FROM
					    `tb_sublocation` AS sl 
					  WHERE sl.`id` = t.`from_location`) AS From_location,
					  (SELECT 
					    sl.`name` 
					  FROM
					    `tb_sublocation` AS sl 
					  WHERE sl.`id` = t.`to_location`) AS to_location ,
					  (SELECT 
					    u.title 
					  FROM
					    `tb_acl_user` AS u 
					  WHERE u.user_id = t.`user_id`) AS title,
					  (SELECT 
					    u.fullname 
					  FROM
					    `tb_acl_user` AS u 
					  WHERE u.user_id = t.`user_id`) AS user_name 
					FROM
					  tb_product AS p,
					  tb_category AS cg,
					  tb_brand AS br,
					  tb_transfer_item AS ti,
					  tb_stocktransfer AS t,
					  tb_sublocation AS sl 
					WHERE ti.transfer_id = t.transfer_id 
					  AND p.id = ti.pro_id 
					  AND cg.id = p.cate_id 
					  AND br.id = p.brand_id ";
					  
// 					  AND t.transfer_date BETWEEN '$start_date' 
// 					  AND '$end_date' ";
		
// 		if(($data["item"]!="" AND $data["item"]!=0 )){
// 			$sql.=" AND p.pro_id = ".trim($data["item"]);
// 		}
// 		if($data["category_id"]!="" AND $data["category_id"]!=0){
// 			$sql.=" AND cg.CategoryId = ".trim($data["category_id"]);
// 		}
// 		if($data["branch_id"]!="" AND $data["branch_id"]!=0){
// 			$sql.=" AND br.branch_id = ".trim($data["branch_id"]);
// 		}
// 		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
// 			$sql.=" AND t.From_location = ".trim($data["LocationId"]);
// 		}
		
// 		if($data["to_LocationId"]!="" AND $data["to_LocationId"]!=0){
// 			$sql.=" AND t.to_location = ".trim($data["to_LocationId"]);
// 		}
// 		$result =  $this->GetuserInfo();
// 		if ($result["level"]!=1 AND $result["level"]!=2 ){
// 			$sql.=" AND t.to_location = ".trim($result["location_id"]);
// 		}
			
// 		$sql.=" GROUP BY transfer_no,p.pro_id ";
		return $db->fetchAll($sql);
	}
	
	public function geProducttQty($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
	
		// 		$sql = "SELECT p.item_name, p.item_code, SUM( ti.qty) AS qty,br.Name AS Brand,cg.Name AS cate_name
		// 		FROM tb_product AS p,tb_category As cg,tb_branch AS br, tb_transfer_item AS ti, tb_stocktransfer AS t
		// 			WHERE
		// 	             ti.transfer_id= t.transfer_id
		// 	             AND p.pro_id = ti.pro_id
		// 				 AND cg.CategoryId=p.cate_id
		// 				 AND br.branch_id=p.brand_id
		// 		AND t.transfer_date  BETWEEN '$start_date' AND '$end_date'";
		$sql = " SELECT 
				  pl.id,
				  p.`item_name`,
				  p.`item_code`,
				  pl.qty,
				
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id = pl.`location_id`) AS location,
				  b.`name` AS branch,
				  c.`name` AS category
				FROM
				  tb_prolocation  AS pl,
				  `tb_brand` AS b,
				  `tb_category` AS c,
				   tb_product AS p
				  WHERE b.`id`=p.`brand_id`
				  AND c.`id`=p.`cate_id`
				  AND pl.`pro_id`=p.`id` ";	
					
// 		if(($data["item"]!="" AND $data["item"]!=0 )){
// 		$sql.="AND p.pro_id = ".trim($data["item"]);
// 	    }
// 		if($data["category_id"]!="" AND $data["category_id"]!=0){
// 		$sql.=" AND c.`CategoryId` = ".trim($data["category_id"]);
// 		}
// 				if($data["branch_id"]!="" AND $data["branch_id"]!=0){
// 				$sql.=" AND b.`branch_id` = ".trim($data["branch_id"]);
// 		}
// 		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
// 		$sql.=" AND LocationId = ".trim($data["LocationId"]);
// 		}
// 				$result =  $this->GetuserInfo();
// 				if ($result["level"]!=1 AND $result["level"]!=2 ){
// 		$sql.=" AND LocationId = ".trim($result["location_id"]);
// 		}
			
// 		$sql.=" ORDER BY pl.pro_id ";
		return $db->fetchAll($sql);
	}
	public function getTopTenProductSO(){
		$db = $this->getAdapter();
		$sql = " SELECT  p.item_name, SUM( si.qty_order ) AS qty
					FROM tb_product AS p,tb_sales_order_item AS si, tb_sales_order AS s
					WHERE p.pro_id = si.pro_id
					AND si.order_id = s.order_id AND s.status=4 AND s.date_order  >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
		 GROUP BY si.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	
	public function getTopTenProductSOByDate($data){
		$db = $this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql = " SELECT  p.item_name, SUM( si.qty_order ) AS qty
		FROM tb_product AS p,tb_sales_order_item AS si, tb_sales_order AS s
		WHERE p.pro_id = si.pro_id
		AND si.order_id = s.order_id AND s.status=4 AND s.date_order  BETWEEN '$start_date' AND '$end_date'
		GROUP BY si.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	public function getTopTenProductPO(){
		$db = $this->getAdapter();
		$sql = " SELECT p.item_name, SUM( pi.qty_order ) AS qty
					FROM tb_product AS p,tb_purchase_order_item AS pi, tb_purchase_order AS pur
				WHERE p.pro_id = pi.pro_id
				AND pi.order_id = pur.order_id
				AND pur.status = 4
				AND pur.date_in >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
		                GROUP BY pi.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getAllQuotation($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT NAME FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.quoat_number,s.date_order,all_total,discount_value,net_total,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1) as is_approved ,
		(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) pending_status,
		s.currency_id,
		s.net_total,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approval_by
		FROM `tb_quoatation` AS s WHERE s.is_cancel=0 ";
		$from_date =(empty($search['start_date']))? '1': " s.date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_order <= '".$search['end_date']." 23:59:59'";
		$where = " AND  ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.quoat_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllCustomerLost($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT NAME FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.quoat_number,s.date_order,all_total,discount_value,net_total,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT name_en FROM `tb_view` WHERE type=14 AND key_code=s.is_cancel LIMIT 1) as is_cancel ,
		(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) pending_status,
		s.currency_id,s.cancel_comment,
		s.net_total,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.cancel_user LIMIT 1 ) AS cancel_by
		FROM `tb_quoatation` AS s WHERE s.is_cancel=1 AND s.is_approved=2 ";
		$from_date =(empty($search['start_date']))? '1': " s.cancel_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.cancel_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND  ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.quoat_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id =".$search['saleagent_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getQuotationById($id){//5
		$db = $this->getAdapter();
		$sql=" 
		SELECT
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		(SELECT branch_code FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_code,
		s.id,
		s.quoat_number,s.date_order,s.remark,s.discount_value,s.discount_type,s.all_total,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1)) as measue_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approval_by,
		s.approval_note,s.is_cancel,s.cancel_comment,s.cancel_date,
		so.qty_unit,so.qty_detail,so.qty_order,so.price,so.disc_value,so.disc_type,so.old_price,so.sub_total,s.net_total
		FROM `tb_quoatation` AS s,
		`tb_quoatation_item` AS so WHERE s.id=so.quoat_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	public function getAllDeliveryReport($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT s.id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT name FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.sale_no,s.date_sold,s.tax,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id limit 1) As curr_name,
		(SELECT d.`is_completed` FROM `tb_deliverynote` AS d WHERE d.`invoice_id`=iv.id) AS is_deliver,
		s.currency_id,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = iv.user_id LIMIT 1 ) AS user_name,
		iv.sub_total,iv.discount,iv.paid_amount as ivpaid,iv.balance,
		(SELECT SUM(tb_receipt_detail.paid) FROM `tb_receipt_detail` WHERE tb_receipt_detail.invoice_id=iv.id LIMIT 1) as paid_amount,
		iv.id as invoice_id,iv.invoice_no,iv.invoice_date
		FROM `tb_sales_order` AS s ,tb_invoice as iv WHERE iv.sale_id = s.id AND iv.status=1  AND s.status=1 AND iv.is_approved=1 ";
		$from_date =(empty($search['start_date']))? '1': " iv.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " iv.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " iv.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id = ".$search['saleagent_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission(" s.branch_id ");
		$order=" ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductDelivyerId($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id limit 1) AS branch_name,
		(SELECT branch_code FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_code,
		(SELECT prefix FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS prefix,
		(SELECT show_by FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS show_by,
		(SELECT logo FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS logo,
		s.sale_no,s.date_sold,s.remark,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS measure_id,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
				(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1)) as measue_name,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) As model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT p.`province_en_name` FROM `ln_province` AS p WHERE p.`province_id` = (SELECT c.`province_id` FROM `tb_customer` AS c WHERE c.`id`=s.customer_id LIMIT 1) limit 1) AS province,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		so.qty_order,so.qty_unit,so.qty_detail,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_real,s.tax,
		s.balance,v.invoice_no,(SELECT tb_deliverynote.deli_date FROM `tb_deliverynote` WHERE tb_deliverynote.invoice_id=v.id limit 1) AS deli_date
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so ,tb_invoice as v WHERE v.sale_id =s.id AND s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	public function getAllSaleByStaffReport($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT s.id,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT name FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.saleagent_id,
		s.sale_no,s.date_sold,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id limit 1) As curr_name,
		s.currency_id,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = iv.user_id LIMIT 1 ) AS user_name,
		iv.sub_total,iv.discount,iv.paid_amount as ivpaid,iv.balance,
		(SELECT SUM(tb_receipt_detail.paid) FROM `tb_receipt_detail` WHERE tb_receipt_detail.invoice_id=iv.id LIMIT 1) as paid_amount,
		iv.id as invoice_id,iv.invoice_no,iv.invoice_date
		FROM `tb_sales_order` AS s ,tb_invoice as iv WHERE iv.sale_id = s.id AND iv.status=1  AND s.status=1 AND iv.is_approved=1 ";
		$from_date =(empty($search['start_date']))? '1': " iv.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " iv.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " iv.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id = ".$search['saleagent_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission(" s.branch_id ");
		$order=" ORDER BY s.saleagent_id ASC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvoiceById($id){//5
		$db = $this->getAdapter();
		$sql="SELECT
		(SELECT branch_code FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_code,
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_name,
		(SELECT prefix FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS prefix,
		(SELECT show_by FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS show_by,
		(SELECT logo FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS logo,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS measure_id,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1)) as measue_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) AS model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		s.customer_id,s.discount_type,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT contact_phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_phone,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT p.`province_en_name` FROM `ln_province` AS p WHERE p.`province_id` = (SELECT c.`province_id` FROM `tb_customer` AS c WHERE c.`id`=s.customer_id LIMIT 1)) AS province,
		so.qty_order,so.sub_total AS dsub_total,so.qty_unit,so.qty_detail,so.price,so.old_price,so.disc_value,
		(SELECT CONCAT(tb_acl_user.title,'. ',tb_acl_user.fullname) FROM tb_acl_user  WHERE tb_acl_user.user_id = v.user_id LIMIT 1 ) AS biller,
		v.invoice_no,v.invoice_date,v.user_id,v.balance_after,v.sub_total,v.discount AS vdiscount,
		(SELECT SUM(paid) FROM `tb_receipt_detail` WHERE tb_receipt_detail.invoice_id=v.id) AS paid_amount,
		v.balance,v.deposit,v.id AS invoice_id,
		s.branch_id
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so ,tb_invoice AS v WHERE v.sale_id =s.id AND s.id=so.saleorder_id
		AND s.status=1 AND s.id =$id ";
		return $db->fetchAll($sql);
	}
	function getCustomerPayment($customer_id,$sale_id){
		$db = $this->getAdapter();
		$sql=" SELECT v.id,v.sale_id,v.invoice_no,v.invoice_date,v.sub_total,v.discount,
		(SELECT SUM(paid) FROM `tb_receipt_detail` WHERE invoice_id=v.id) as paid_amount,
		v.balance,v.balance_after,v.is_approved FROM 
		`tb_invoice` AS v,tb_sales_order As s WHERE v.sale_id = s.id AND customer_id = $customer_id 
		AND s.id!= $sale_id AND v.is_fullpaid =0  AND v.balance_after>0  ";
		//echo $customer_id."<br />".$sale_id;
		return $db->fetchAll($sql);
	}
	function getAllExpense($search){
		$db=$this->getAdapter();
		$sql = "SELECT e.*,
		(SELECT tb_expensetitle.title_en FROM `tb_expensetitle` WHERE tb_expensetitle.id=e.title LIMIT 1) as title_en,
		(SELECT tb_expensetitle.title FROM `tb_expensetitle` WHERE tb_expensetitle.id=e.title LIMIT 1) as title,
		(SELECT name FROM `tb_sublocation` WHERE id=e.branch_id LIMIT 1) AS branch_name,
		(SELECT description FROM tb_currency WHERE tb_currency.id=e.curr_type) AS curr_name,
		(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = e.user_id)  AS user_name
		FROM tb_income_expense as e  WHERE e.status=1  ";
		$where= ' ';
		$order=" ORDER BY e.for_date DESC  ";
		 
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".$search['end_date']." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
		 
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND e.user_id = ".$search['user'] ;
		}
		if($search['branch_id']>-1){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['title']>0){
			$where.= " AND title = ".$search['title'];
		}
		 
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_where[] = " e.title LIKE '%{$s_search}%'";
			$s_where[] = " e.desc LIKE '%{$s_search}%'";
			$s_where[] = " e.invoice LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpensePurchaseNoSum($search){
		$db=$this->getAdapter();
		$sql = "  SELECT vp.id,
					vp.`receipt_no`,
					vp.`expense_date`,
					vp.`remark`,
					vp.expense_id,
					(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = vp.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name, 
					(SELECT v.`v_name` FROM `tb_vendor` AS  v WHERE v.`vendor_id`=vp.`vendor_id` LIMIT 1) vendor,
					(SELECT tb_expensetitle.title FROM `tb_expensetitle` WHERE tb_expensetitle.id=vp.expense_id LIMIT 1) AS title_kh, 
					(SELECT tb_expensetitle.title_en FROM `tb_expensetitle` WHERE tb_expensetitle.id=vp.expense_id LIMIT 1) AS title_en,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id=vp.`user_id`) AS `user`,
					 vp.paid AS total_paid 
 FROM `tb_vendor_payment` AS vp WHERE vp.status=1 ";
		$where= ' ';
		$order=" GROUP BY vp.branch_id ,vp.expense_id  ";
			
		$from_date =(empty($search['start_date']))? '1': " vp.expense_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " vp.expense_date <= '".$search['end_date']." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
			
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND vp.user_id = ".$search['user'] ;
		}
		if($search['branch_id']>-1){
			$where.= " AND vp.branch_id = ".$search['branch_id'];
		}
		if($search['title']>-0){
			$where.= " AND vp.expense_id = ".$search['title'];
		}
			
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
// 			$s_where[] = " e.title LIKE '%{$s_search}%'";
// 			$s_where[] = " e.desc LIKE '%{$s_search}%'";
// 			$s_where[] = " e.invoice LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
 		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	function getAllExpensePurchase($search){
		$db=$this->getAdapter();
		$sql = " SELECT vp.id,
(SELECT NAME FROM `tb_sublocation` WHERE tb_sublocation.id = vp.branch_id AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
(SELECT tb_expensetitle.title FROM `tb_expensetitle` WHERE tb_expensetitle.id=vp.expense_id LIMIT 1) AS title_kh,
(SELECT tb_expensetitle.title_en FROM `tb_expensetitle` WHERE tb_expensetitle.id=vp.expense_id LIMIT 1) AS title_en,
SUM(vp.paid) AS total_paid
 FROM `tb_vendor_payment` AS vp WHERE vp.status=1 ";
		$where= ' ';
		$order=" GROUP BY vp.branch_id ,vp.expense_id  ";
			
		$from_date =(empty($search['start_date']))? '1': " vp.expense_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " vp.expense_date <= '".$search['end_date']." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
			
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND vp.user_id = ".$search['user'] ;
		}
		if($search['branch_id']>-1){
			$where.= " AND vp.branch_id = ".$search['branch_id'];
		}
		if($search['title']>-0){
			$where.= " AND vp.expense_id = ".$search['title'];
		}
			
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
// 			$s_where[] = " e.title LIKE '%{$s_search}%'";
// 			$s_where[] = " e.desc LIKE '%{$s_search}%'";
// 			$s_where[] = " e.invoice LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
// 		echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpenseType($search){
		$db=$this->getAdapter();
		$sql = "SELECT e.id,e.branch_id,e.title,e.desc,SUM(e.total_amount) as total_amount,e.curr_type,
		(SELECT tb_expensetitle.title FROM `tb_expensetitle` WHERE tb_expensetitle.id=e.title LIMIT 1) as title,
		(SELECT tb_expensetitle.title_en FROM `tb_expensetitle` WHERE tb_expensetitle.id=e.title LIMIT 1) as title_en,
		
		(SELECT name FROM `tb_sublocation` WHERE id=e.branch_id LIMIT 1) AS branch_name,
		(SELECT description FROM tb_currency WHERE tb_currency.id=e.curr_type) AS curr_name,
		(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = e.user_id)  AS user_name
		FROM tb_income_expense as e  WHERE e.status=1  ";
		$where= ' ';
		$order=" GROUP BY e.branch_id, e.curr_type, e.title ORDER BY e.for_date DESC ";
			
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".$search['end_date']." 23:59:59'";
		$where .= "  AND ".$from_date." AND ".$to_date;
			
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['user'])){
			$where.=" AND e.user_id = ".$search['user'] ;
		}
		if($search['branch_id']>-1){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['title']>-0){
			$where.= " AND title = ".$search['title'];
		}
			
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['text_search']));
			$s_where[] = " e.title LIKE '%{$s_search}%'";
			$s_where[] = " e.desc LIKE '%{$s_search}%'";
			$s_where[] = " e.invoice LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);
	}
	public function getVendorBalance($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,date_order,date_in,invoice_no,
		net_total,
		(SELECT SUM(paid) FROM `tb_vendorpayment_detail` WHERE tb_vendorpayment_detail.invoice_id=tb_purchase_order.id) as paid,
		 balance,balance_after,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=2 LIMIT 1),
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
		FROM
			`tb_purchase_order` WHERE balance_after>0 AND status=1 ";
		
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY date_order DESC ";
		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getchequeWithdrawalWaring($search){//1
			$db= $this->getAdapter();
		$sql=" SELECT r.id,
		(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
		(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS vendor_name,
		r.`date_input`,
		r.`total`,r.`paid`,r.`balance`,
		(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id` LIMIT 1) AS payment_name,
		cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
		(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name,
		DATEDIFF(r.`che_withdrawaldate`, NOW())
		FROM `tb_vendor_payment` AS r WHERE DATEDIFF(r.`che_withdrawaldate`,NOW() )<2 AND DATEDIFF(r.`che_withdrawaldate`,NOW() )=0 ";
			
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[] = " r.`total` LIKE '%{$s_search}%'";
			$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
			$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND r.`branch_id` = ".$search['branch_id'];
		}
		if($search['suppliyer_id']>0){
			$where .= " AND r.vendor_id =".$search['suppliyer_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getchequeWithdrawal($search){//1
			$db= $this->getAdapter();
		$sql=" SELECT r.id,
		(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
		(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS vendor_name,
		r.`date_input`,
		r.`total`,r.`paid`,r.`balance`,
		(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id` LIMIT 1) AS payment_name,
		cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
		(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name
		FROM `tb_vendor_payment` AS r ";
			
		$from_date =(empty($search['start_date']))? '1': " r.`date_input` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.`expense_date` <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[] = " r.`total` LIKE '%{$s_search}%'";
			$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
			$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND r.`branch_id` = ".$search['branch_id'];
		}
		if($search['suppliyer_id']>0){
			$where .= " AND r.vendor_id =".$search['suppliyer_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getCustomerPaidByInvoice($invoice_id){
		$db = $this->getAdapter();
		$sql=" SELECT SUM(paid) AS paid_amount FROM `tb_receipt_detail` WHERE invoice_id=$invoice_id LIMIT 1";
		return $db->fetchOne($sql);
	}
	function getAllSaleAmount($search){
		$db = $this->getAdapter();
		$sql=" SELECT SUM(s.all_total) AS sale_amount,SUM(s.discount_value) AS discount_amount
		FROM 
			`tb_sales_order` AS s
		WHERE s.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " s.`date_sold` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "  s.`date_sold` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$sql .= " AND s.`branch_id` = ".$search['branch_id'];
		}
		return $db->fetchRow($sql);
	}
	function getAllCostPrice($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
		SUM(si.qty_order*si.cost_price) AS total_cost
		FROM
		`tb_sales_order` AS s,
		tb_salesorder_item AS si
		WHERE s.status=1 AND si.saleorder_id = s.id ";
		$from_date =(empty($search['start_date']))? '1': " s.`date_sold` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "  s.`date_sold` <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$sql .= " AND s.`branch_id` = ".$search['branch_id'];
		}
		return $db->fetchRow($sql);
	}
	//functi
	function calcualteCostofGooldSold($search){
// 		$db = $this->getAdapter();
// 		$sql=" SELECT SUM(si.cost_price*si.qty_order) AS total_cost FROM `tb_invoice` AS v, `tb_salesorder_item` AS si
// 			WHERE si.saleorder_id = v.sale_id AND v.status =1 AND v.is_approved=1 ";
// 		$from_date =(empty($search['start_date']))? '1': " v.invoice_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': " v.invoice_date <= '".$search['end_date']." 23:59:59'";
// 		$sql.= " AND  ".$from_date." AND ".$to_date;
// 		if($search['branch_id']>0){
// 			$sql .= " AND v.`branch_id` = ".$search['branch_id'];
// 		}
// 		return $db->fetchRow($sql);
	}
	function getProductSoldDetail($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=v.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=it.measure_id LIMIT 1) as measue_name,
		it.qty_perunit AS qty_perunit,
		it.unit_label AS unit_label,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		SUM(so.qty_order) AS qty_order,
		SUM(so.qty_unit) AS qty_unit,
		SUM(so.qty_detail) AS qty_detail
		FROM `tb_invoice` AS v,
		`tb_salesorder_item` AS so,
		tb_product AS it
		WHERE v.sale_id=so.saleorder_id AND it.id=so.pro_id
		AND v.status =1 AND v.is_approved=1 ";
		$from_date =(empty($search['start_date']))? '1': " v.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND v.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order="  GROUP BY so.pro_id ORDER BY v.branch_id ,qty_order DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductSoldByStaff($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=v.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=it.measure_id LIMIT 1) as measue_name,
		it.qty_perunit AS qty_perunit,
		it.unit_label AS unit_label,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		(SELECT `name` FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) as staff_name,
		s.saleagent_id,
		SUM(so.qty_order) AS qty_order,
		SUM(so.qty_unit) AS qty_unit,
		SUM(so.qty_detail) AS qty_detail,s.saleagent_id
		FROM `tb_invoice` AS v,
		`tb_salesorder_item` AS so,
		tb_sales_order as s,
		tb_product AS it
		WHERE v.sale_id=so.saleorder_id AND it.id=so.pro_id
		AND v.status =1 AND v.is_approved=1 AND s.id = so.saleorder_id ";
		$from_date =(empty($search['start_date']))? '1': " v.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id =".$search['saleagent_id'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND v.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission('v.branch_id');
		$order="  GROUP BY s.saleagent_id,so.pro_id ORDER BY s.saleagent_id,v.branch_id ,qty_order DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllReceipt($search){
		$db = $this->getAdapter();
		$sql = " SELECT c.id,
		(SELECT name FROM `tb_sublocation` WHERE id=c.branch_id) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=c.customer_id LIMIT 1 ) AS customer_name,
		c.customer_id,c.payment_type,c.payment_id,c.receipt_no,c.receipt_date,c.total,c.paid,c.balance,c.remark,
		(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = c.`user_id` LIMIT 1) AS user_name
		 FROM `tb_receipt` as c
		WHERE 1 ";
		$from_date =(empty($search['start_date']))? '1': " c.receipt_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " c.receipt_date <= '".$search['end_date']." 23:59:59'";
		$where= " AND ".$from_date." AND ".$to_date;
		
		$order= " ORDER BY receipt_date DESC "; 
		if($search['branch_id']>0){
			$where .= " AND c.branch_id =".$search['branch_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND c.customer_id = ".$search['customer_id'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	public function getClientBystaff($search = null){//rpt-loan-released
		$db= $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " iv.invoice_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " iv.invoice_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$from_date =(empty($search['start_date']))? '1': " receipt_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " receipt_date <= '".$search['end_date']." 23:59:59'";
		$whereeeceipt = " AND ".$from_date." AND ".$to_date;
		
		
		
		$sql=" SELECT s.id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT contact_phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_phone,
		(SELECT cu_code FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS cu_code,
		(SELECT name FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		count(s.id) as times,
		s.saleagent_id,
		sum(iv.sub_total) as sub_total,
		sum(iv.discount)  as discount,
		sum(iv.paid_amount) as ivpaid,
		sum(iv.balance) as balance,
		(SELECT SUM(tb_receipt_detail.paid) FROM `tb_receipt_detail`,tb_receipt as re WHERE re.id=tb_receipt_detail.receipt_id AND re.customer_id =s.customer_id $whereeeceipt ) as paid_amount
		FROM `tb_sales_order` AS s ,tb_invoice as iv WHERE iv.sale_id = s.id AND iv.status=1  AND s.status=1 AND iv.is_approved=1 ";
		
// 		$where="";
		if(!empty($search['text_search'])){
			$s_where = array();
// 			$s_search = trim(addslashes($search['text_search']));
// 			$s_where[] = " iv.invoice_no LIKE '%{$s_search}%'";
// 			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
// 			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
// 			$s_where[] = " s.paid LIKE '%{$s_search}%'";
// 			$s_where[] = " s.balance LIKE '%{$s_search}%'";
// 			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .=" AND s.customer_id = ".$search['customer_id'];
		}
		if($search['saleagent_id']>0){
			$where .= " AND s.saleagent_id = ".$search['saleagent_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission(" s.branch_id ");
		$order=" GROUP BY s.customer_id ORDER BY s.saleagent_id DESC ";
		
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getInvoiceDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  v.id,
				  v.`invoice_no`,
				  v.`invoice_date`,
				  v.`balance`,
				  v.`balance_after`,
				  v.`discount`,
				  v.`discount_after`,
				  v.`sub_total`,
				  v.`sub_total_after`,
				  v.`paid_amount`,
				  v.`paid_after` ,
				  (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=si.`pro_id` LIMIT 1) AS item_name ,
				  (SELECT `cust_name` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS customer_name,
   (SELECT `contact_name` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS contact_name,
    (SELECT `phone` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS contact_phone,
    (SELECT `email` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS email,
    (SELECT `address` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS add_name,
    (SELECT sg.`name` FROM `tb_sale_agent` AS sg WHERE sg.id=(SELECT s.`saleagent_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) AS staff_name,
  (SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=v.`branch_id` LIMIT 1) AS branch,
  (SELECT p.`province_kh_name` FROM `ln_province` AS p WHERE p.`province_id`=(SELECT `province_id` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1) LIMIT 1) LIMIT 1) AS province ,
  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=v.`user_id`) AS biller ,
				  si.`qty_order`,
				  si.`price`,
				  si.`sub_total`,
				  s.`net_total`,
				  s.`discount_real`
				FROM
				  `tb_invoice` AS v,
				  `tb_salesorder_item` AS si,
				  `tb_sales_order` AS s
				WHERE v.`sale_id`=s.id AND s.id=si.`saleorder_id` AND v.id=$id";
				
		return $db->fetchAll($sql);
	}
	
	function getInvoice($data){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  v.id,
				  v.`invoice_no`,
				  v.`invoice_date`,
				  v.`sub_total`,
				  v.`paid_amount`,
				  v.`balance_after`,
				  (SELECT `cust_name` FROM `tb_customer` AS c WHERE c.id=(SELECT s.`customer_id` FROM `tb_sales_order` AS s WHERE s.id=v.`sale_id` LIMIT 1)) AS customer_name,
				  (SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=v.`branch_id` LIMIT 1) AS branch,
				(SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=v.`user_id`) AS user_name,
				  'View'
				FROM
				  `tb_invoice` AS v ORDER BY v.`invoice_date` DESC";
				  
		return $db->fetchAll($sql);
	}
	function getInvoicePayment($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					r.`receipt_no`,
					r.`receipt_date`,
					rd.`paid`,
					v.`invoice_no`,
					v.`sub_total`,
	(SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=r.`user_id`) AS user_name,
	r.`remark`
				FROM
				  `tb_receipt` AS r ,
				  `tb_receipt_detail` AS rd,
				  `tb_invoice` AS v
				WHERE r.id=rd.`receipt_id` AND rd.`invoice_id`=v.`id` AND rd.`invoice_id`=$id";
				
		return $db->fetchAll($sql);
	}
	
	function getCustomerInvoice($data,$id=null){
		$db = $this->getAdapter();
		$sql = "SELECT
				  v.id,
				  v.`invoice_no`,
				  v.`invoice_date`,
				  v.`sub_total`,
				  v.`paid_amount`,
				  v.`balance` ,
				  v.balance_after ,
				  s.`customer_id`,
				  (SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=v.`branch_id` LIMIT 1) AS branch,
				  (SELECT c.`cust_name` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) AS customer,
				  (SELECT c.`contact_name` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) AS contact_name,
				  (SELECT c.`phone` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) AS phone,
				  (SELECT c.`email` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) AS email,
				  (SELECT c.`address` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) AS address,
				  (SELECT p.`province_en_name` FROM `ln_province` AS p WHERE p.`province_id`=(SELECT c.`province_id` FROM `tb_customer` AS c WHERE c.id=s.`customer_id` LIMIT 1) LIMIT 1) AS province,
				(SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=v.`user_id`) AS user_name,
				(SELECT sg.`name` FROM `tb_sale_agent` AS sg WHERE sg.id=s.`saleagent_id` LIMIT 1) AS staff_name,
				(SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=v.`user_id`) AS biller,
				'View'
				FROM
				  tb_invoice AS v,
				  `tb_sales_order` AS s 
				  WHERE v.`sale_id`=s.`id` 
				  ";
			if($id!=null){
				$sql.=" AND s.`customer_id`=$id";
			}
			if(!empty($data['text_search'])){
				$s_where = array();
	 			$s_search = trim(addslashes($data['text_search']));
	 			$s_where[] = " v.invoice_no LIKE '%{$s_search}%'";
	 			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
	 			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
	 			$s_where[] = " s.paid LIKE '%{$s_search}%'";
	 			$s_where[] = " s.balance LIKE '%{$s_search}%'";
	 			$sql .=' AND ('.implode(' OR ',$s_where).')';
			}
			if(@$data["customer_id"]>0){
				$sql.=" AND s.`customer_id`=".$data["customer_id"];
			}
			if(@$data["branch_id"]>0){
				$sql.=" AND v.`branch_id`=".$data["branch_id"];
			}
			$order = " ORDER BY s.`customer_id`";
			//echo $sql.$order;
		return $db->fetchAll($sql.$order);
	}
	
	function getCustomer($data){
		$db = $this->getAdapter();
		$sql ="SELECT 
				  c.id,
				  c.id AS customer_id,
				  c.`contact_name`,
				  c.`contact_phone` AS  phone,
				  c.`cust_name` AS customer,
				  c.`email`,
				  c.`address`,
				  (SELECT p.`province_en_name` FROM `ln_province` AS p WHERE p.`province_id`=c.`province_id`) AS province,
				  (SELECT v.`invoice_no` FROM `tb_invoice` AS  v WHERE v.`sale_id`=s.`id` LIMIT 1) AS invoice_no,
				  (SELECT v.`invoice_date` FROM `tb_invoice` AS  v WHERE v.`sale_id`=s.`id` LIMIT 1) AS invoice_date,
				  (SELECT v.`sub_total` FROM `tb_invoice` AS  v WHERE v.`sale_id`=s.`id` LIMIT 1) AS sub_total,
				  (SELECT v.`paid_amount` FROM `tb_invoice` AS  v WHERE v.`sale_id`=s.`id` LIMIT 1) AS paid_amount,
				  (SELECT v.`balance_after` FROM `tb_invoice` AS  v WHERE v.`sale_id`=s.`id` LIMIT 1) AS balance_after,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=s.`user_mod`) AS user_name,
				  (SELECT sg.`name` FROM `tb_sale_agent` AS sg WHERE sg.id=s.`saleagent_id` LIMIT 1) AS staff_name,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=s.`user_mod`) AS biller,
				  (SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=s.`branch_id` LIMIT 1) AS branch,
				  'View'
				FROM
				  `tb_customer` AS c LEFT JOIN `tb_sales_order` AS s ON c.id=s.`customer_id` and c.status=1";
		$limit ='';
		$order = "";
		if($data["branch_id"]>0){
				$sql.=" AND s.`branch_id`=".$data["branch_id"];
			}		  
		if($data["customer_id"]>0){
				$sql.=" AND s.`customer_id`=".$data["customer_id"];
				$limit=' LIMIT 1';
				$order = " ORDER BY s.`customer_id`";
			}
		return $db->fetchAll($sql.$limit);
	}
	
	//report alert customer payment 
	function getAlertCustomerPayment($search=null){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
// 		$branch_id = $_db->getAccessPermission('sp.branch_id');
		$sql="SELECT s.id,s.saleagent_id,s.sale_no,
			       c.cust_name,c.contact_name,c.contact_phone,c.cu_code,
			       (SELECT sg.name FROM tb_sale_agent AS sg WHERE sg.id=s.saleagent_id LIMIT 1) AS sal_rep,
			        s.date_sold,s.payment_date,s.status,
			         s.net_total,s.discount_value,s.paid,s.balance,s.is_completed,
			       (SELECT v.name_en FROM tb_view AS v WHERE v.key_code=s.status AND v.type=5 LIMIT 1)AS status_name,
			       (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=s.`user_mod`) AS user_name
			       
				FROM tb_sales_order AS s,tb_salesorder_item AS sd,tb_customer AS c
				WHERE s.id=sd.saleorder_id
				AND  c.id=s.customer_id AND s.status
				AND  s.balance != 0 AND s.is_completed=0 ";
		$where = '';
		if(!empty($search["txt_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['txt_search']));
			$s_search = str_replace(' ', '',$s_search);
			$s_where[]="  REPLACE(s.sale_no,' ','') 	LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(c.cust_name,' ','') 	LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(c.contact_phone,' ','')LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(c.cu_code,' ','') 	LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(s.net_total,' ','') 	LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(s.paid,' ','') 		LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(s.balance,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(c.contact_name,' ','')LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		 
		if($search['customer_id']>0){
			$where.=' AND s.customer_id='.$search["customer_id"];
		}
		
		if($search["saleagent_id"]>0){
			$where.=' AND s.saleagent_id='.$search["saleagent_id"];
		}
	
		$order=" GROUP BY s.sale_no ORDER BY s.date_sold ASC";
		$str_next = '+1 day';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " s.payment_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$to_date;
		//echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
	}
}

?>