<?php

class Sales_Model_DbTable_Dbinvoiceapprove extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_invoice";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS customer_name,
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_sales_order.saleagent_id  LIMIT 1 ) AS staff_name,
			sale_no,date_sold,approved_date,
			(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
			all_total,discount_value,net_total,
			(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1),
			(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1),
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_sales_order` WHERE is_toinvocie=1 ";
			
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
			$order=" ORDER BY id DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvoieExisting($saleid){
		$db = $this->getAdapter();
		$sql="SELECT id,invoice_no FROM `tb_invoice` WHERE sale_id=$saleid limit 1 ";
		return $db->fetchRow($sql);
	}
	public function addInvoiceApproved($data)	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();

		if($data['approved_name']==1){	//approval and create invoice		
			$this->_name="tb_quoatation";
				$data_to = array(
						'pending_status'=>4,			
				);
				
			if(!empty($data['quote_id'])){
				$where=" id = ".$data['quote_id'];
				$this->update($data_to, $where);
			}
				
			$this->_name="tb_sales_order";
			$data_to = array(
						'is_toinvocie'=>1,
						'pending_status'=>4,
						'is_approved'=>$data['approved_name'],
						'approved_note'=>$data['app_remark'],
						'approved_date'=>$data['app_date'],
						'approved_userid'=>$GetUserId,
				);
			$where=" id = ".$data['id'];
			$this->update($data_to, $where);
				
			$dbg = new Application_Model_DbTable_DbGlobal();
			
			$rowexisting = $this->getInvoieExisting($data['id']);
			if(!empty($rowexisting)){// for update
				$invoice_number = $rowexisting['invoice_no'];
			}else{//for add
				$invoice_number = $dbg->getInvoiceNumber($data['branch_id']);
			}
			
			$this->_name="tb_invoice"; /// if invoice existing update
				$arr = array(
						'approved_note'=>$data['app_remark'],
						'sale_id'=>$data['id'],
						'branch_id'=>$data['branch_id'],
						'invoice_no'=>$invoice_number,
						'invoice_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'approved_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'user_id'=>$GetUserId,
						'sub_total'=>$data['all_total'],//$data['net_total'],
						'discount'=>$data['discount'],
						'paid_amount'=>0,
						'balance'=>$data['balance']+$data['deposit'],
						'sub_total_after'=>$data['all_total'],//$data['net_total'],
						'discount_after'=>$data['discount'],
						'paid_after'=>0,
						'balance_after'=>$data['balance']+$data['deposit'],
						'deposit'=>$data['deposit'],
						'is_approved'=>$data['approved_name'],
						);	
            if(!empty($rowexisting)){// for update
				$where = "sale_id = ".$data['id'];
				$this->update($arr,$where);
				$invoice_id=$rowexisting["id"];
			}else{//for add
				$invoice_id = $this->insert($arr);	
			}						
			
			$this->_name="tb_deliverynote";//if delevery existing update
			$arr = array(
			    'branch_id'=>$data['branch_id'],
				'invoice_id'=>$invoice_id,
				'so_id'=>$data['soid'],
				'delivery_userid'=>$data['app_remark'],
				'deli_date'=>date("Y-m-d",strtotime($data['dilivery_date'])),
				'user_id'=>$GetUserId,
				'notefrom_accounting'=>$data['notefrom_accountingk']);			
			if(!empty($rowexisting)){// for update
				$where = " invoice_id = ".$rowexisting['id'];
				$this->update($arr,$where);
			}else{//for add
				$this->insert($arr);
			}
			$result = 1;
			}else{// not approval //update to sale order
			
			$rowexisting = $this->getInvoieExisting($data['id']);
			if(!empty($rowexisting)){
				$this->_name="tb_invoice"; /// if invoice existing update
				
				$arr = array(
						'approved_note'=>$data['app_remark'],
						'sale_id'=>$data['id'],
						'branch_id'=>$data['branch_id'],
// 						'invoice_no'=>$invoice_number,
						'invoice_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'approved_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'user_id'=>$GetUserId,
						'sub_total'=>$data['all_total'],//$data['net_total'],
						'discount'=>$data['discount'],
						'paid_amount'=>0,
						'balance'=>$data['balance']+$data['deposit'],
						'sub_total_after'=>$data['all_total'],//$data['net_total'],
						'discount_after'=>$data['discount'],
						'paid_after'=>0,
						'balance_after'=>$data['balance']+$data['deposit'],
						'is_approved'=>$data['approved_name'],
						);	
						
				$where = "sale_id = ".$data['id'];
				$this->update($arr,$where);
				
				$this->_name="tb_deliverynote";
				$arr = array(
				'status'=>0,
			    'branch_id'=>$data['branch_id'],
// 				'invoice_id'=>$invoice_id,
				'so_id'=>$data['soid'],
				'delivery_userid'=>$data['app_remark'],
				'deli_date'=>date("Y-m-d",strtotime($data['dilivery_date'])),
				'user_id'=>$GetUserId,
				'notefrom_accounting'=>$data['notefrom_accountingk']);		
				
				$where = " invoice_id = ".$rowexisting['id'];
				$this->update($arr,$where);
			}
			
			    $this->_name="tb_quoatation";
				$data_to = array(
						'pending_status'=>1,
						'is_approved'=>0,		
                        'is_tosale'=>0,
						'is_approved'=>0,	
						
				);
			if(!empty($data['quote_id'])){
				$where=" id = ".$data['quote_id'];
				$this->update($data_to, $where);
			}
			   $this->_name="tb_sales_order";
				$data_to = array(
						'is_toinvocie'=>0,
						'pending_status'=>2,
						'is_approved'=>2,
						'approved_note'=>$data['app_remark'],
						'approved_date'=>$data['app_date'],
						'approved_userid'=>$GetUserId,
				);
				$where=" id = ".$data['id'];
				$this->update($data_to, $where);
				$result = 0;
			}			 
			$db->commit();
			return $result;
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			//echo 333;
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		s.id,
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		
		(SELECT branch_code FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_code,
		s.branch_id,
		s.sale_no,s.date_sold,s.remark,s.approved_note,s.approved_date,s.all_total,s.quote_id,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= so.pro_id LIMIT 1)) as measue_name,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) As model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approved_by,
		(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1) approval_status,
		(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) processing,
		so.qty_order,so.qty_unit,so.qty_detail ,so.price,so.old_price,so.sub_total,s.net_total,so.disc_value,so.disc_type,
		s.paid,s.discount_type,s.tax,s.discount_value,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	} 
}