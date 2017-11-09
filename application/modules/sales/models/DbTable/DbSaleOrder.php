<?php

class Sales_Model_DbTable_DbSaleOrder extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_sales_order";
	function getAllSaleOrder($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,sale_no,
				 	(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=tb_sales_order.customer_id LIMIT 1 ) AS contact_name,	
				 	date_sold,payment_date,
					net_total,discount_value,transport_fee,all_total,
					(SELECT SUM(paid) FROM `tb_receipt_detail` WHERE STATUS=1 AND invoice_id=tb_sales_order.id ) AS paid,
					(all_total-(SELECT SUM(paid) FROM `tb_receipt_detail` WHERE STATUS=1 AND invoice_id=tb_sales_order.id )) AS balance,
					'វិក្កយបត្រ','លុបវិក្កយបត្រ',
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1) AS user_name
					FROM `tb_sales_order` ";
			
			$from_date =(empty($search['start_date']))? '1': " date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_search = str_replace(' ', '', $s_search);
				$s_where[] = "REPLACE(sale_no,' ','')  	LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(net_total,' ','') LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(paid,' ','')  	LIKE '%{$s_search}%'";
				$s_where[] = "REPLACE(balance,' ','')  	LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			//echo $sql.$where;
			return $db->fetchAll($sql.$where.$order);
	}
	
	public function addSaleOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$so = $dbc->getSalesNumber($data["branch_id"]);

			$info_purchase_order=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					//"payment_method" => $data['payment_name'],
					"currency_id"    => 1,//$data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_type"  => 	$data['discount_type'],
					"net_total"      => 	$data['all_total'],
					//"paid"         => 	$data['paid'],
					//"balance"      => 	$data['remain'],
					//"tax"			 =>     $data["total_tax"],
					"user_mod"       => 	$GetUserId,
					'pending_status' =>3,
					"date"      => 	date("Y-m-d"),
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $sale_id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'old_price'   =>    $data['oldprice_'.$i],
						'cost_price'   =>    $data['cost_price_'.$i],
						'extra_price' =>    $data['extra_price'.$i],
						'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  =>    $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				/*$rows=$db_global ->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
				
				if($rows)
				{
						$datatostock   = array(
								'qty'   		=> 		$rows["qty"]-$data['qty'.$i],
								'last_mod_date'		=>	date("Y-m-d"),
								'last_mod_userid'=>$GetUserId
						);
						$this->_name="tb_prolocation";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					
				}else{
				}*/
			 }
			 
			 $ids=explode(',',$data['identity_term']);
			 if(!empty($data['identity_term'])){
				 foreach ($ids as $i)
				 {
				 	$data_item= array(
				 			'quoation_id'=> $sale_id,
				 			'condition_id'=> $data['termid_'.$i],
				 			"user_id"   => 	$GetUserId,
				 			"date"      => 	date("Y-m-d"),
							'term_type'=>2
				 			
				 	);
				 	$this->_name='tb_quoatation_termcondition';
				 	$this->insert($data_item);
				 }
			 }
			 
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function RejectSale($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			if($data['approved_name']==2){//reject sale update to quoatation
					$dbc=new Application_Model_DbTable_DbGlobal();
				    $pending=1;
					$arr=array(
							'is_approved'	=> $data['approved_name'],
							'approved_userid'=> $GetUserId,
							'approval_note'	=> $data['app_remark'],
							'approved_date'	=> date("Y-m-d",strtotime($data['app_date'])),
							'pending_status'=>$pending,
					);
					$this->_name="tb_quoatation";
					$where = " id = ".$data["id"];
					$sale_id = $this->update($arr, $where);
			}else{//can sale quoation or sale;
				$arr = array(
						'is_cancel'=>1,
						'cancel_comment'=>$data['app_remark'],
						'cancel_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'cancel_user'=>$GetUserId,
				);
				$where=" id=".$data['quote_id'];
				if(!empty($data['quote_id'])){
					$this->_name="tb_quoatation";
					$this->update($arr, $where);
				}
				
				$this->_name="tb_sales_order";
				$where=" id=".$data['id'];
				$this->update($arr, $where);
				
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updateSaleOrder($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $dbc->getSalesNumber($data["branch_id"]);
			$arr=array(
					"customer_id"   => 	$data['customer_id'],
					"branch_id"     => 	$data["branch_id"],
// 					"sale_no"       => 	$so,//$data['txt_order'],
					"date_sold"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 	$data['saleagent_id'],
					"currency_id"    => $data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_type"  => 	$data['discount_type'],
					"net_total"      => 	$data['all_total'],
					"user_mod"       => 	$GetUserId,
					"is_cancel"       => 	0,
 					'pending_status' =>2,
					'is_approved'=>0,
					'is_toinvocie'=>0,
					"date"      => 	date("Y-m-d"),
			);

			$this->_name="tb_sales_order";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			$this->_name='tb_salesorder_item';
			$where = " saleorder_id =".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $id,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'extra_price' =>    $data['extra_price'.$i],
						'cost_price'   =>    $data['cost_price_'.$i],
						'old_price'   =>    $data['oldprice_'.$i],
						'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
// 				print_r($data_item);exit();
			}
			$this->_name='tb_quoatation_termcondition';
			$where = " term_type=2 AND quoation_id = ".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity_term']);
			if(!empty($data['identity_term'])){
				foreach ($ids as $i)
				{
					$data_item= array(
							'quoation_id'=> $id,
							'condition_id'=> $data['termid_'.$i],
							"user_id"   => 	$GetUserId,
							"date"      => 	date("Y-m-d"),
							'term_type'=>2
	
					);
					$this->_name='tb_quoatation_termcondition';
					$this->insert($data_item);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getSaleorderItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
}