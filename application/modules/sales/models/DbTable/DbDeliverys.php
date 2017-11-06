<?php

class Sales_Model_DbTable_DbDeliverys extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_invoice";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS customer_name,
			(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS contact_name,	
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_sales_order.saleagent_id  LIMIT 1 ) AS staff_name,
			sale_no,date_sold,
			is_todeliver,
			(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
			all_total,discount_value,net_total,
			(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1)AS appr_status ,
			(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) AS appr_pedding,
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name,
			(SELECT v.`invoice_no` FROM `tb_invoice` AS v WHERE v.`sale_id`=tb_sales_order.id LIMIT 1) as invoice_no
			FROM `tb_sales_order` WHERE `is_toinvocie`=1 ";
			
			$from_date =(empty($search['start_date']))? '1': " date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " sale_no LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND branch_id = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY date_sold DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function addDelivery($data)	{
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			
			$row = $this->getItemOrder($data["id"]);
			//print_r($row);
			if(!empty($row)){
				if($data["status"]==1){
					$completed = 1;
					$pendding_status = 5;
					$is_todelivery = 1;
					$approve = 1;
					$status = 1;
					foreach ($row as $rs){
						$rs_pro = $this->getProductExist($rs["pro_id"],$rs["branch_id"]);
						if(!empty($rs_pro)){
							$arr_pro = array(
								'qty'		=>	$rs_pro["qty"]-$rs["qty_order"],
							);
							$this->_name = "tb_prolocation";
							$where=" pro_id = ".$rs['pro_id']." AND location_id = ".$rs["branch_id"];
							//$where.=" AND location_id = ".$rs["branch_id"];
							$this->update($arr_pro,$where);
						}
					}
				}else{
					$completed = 0;
					$pendding_status = 1;
					$is_todelivery = 0;
					$approve = 2;
					$status=0;
				}
				//echo $approve;
				$this->_name="tb_quoatation";
					$data_to = array(
						'pending_status'	=>	$pendding_status,	
						//'is_todeliver'		=>	$is_todelivery,		
						'is_approved'		=>	$approve,
					);
					
				if(!empty($data['quote_id'])){
					$where=" id = ".$data['quote_id'];
					$this->update($data_to, $where);
				}
					
				$this->_name="tb_sales_order";
				$data_to = array(
							'pending_status'	=>	$pendding_status,
							'is_todeliver'		=>	$is_todelivery,
							'is_approved'		=>	$approve,
							);
				$where=" id = ".$data['id'];
				$this->update($data_to, $where);
				
				$this->_name="tb_invoice";
				$data_to = array(
							//'pending_status'	=>	$pendding_status,
							//'is_todeliver'		=>	$is_todelivery,
							'is_approved'		=>	$approve,
							);
				$where=" sale_id = ".$data['id'];
				$this->update($data_to, $where);
				
				$this->_name="tb_deliverynote";
				$arr = array(
					'is_completed'		=>		$completed,
					'date_mod'			=>		date("Y-m-d"),
					'deliver_name'		=>		$data["deliver_name"],
					'deliver_phone'		=>		$data["deliver_phone"],
					'status'			=>		1
				);
				$where=" so_id = ".$data['id'];
				$this->update($arr,$where);	
			}
			//echo $pendding_status;
			//exit();
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function cancelDelivery($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			
			$row = $this->getItemOrder($data["id"]);
			//print_r($row);
			if(!empty($row)){
					
				if($row[0]["is_completed"] == 1){
					$completed = 0;
					$pendding_status = 1;
					$is_todelivery = 0;
					foreach ($row as $rs){
						if($rs["is_completed"]==1){
							$rs_pro = $this->getProductExist($rs["pro_id"],$rs["branch_id"]);
							if(!empty($rs_pro)){
								$arr_pro = array(
									'qty'	=>	$rs_pro["qty"]+$rs["qty_order"],
								);
								$this->_name = "tb_prolocation";
								$where=" pro_id = ".$rs['pro_id']." AND location_id = ".$rs["branch_id"];
								//$where.=" AND location_id = ".$rs["branch_id"];
								$this->update($arr_pro,$where);
							}
						}
					}
				
					$this->_name="tb_quoatation";
						$data_to = array(
							'pending_status'	=>	$pendding_status,
							//'status'			=>	0,
							'is_approved'		=>	2
							//'is_todeliver'		=>	$is_todelivery,								
						);
						
					if(!empty($data['quote_id'])){
						$where=" id = ".$data['quote_id'];
						$this->update($data_to, $where);
						$db->getProfiler()->setEnabled(false);
					}
						
					$this->_name="tb_sales_order";
					$data_to = array(
								'pending_status'	=>	$pendding_status,
								'is_todeliver'		=>	$is_todelivery,
								'is_approved'		=>	2
								//'status'			=>	0,
								);
					$where=" id = ".$data['id'];
					$this->update($data_to, $where);
					
					$this->_name="tb_invoice";
					$data_to = array(
								'is_approved'		=>	2
								//'status'			=>	0,
								);
					$where=" sale_id = ".$data['id'];
					$this->update($data_to, $where);
					
					$this->_name="tb_deliverynote";
					$arr = array(
						'is_completed'		=>		$completed,
						'date_mod'			=>		date("Y-m-d"),
						'deliver_name'		=>		$data["deliver_name"],
						'deliver_phone'		=>		$data["deliver_phone"],	
						'status'			=>		0,
					);
					$where=" so_id = ".$data['id'];
					$this->update($arr,$where);	
				}
			}
			//exit();
			$db->commit();
			return $data["id"];
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.branch_id,
		s.sale_no,s.date_sold,s.remark,s.approved_note,s.approved_date,s.all_total,s.quote_id,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT `name` FROM `tb_measure` AS m WHERE m.id=(SELECT measure_id FROM `tb_product` AS p WHERE p.id=so.pro_id)) AS measure,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) AS model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE TYPE=7 AND key_code=s.is_approved LIMIT 1) approval_status,
		(SELECT pl.qty FROM `tb_prolocation` AS pl WHERE pl.pro_id=so.`pro_id` AND pl.`location_id`=s.`branch_id` LIMIT 1) AS qty,
		(SELECT name_en FROM `tb_view` WHERE TYPE=8 AND key_code=s.pending_status LIMIT 1) processing,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,so.disc_value,
		s.paid,s.discount_real,s.tax,s.discount_value,
		s.balance,
		d.`deli_date`,
		d.`deliver_name`,
		d.`deliver_phone`,
		d.`notefrom_accounting`,
		v.`invoice_no`,
		d.`is_completed`,
		s.approved_note
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so ,
		`tb_deliverynote` AS d,
		`tb_invoice` AS v
		WHERE s.id=so.saleorder_id
		AND s.status=1 
		AND s.`id`=d.`so_id`
		AND s.id=v.`sale_id`
		AND s.id = $id ";
		return $db->fetchAll($sql);
	} 
	
	function getItemOrder($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
			  so.`qty_order`,
			  s.`branch_id`,
			   so.`pro_id`,
			  (SELECT d.`is_completed` FROM `tb_deliverynote` AS d WHERE d.`so_id`=s.id) AS is_completed 
			FROM
			  `tb_sales_order` AS s,
			  `tb_salesorder_item` AS so 
			WHERE s.id = so.`saleorder_id` 
			  AND so.`saleorder_id` =$id";
		return $db->fetchAll($sql);
	}
	
	function getProductExist($id,$br_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`pro_id`,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=$id AND pl.`location_id`=$br_id";
		return $db->fetchRow($sql);
	}
}