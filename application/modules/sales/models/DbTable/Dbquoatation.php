<?php

class Sales_Model_DbTable_Dbquoatation extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_quoatation"; 
	
	function getAllQuoatation($search){
			$db= $this->getAdapter();
			$sql=" SELECT id,
			(SELECT name FROM `tb_sublocation` WHERE id=tb_quoatation.branch_id LIMIT 1) AS branch_name,
			(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_quoatation.customer_id LIMIT 1 ) AS customer_name,
			(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=tb_quoatation.customer_id LIMIT 1 ) AS contact_name,	
			
			(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =tb_quoatation.saleagent_id  LIMIT 1 ) AS staff_name,
			quoat_number,date_order,
			(SELECT name_en FROM `tb_view` WHERE type=15 AND key_code=order_type LIMIT 1) as order_type,
			all_total,discount_value,net_total,
			(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1) as is_approved,
			(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1),
			(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod) AS user_name
			FROM `tb_quoatation` ";
			$order=" ORDER BY id DESC";
			
			$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " quoat_number LIKE '%{$s_search}%'";
				$s_where[] = " all_total LIKE '%{$s_search}%'";
				$s_where[] = " discount_value LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
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
	public function RejectQuotation($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			if($data['approved_name']==2){//reject sale update to quoatation
// 				$dbc=new Application_Model_DbTable_DbGlobal();
// 				$pending=1;
// 				$arr=array(
// 						'is_approved'	=> $data['approved_name'],
// 						'approved_userid'=> $GetUserId,
// 						'approval_note'	=> $data['app_remark'],
// 						'approved_date'	=> date("Y-m-d",strtotime($data['app_date'])),
// 						'pending_status'=>$pending,
// 				);
// 				$this->_name="tb_quoatation";
// 				$where = " id = ".$data["id"];
// 				$sale_id = $this->update($arr, $where);
			}else{//can sale quoation or sale;
				$arr = array(
						'is_cancel'=>1,
						'cancel_comment'=>$data['app_remark'],
						'cancel_date'=>date("Y-m-d",strtotime($data['app_date'])),
						'cancel_user'=>$GetUserId,
				);
				$where=" id=".$data['id'];
				$this->_name="tb_quoatation";
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
	public function addQuoatationOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$qoatationid = $db_global->getQuoationNumber($data["branch_id"]);
			$info=array(
					"customer_id"    => $data['customer_id'],
					'saleagent_id'   =>	$data['saleagent_id'],
					'order_type'   =>	$data['pre_order'],
					"branch_id"      => $data["branch_id"],
					"quoat_number"   => $qoatationid,
					"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
					
					"currency_id"    => 1,//$data['currency'],
					"remark"         => $data['remark'],
					"all_total"      => $data['totalAmoun'],
					"discount_value" => str_replace("%",'',$data['dis_value']),//check it $data['dis_value'],
					'discount_type'	 => $data['discount_type'],//check it
					"discount_real" => str_replace("%",'',$data['dis_value']),
					"net_total"      => $data['all_total'],
					"user_mod"       => $GetUserId,
					"date"      	 => date("Y-m-d"),
					'is_approved'=>0,
					'pending_status'=>2,
			);
			 $this->_name="tb_quoatation";
			$qoid = $this->insert($info); 
			unset($info);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'quoat_id'	  => 	$qoid,
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'  =>$data['qty_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
						'cost_price'   =>    $data['cost_price_'.$i],
						'extra_price'	=> $data['extra_price'.$i],
						'old_price' => 	$data['oldprice_'.$i],
						'disc_value' => str_replace("%",'',$data['dis_value'.$i]),
						'disc_type'	  =>    $data['discount_type'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_quoatation_item';
				$this->insert($data_item);
			 }
			 $ids=explode(',',$data['identity_term']);
			 if(!empty($data['identity_term'])){
				 foreach ($ids as $i)
				 {
				 	$data_item= array(
				 			'quoation_id'=> $qoid,
				 			'condition_id'=> $data['termid_'.$i],
				 			"user_id"   => 	$GetUserId,
				 			"date"      => 	date("Y-m-d"),
							'term_type'=>1
				 			
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
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	} 
	public function updateQoutation($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
// 			$qoatationid = $db_global->getQuoationNumber($data["branch_id"]);
				if(!empty($data['makeso'])){//if edit quote=>sale order
				
					$this->_name="tb_quoatation";
					$arr = array(
					'is_tosale'=>1);
					$where ="id = ".$id;
					$this->update($arr,$where);
				
					$so = $db_global->getSalesNumber($data["branch_id"]);
					$qdata=array(
							'quote_id'=>$id,
							"customer_id"    => $data['customer_id'],
							"branch_id"      => $data["branch_id"],
							"sale_no"       => 	$so,//$data['txt_order'],
							"date_sold"     => date("Y-m-d",strtotime($data['order_date'])),
							"saleagent_id"  => 	$data['saleagent_id'],
							"currency_id"    => 1,//$data['currency'],
							"remark"         => $data['remark'],
							"all_total"      => $data['totalAmoun'],
							"net_total"      => $data['all_total'],
							"user_mod"       => $GetUserId,
							"date"      	 => date("Y-m-d"),
							"status"      => $data['status'],
// 							'is_approved'=>0,
							'pending_status' =>2,
							"date"      => 	date("Y-m-d"),
							'discount_value'  =>    str_replace("%",'',$data['dis_value']),//check it
						    'discount_type'	  =>    $data['discount_type'],//check it
					);
					$this->_name="tb_sales_order";
					$sale_id = $this->insert($qdata);
					
					$this->_name='tb_salesorder_item';
					$ids=explode(',',$data['identity']);
					foreach ($ids as $i)
					{
						$data_item= array(
								'saleorder_id'=> $sale_id,
								'pro_id'	  => 	$data['item_id_'.$i],
								'qty_order'	  => 	$data['qty'.$i],
								'qty_detail'  => 	$data['qty_per_unit_'.$i],
								'price'		  => 	$data['price'.$i],
								'cost_price'   =>    $data['cost_price_'.$i],
								'old_price'   =>    $data['oldprice_'.$i],
								'disc_value'  => $data['dis_value'.$i],
								'sub_total'	  => $data['total'.$i],
								'disc_value'  =>    str_replace("%",'',$data['dis_value'.$i]),//check it
								'disc_type'	  =>    $data['discount_type'.$i],//check it
						);
						$this->insert($data_item);
					}
					///add term condtion of so
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
				}else{
						$qdata=array(
								"customer_id"    => $data['customer_id'],
								'saleagent_id'   =>	$data['saleagent_id'],
								"branch_id"      => $data["branch_id"],
								"date_order"     => date("Y-m-d",strtotime($data['order_date'])),
								"saleagent_id"   => $data['saleagent_id'],
								"currency_id"    => $data['currency'],
								"remark"         => $data['remark'],
								"all_total"      => $data['totalAmoun'],
								"discount_value" => $data['dis_value'],
								"net_total"      => $data['all_total'],
								"user_mod"       => $GetUserId,
								"status"      => $data['status'],
								"date"      	 => date("Y-m-d"),
								'is_approved'=>0,
								'is_cancel'=>0,
								'pending_status'=>2,
								"discount_value" => str_replace("%",'',$data['dis_value']),//check it $data['dis_value'],
								'discount_type'	 => $data['discount_type'],//check it
						);
					
					$this->_name="tb_quoatation";
					$where="id = ".$id;
					$this->update($qdata, $where);
// 					unset($info_purchase_order);
			        
					//delete detail
					$this->_name='tb_quoatation_item';
					$where = " quoat_id =".$id;
					$this->delete($where);
					
					$ids=explode(',',$data['identity']);
					$locationid=$data['branch_id'];
					foreach ($ids as $i)
					{
						$data_item= array(
								'quoat_id'	  => 	$id,
								'pro_id'	  => 	$data['item_id_'.$i],
								'qty_unit'  =>$data['qty_unit_'.$i],
								'qty_order'	  => 	$data['qty'.$i],
								'qty_detail'  => 	$data['qty_per_unit_'.$i],
								'price'		  => 	$data['price'.$i]+$data['extra_price'.$i],
								'extra_price'=> $data['extra_price'.$i],
								'cost_price'   =>    $data['cost_price_'.$i],
								'old_price' => 	$data['oldprice_'.$i],
								//'disc_value'	  => $data['real-value'.$i],
								'sub_total'	  => $data['total'.$i],
								'disc_value' => str_replace("%",'',$data['dis_value'.$i]),
						        'disc_type'	  =>    $data['discount_type'.$i],//check it
						);
						$this->insert($data_item);
					}	
		
					$this->_name='tb_quoatation_termcondition';
					$where = " term_type=1 AND quoation_id = ".$id;		
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
									'term_type'=>1
							);
							$this->insert($data_item);
						}
					}
		    }
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getQuotationItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM tb_quoatation WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getQuotationItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_item` WHERE quoat_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=1 ";
		return $db->fetchAll($sql);
	}
	
}