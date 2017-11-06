<?php

class Purchase_Model_DbTable_DbPurchaseOrder extends Zend_Db_Table_Abstract
{	
	function getAllPurchaseOrder($search){//new
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,date_order,date_in,
		invoice_no,
		net_total,paid,(net_total-paid) AS balance,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1) As purchase_status,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=5 LIMIT 1),
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
		FROM `tb_purchase_order` ";
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
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
		if($search['purchase_status']>0){
			$where .= " AND purchase_status =".$search['purchase_status'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
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
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductCostAndQty($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.id,p.`price`,sum(pl.`qty`) as qty 
		FROM `tb_product` AS p ,`tb_prolocation` AS pl 
			WHERE 
			p.id=pl.`pro_id` AND p.id=$pro_id ";
		return $db->fetchRow($sql);
	}
	public function addPurchaseOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$idrecord=$data['v_name'];
			$date= new Zend_Date();
			$number_transaction = $date->get('hh-mm-ss');
			if($data['txt_order']==""){
				$PO = "PO";//$po["key_value"];
				$order_add=$PO.$number_transaction;
			}
			else{
				$order_add=$data['txt_order'];
			}
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"branch_id"      => 	$data["LocationId"],
					"order_number"   => 	$order_add,
					"date_order"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"date_in"     	 => 	date("Y-m-d",strtotime($data['date_in'])),
					"purchase_status"=> 	$data['status'],
					//"payment_method" => $data['payment_name'],
					//"currency_id"    => $data['currency'],
					//"discount_type"	 => $data['discount_type'],
					//"payment_number"        => 	$data['payment_number'],
					//'date_issuecheque'=>date("Y-m-d",strtotime($data['date_issuecheque'])),
					"remark"         => $data['remark'],
					"all_total"      => $data['totalAmoun'],
					"discount_value" => $data['dis_value'],
					'discount_after' => $data['dis_value'],
					"discount_real"  => $data['global_disc'],
					"net_total"      => $data['all_totalpayment'],
					"net_totalafter" => $data['all_totalpayment'],
					"paid"           => $data['paid'],
					"paid_after"     => $data['paid'],
					"balance"        => $data['remain'],
					"balance_after"  => $data['remain'],
					'invoice_no' 	 => $data['invoice_no'],
					"user_mod"       => $GetUserId,
					"date"      	 => new Zend_Date(),
					'commission'	 => $data['commission'],
					'commission_ensur'=>$data['commission_ensur'],
					'bank_name'		 => $data['bank_name'],
					'is_completed'	 => ($data['remain']==0)?1:0,
			);
			$this->_name="tb_purchase_order";
			$purchase_id = $this->insert($info_purchase_order);
			unset($info_purchase_order);
			
			$info_purchase_order=array(
					"branch_id"   	=> $data["LocationId"],
					"vendor_id"     => $data["v_name"],
					"payment_type"  => 1,//payment by​ invoice
					"payment_id"    => 1,	//payment by cash/paypal/cheque
					"receipt_no"    => '',//$data['receipt'],
					"date_input"    => date("Y-m-d"),
					"che_issuedate" => date("Y-m-d",strtotime($data['date_in'])),
					"che_withdrawaldate" =>  date("Y-m-d",strtotime($data['date_in'])),
					"expense_date"  => date("Y-m-d",strtotime($data['date_in'])),
					"total"         => $data['all_totalpayment'],
					"paid"          => $data['paid'],
					"balance"       => $data['remain'],
					"remark"        => $data['remark'],
					"user_id"       => $GetUserId,
					'status'        => 1,
					"bank_name" 	=> '',
					"cheque_number" => '',
					"withdraw_name" => '',
			);
			$this->_name="tb_vendor_payment";
			$reciept_id = $this->insert($info_purchase_order);
			
			$data_item= array(
					'receipt_id'	=> $reciept_id,
					'invoice_id'	=> $purchase_id,
					'total'			=> $data['all_totalpayment'],
					'paid'	  		=> $data['paid'],
					'balance'		=> $data['remain'],
					'is_completed'  => ($data['remain']==0)?1:0,
					'status'  		=> 1,
					'date_input'	=> date("Y-m-d"),
			);
			$this->_name='tb_vendorpayment_detail';
			$this->insert($data_item);
			
			if($data["status"]==5 OR $data["status"]==4){
				$RO = "R";//$ro["key_value"];
				$date= new Zend_Date();
				$recieve_no=$RO.$number_transaction;
				$orderdata = array(
						//"tax"=>$data["total_tax"],
						'purchase_id'	 =>$purchase_id,
						"vendor_id"      => $data['v_name'],
						"LocationId"     => $data["LocationId"],
						"recieve_number" => $recieve_no,
						"date_order"     => $data['order_date'],
						"date_in"     	 => $data['date_in'],
						"purchase_status"=> $data['status'],
						//"payment_method" => $data['payment_name'],
						//"currency_id"    => $data['currency'],
						"remark"         => $data['remark'],
						"all_total"      => $data['totalAmoun'],
						"discount_value" => $data['dis_value'],
						"discount_real"  => $data['global_disc'],
						"net_total"      => $data['all_totalpayment'],
						"paid"           => $data['paid'],
						"balance"        => $data['remain'],
						"user_mod"       => $GetUserId,
						"date"      	 => new Zend_Date(),
				);
				$this->_name='tb_recieve_order';
				$recieved_order = $this->insert($orderdata);
				unset($orderdata);
			}
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			foreach ($ids as $i)
			{
				$rsproduct = $this->getProductCostAndQty($data['item_id_'.$i]);
				$cost_avg = (($rsproduct['qty']*$rsproduct['price'])+($data['price'.$i]*$data['qty'.$i])) / ($rsproduct['qty']+$data['qty'.$i]);
				$array=array(
						'price'=>$cost_avg
				);
				$this->_name="tb_product";
				$where = " id= ".$rsproduct['id'];
				$this->update($array, $where);
				
				$data_item= array(
						'purchase_id' => 	$purchase_id,
						'pro_id'	  => 	$data['item_id_'.$i],
                        'qty_unit' 	  => 	$data['qty_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'price'		  => 	$data['price'.$i],
						'disc_value'	  => $data['real-value'.$i],
						'sub_total'	  => $data['total'.$i],
						//'remark'	  => $data['remark_'.$i]
						//'total_befor' => 	$data['total'.$i],
				);
				$this->_name='tb_purchase_order_item';
				$this->insert($data_item);
	
				if($data["status"]==5 OR $data["status"]==4){
					$recieved_item = array(
							'recieve_id'	  => 	$recieved_order,
							'pro_id'	  => 	$data['item_id_'.$i],
							'qty_order'	  => 	$data['qty'.$i],
							'qty_unit' => 	$data['qty_unit_'.$i],
							'qty_detail'  => 	$data['qty_per_unit_'.$i],
							'qty_receive'  => 	$data['qty'.$i],
							'price'		  => 	$data['price'.$i],
							'disc_value'	  => $data['real-value'.$i],
							'sub_total'	  => $data['total'.$i],
					);
					$db->insert("tb_recieve_order_item", $recieved_item);

					unset($recieved_item);
					$rows=$db_global ->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						if($data["status"]==4 OR $data["status"]==5){
							$datatostock   = array(
								'qty'   		=> 		$rows["qty"]+$data['qty'.$i],
								'last_mod_date'		=>	date("Y-m-d"),
								'last_mod_userid'=>$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						}
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
	public function updatePurchaseOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$row_oldhistory = $this->getPurchaseById($data['id']);// get prev.item
			if($row_oldhistory['status']==1 AND $row_oldhistory['purchase_status']==5){//usedi and paid
				$po_item = $this->getPurchaseDetailById($data['id']);//get old item detail
				if(!empty($po_item)){
					foreach ($po_item as $rsitem){
						$rows=$db_global->productLocationInventory($rsitem['pro_id'], $row_oldhistory['branch_id']);//check stock product location
						if($rows)
						{
							$datatostock   = array(
									'qty'   		=> $rows["qty"]-$rsitem['qty_order'],
									'last_mod_date'	=> date("Y-m-d"),
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						}
					}
				}
			}elseif($row_oldhistory['status']==1 AND $row_oldhistory['purchase_status']==2){//activ and open
			
			}else{
	
			}
				
			$this->_name='tb_purchase_order_item';
			$where =" purchase_id=".$data['id'];
			$this->delete($where);
				
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
	
			$idrecord=$data['v_name'];
			$order_add=$data['txt_order'];
			$info_purchase_order=array(
					"vendor_id"      => 	$data['v_name'],
					"branch_id"      => 	$data["LocationId"],
					"order_number"   => 	$order_add,
					"date_order"     => 	date("Y-m-d",strtotime($data['order_date'])),
					"date_in"     	 => 	date("Y-m-d",strtotime($data['date_in'])),
					"purchase_status"=> 	$data['status'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"invoice_no"	 =>     $data['invoice_no'],
					"discount_value" => 	$data['dis_value'],
					'discount_after' => 	$data['dis_value'],
					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_totalpayment'],//$data['all_totalpayment'] which 1 choose .
					"net_totalafter" =>     $data['all_totalpayment'],
					"paid"           => 	$data['paid'],
					"paid_after"     => 	$data['paid'],
					"balance"        => 	$data['remain'],
					"balance_after"  => 	$data['remain'],
					"user_mod"       => 	$GetUserId,
					"date"      => 	new Zend_Date(),
					'invoice_no' => 	$data['invoice_no'],
					//'date_issuecheque'=>date("Y-m-d",strtotime($data['date_issuecheque'])),
					//"payment_method" =>     $data['payment_name'],
					//"payment_number" =>     $data["payment_number"],
					//"currency_id"    =>     $data['currency'],
					'status'	    => $data['status_use'],
					'commission'    => 	$data['commission'],
					'commission_ensur'=> 	$data['commission_ensur'],
					'bank_name'=> 	$data['bank_name'],
			);
			
			$this->_name="tb_purchase_order";
			$where=" id=".$data['id'];
			$purchase_id = $this->update($info_purchase_order, $where);
			$purchase_id=$data['id'];
			unset($info_purchase_order);
				
			$ids=explode(',',$data['identity']);
			$locationid=$data['LocationId'];
			if(!empty($data['identity']))foreach ($ids as $i)
			{
				$data_item= array(
						'purchase_id' => $purchase_id,
						'pro_id'	  => $data['item_id_'.$i],
						'qty_order'	  => $data['qty'.$i],
						'qty_unit'    => $data['qty_unit_'.$i],
						'qty_detail'  => $data['qty_per_unit_'.$i],
						'price'		  => $data['price'.$i],
						'disc_value'  => $data['real-value'.$i],
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_purchase_order_item';
				$this->insert($data_item);
	
				if(($data["status"]==5 OR $data["status"]==4) AND $data["status_use"]==1 ){
					$rows=$db_global->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						if($data["status"]==4 OR $data["status"]==5){
							$arr  = array(
									'qty'   		  =>$rows["qty"]+$data['qty'.$i],
									'last_mod_date'	  =>date("Y-m-d"),
									'last_mod_userid' =>$GetUserId,
									'user_id'=>$GetUserId,
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($arr, $where);
						}
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
	public function getPurchaseID($id){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(p.item_name,'(',p.item_code,' )') AS item_name , p.qty_perunit,od.order_id, od.pro_id, od.qty_order,
		od.price, od.total_befor, od.disc_type,	od.disc_value, od.sub_total, od.remark 
		FROM tb_purchase_order_item AS od
		INNER JOIN tb_product AS p ON p.pro_id=od.pro_id WHERE od.order_id=".$id;
		$row = $db->fetchAll($sql);
		return $row;
	}
	
	public function getPurchaseById($id){//just new 
		$db=$this->getAdapter();
		$sql = "SELECT p.*,(SELECT title FROM `tb_income_expense` WHERE po_id=p.`id` LIMIT 1)  AS po_x FROM `tb_purchase_order` AS p WHERE id=$id LIMIT 1 ";
		$rows=$db->fetchRow($sql);
		return $rows;
	}
	public function getPurchaseDetailById($id){//just new
		$db=$this->getAdapter();
		$sql = "SELECT * FROM `tb_purchase_order_item` WHERE purchase_id=$id ";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	public function recieved_info($order_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_recieve_order WHERE order_id=".$order_id." LIMIT 1";		
		$row =$db->fetchRow($sql);
		return $row;
	}
	//for get left order address change form showsaleorder to below
	public function showPurchaseOrder(){
		$db= $this->getAdapter();
		$sql = "SELECT p.order_id, p.order, p.date_order, p.status, v.v_name, p.all_total,p.paid,p.balance
		FROM tb_purchase_order AS p  INNER JOIN tb_vendor AS v ON v.vendor_id=p.vendor_id";
		$row=$db->fetchAll($sql);
		return $row;
		
	}
	public function getVendorInfo($post){
		$db=$this->getAdapter();
		$sql="SELECT contact_name,phone, add_name AS address 
		FROM tb_vendor WHERE vendor_id = ".$post['vendor_id']." LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
}