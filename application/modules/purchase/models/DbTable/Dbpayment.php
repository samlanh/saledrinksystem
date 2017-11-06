<?php

class Purchase_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	
	function getPurchaseExist($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`net_totalafter`,
				  p.`balance_after`,
				  p.`paid` ,
				  p.`paid_after`
				FROM
				  `tb_purchase_order` AS p 
				WHERE p.id =$id ";
		
		return $db->fetchRow($sql);
	}
	function getVendorPaymentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  v.`id`,
					v.`branch_id`,
				  v.`payment_type`,
				  v.`payment_id`,
				  v.`receipt_no`,
				  v.`cheque_number`,
				  v.`che_issuedate`,
				  v.`che_withdrawaldate`,
				  v.`expense_date`,
				  v.`bank_name`,
				  v.`vendor_id`,
				  v.`withdraw_name`,
				  v.`remark`,
				  v.`total`,
				  v.`paid`,
				  v.`balance`,
				  v.`subtotal_after`,
				  v.`paid_after`,
				  v.`balance_after`,
				  vd.`invoice_id`,
  (SELECT p.`order_number` FROM `tb_purchase_order` AS p WHERE p.id=vd.`invoice_id` LIMIT 1) AS invoice_no,
  (SELECT p.`net_total` FROM `tb_purchase_order` AS p WHERE p.id=vd.`invoice_id` LIMIT 1) AS net_total,
  (SELECT p.`paid` FROM `tb_purchase_order` AS p WHERE p.id=vd.`invoice_id` LIMIT 1) AS paided,
  (SELECT p.`balance_after` FROM `tb_purchase_order` AS p WHERE p.id=vd.`invoice_id` LIMIT 1) AS balance_after,
				  vd.`receipt_id`,
				  vd.`total`,
				  vd.`paid`,
				  vd.`balance`,
				  vd.`discount`,
				  vd.`date_input` 
				FROM
				  `tb_vendor_payment` AS v,
				  `tb_vendorpayment_detail` AS vd 
				WHERE v.id = vd.`receipt_id` AND v.id=$id";
		
		return $db->fetchAll($sql);
	}
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT r.id,
			(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
			(SELECT v.v_name FROM `tb_vendor` AS v WHERE v.vendor_id=r.vendor_id LIMIT 1 ) AS customer_name,
			r.`date_input`,
			r.`total`,r.`paid`,r.`balance`,
			(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=r.`payment_id`) AS payment_name,
			cheque_number,bank_name,withdraw_name,che_issuedate,che_withdrawaldate,
			(SELECT name_en FROM `tb_view` WHERE TYPE=10 AND key_code=r.`payment_type` LIMIT 1 ) payment_by,
			(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name 
			FROM `tb_vendor_payment` AS r ";
			
			$from_date =(empty($search['start_date']))? '1': " r.`expense_date` >= '".$search['start_date']." 00:00:00'";
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
			if($search['customer_id']>0){
				$where .= " AND r.vendor_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addReceiptPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			
			foreach ($ids as $row){
				
				$branch_id = $this->getBranchByInvoice($data['invoice_no'.$row]);
				break;
			}
			//$data['receipt'] = $db_global->getReceiptNumber($branch_id['branch_id']);
			
			$info_purchase_order=array(
					"branch_id"   	=> 	$branch_id['branch_id'],
					"vendor_id"   => 	$data["customer_id"],
					"payment_type"  => 	$data["payment_method"],//payment by customer/invoice
					"payment_id"    => 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"    => 	'',//$data['receipt'],
					"date_input"  =>  date("Y-m-d"),
					"che_issuedate"  =>  date("Y-m-d",strtotime($data['cheque_issuedate'])),
					"che_withdrawaldate"  =>  date("Y-m-d",strtotime($data['cheque_withdrawdate'])),
					"expense_date"  =>  date("Y-m-d",strtotime($data['expense_date'])),
					"total"         => 	$data['all_total'],
					"paid"          => 	$data['paid'],
					"balance"       => 	$data['balance'],
					"remark"        => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status'        =>1,
					"bank_name" => 	$data['bank_name'],
					"cheque_number" => 	$data['cheque'],
					"withdraw_name" => 	$data['holder_name'],
				 // "paid_dollar"   => 	$data['paid_dollar'],
// 				    "paid_riel"     => 	$data['paid_riel'],
				
			);
			$this->_name="tb_vendor_payment";
			$reciept_id = $this->insert($info_purchase_order); 
			
			unset($info_purchase_order);
// 			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			$compelted = 0;
			foreach ($ids as $key => $i)
			{
				$paid = $paid -($data['balance_after'.$i]);
				$recipt_paid = 0;
				if ($paid>=0){
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
				}else{
					$paided = $data['paid'];
					$balance= $data['balance_after'.$i]-$data['paid'];
					$compelted=0;
				}
				$data_item= array(
						'receipt_id'=> $reciept_id,
						'invoice_id'=> 	$data['invoice_no'.$i],
						'total'=>$data['balance_after'.$i],
// 						'discount'  => 	$data['discount'.$i],
						'paid'	  => 	$paided,
						'balance'		  => 	$balance,
						'is_completed'   =>    $compelted,
						'status'  => 1,
						'date_input'	  => date("Y-m-d"),
				);
				$this->_name='tb_vendorpayment_detail';
				$this->insert($data_item);
				
				$rsinvoice = $this->getBranchByInvoice($data['invoice_no'.$row]);
				if(!empty($rsinvoice)){
					$data_invoice = array(
								'paid'=>$rsinvoice['paid']+$paided,
								'discount_after'  => 	0,
								'paid_after'	  => 	$paided,
								'balance_after'	  => 	$balance,
								'net_totalafter' =>    $balance,
								'is_completed'	  => 	$compelted,
								);
					$this->_name='tb_purchase_order';
					$where = 'id = '.$data['invoice_no'.$i];
					$this->update($data_invoice, $where);
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
	public function updatePayment($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id = $data["id"];
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			$compelted = 0;
			foreach ($ids as $row){
				
				$branch_id = $this->getBranchByInvoice($data['invoice_no'.$row]);
				break;
			}
			
			$info_purchase_order=array(
					"branch_id"   	=> 	$branch_id['branch_id'],
					"vendor_id"   => 	$data["customer_id"],
					"payment_type"  => 	$data["payment_method"],//payment by customer/invoice
					"payment_id"    => 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"    => 	'',//$data['receipt'],
					"date_input"  =>  date("Y-m-d"),
					"che_issuedate"  =>  date("Y-m-d",strtotime($data['cheque_issuedate'])),
					"che_withdrawaldate"  =>  date("Y-m-d",strtotime($data['cheque_withdrawdate'])),
					"expense_date"  =>  date("Y-m-d",strtotime($data['expense_date'])),
					"total"         => 	$data['all_total'],
					"paid"          => 	$data['paid'],
					"balance"       => 	$data['balance'],
					"remark"        => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status'        =>1,
					"bank_name" => 	$data['bank_name'],
					"cheque_number" => 	$data['cheque'],
					"withdraw_name" => 	$data['holder_name'],
				 // "paid_dollar"   => 	$data['paid_dollar'],
// 				    "paid_riel"     => 	$data['paid_riel'],
				
			);
			$this->_name="tb_vendor_payment";
			$where = "id=".$data['id'];
			
			$db->getProfiler()->setEnabled(true);
			$reciept_id = $this->update($info_purchase_order,$where); 
			Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
			
			
			
			foreach ($ids as $key => $i)
			{
				$old_purchase_amount = $this->getPurchaseExist($data['invoice_no'.$i]);
				if($old_purchase_amount){
					$arr_old_po = array(
						'net_totalafter'		=>	$old_purchase_amount["net_totalafter"]+$data['old_paid_'.$i],
						'balance_after'			=>	$old_purchase_amount["balance_after"]+$data['old_paid_'.$i],
						'paid'					=>	$old_purchase_amount["paid"]-$data['old_paid_'.$i],
					);
				}
				$this->_name = "tb_purchase_order";
				$where = "id=".$data['invoice_no'.$i];
				
				$db->getProfiler()->setEnabled(true);
				$this->update($arr_old_po,$where);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				$sql = "DELETE FROM tb_vendorpayment_detail WHERE receipt_id=".$data['id'];
				$db->getProfiler()->setEnabled(true);
				$db->query($sql);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				
				$paid = $paid -($data['balance_after'.$i]);
				$recipt_paid = 0;
				if ($paid>=0){
					$paided = $data['balance_after'.$i];
					$balance=0;
					$compelted=1;
				}else{
					$paided = $data['paid'];
					$balance= $data['balance_after'.$i]-$data['paid'];
					$compelted=0;
				}
				$data_item= array(
						'receipt_id'=> $data['id'],
						'invoice_id'=> 	$data['invoice_no'.$i],
						'total'=>$data['balance_after'.$i],
// 						'discount'  => 	$data['discount'.$i],
						'paid'	  => 	$paided,
						'balance'		  => 	$balance,
						'is_completed'   =>    $compelted,
						'status'  => 1,
						'date_input'	  => date("Y-m-d"),
				);
				$this->_name='tb_vendorpayment_detail';
				
				$db->getProfiler()->setEnabled(true);
				$this->insert($data_item);
				Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);
				
				$rsinvoice = $this->getBranchByInvoice($data['invoice_no'.$i]);
				if(!empty($rsinvoice)){
					$data_invoice = array(
								'paid'=>$rsinvoice['paid']+$paided,
								'discount_after'  => 	0,
								'paid_after'	  => 	$paided,
								'balance_after'	  => 	$balance,
								'net_totalafter' =>    $balance,
								'is_completed'	  => 	$compelted,
								);
					$this->_name='tb_purchase_order';
					$where = 'id = '.$data['invoice_no'.$i];
					
					$db->getProfiler()->setEnabled(true);
					$this->update($data_invoice, $where);
					Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
$db->getProfiler()->setEnabled(false);

				}
				
// 				if ($key== ($count-1)){
// 						if ($paid>0){
// 							$idss= explode(',',$data['identity']);
// 							foreach ($idss as $k)
// 							{
// 								$paid = $paid - $data['balance_after'.$k];
// 								if ($paid>=0){
// 									$paided = 0;
// 									$recipt_paid =$data['balance_after'.$k]+$data['paid_amount'.$k];
// 								}else{
// 									$paided = abs($paid);
// 									$recipt_paid = $data['paid_amount'.$k]+($data['balance_after'.$k] - $paided);
// 									$paid=0;
// 								}
// 								$data_item= array(
// 										'paid'	  => 	$recipt_paid,
// 										'balance'		  => 	$paided,
// 										'is_completed'   =>    1,
// 										'status'  => 1,
// 								);
// 								$this->_name='tb_receipt_detail';
// 								$wheres = 'invoice_id = '.$data['invoice_no'.$k];
// 								$this->update($data_item, $wheres);
								
// 								$data_invoice = array(
// 										'balance_after'	  => 	$paided,
// 										'is_fullpaid'	  => 	($balance>0)?0:1,
// 								);
// 								$this->_name='tb_invoice';
// 								$where = 'id = '.$data['invoice_no'.$k];
// 								$this->update($data_invoice, $where);
// 							}
// 						}
// 				}
				
			 }
				
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getBranchByInvoice($invoice_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_purchase_order` AS p WHERE p.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT d.`id`,d.`receipt_id`,d.`invoice_id`,
		( SELECT i.invoice_no FROM `tb_invoice` AS i  WHERE i.id = d.`invoice_id` ) AS invoice_no,
		d.`total`,d.`paid`,d.`balance`,d.`discount`,d.`date_input`
		FROM `tb_receipt_detail` AS d WHERE d.`receipt_id` =".$reciept_id;
		return $db->fetchAll($sql);
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