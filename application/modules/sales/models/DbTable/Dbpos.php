<?php

class Sales_Model_DbTable_Dbpos extends Zend_Db_Table_Abstract
{
	protected $_name="tb_invoice";
	function getAllProductName(){
		$sql="SELECT id,CONCAT(item_name) AS name FROM `tb_product` WHERE item_name!='' AND status=1 ";
		//$sql="SELECT id,CONCAT(item_name,' ',barcode) AS name FROM `tb_product` WHERE item_name!='' AND status=1 ";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getAllCustomerName(){
		$sql="SELECT id,CONCAT(cust_name,contact_name) AS name FROM `tb_customer` WHERE status=1 ";
		return $this->getAdapter()->fetchAll($sql);
	}
	function getProductById($product_id,$branch_id){
		$sql=" SELECT *,price as cost_price, 
			(SELECT qty FROM `tb_prolocation` WHERE pro_id=$product_id AND location_id=$branch_id LIMIT 1) AS qty,
			(SELECT price FROM `tb_product_price` WHERE type_id=1 AND pro_id=$product_id AND location_id=$branch_id LIMIT 1) as price,
			(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=measure_id) as measue_name
	 		FROM tb_product WHERE id=$product_id LIMIT 1"; 
		return $this->getAdapter()->fetchRow($sql);
	}
	function getProductByProductId($product_id,$location){
		$sql=" SELECT * FROM tb_prolocation WHERE pro_id = $product_id AND location_id = $location ";
		return $this->getAdapter()->fetchRow($sql);
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
			$so = $data['invoice'];
	
			$info_purchase_order=array(
					"sale_no"		=>$data['txt_order'],
					"saleagent_id"	=>$data['saleagent_id'],
					"remark"		=>$data['note'],
					"date_sold"		=>$data['order_date'],
					"payment_date"	=>$data['payment_date'],
					"saving_id"		=>$data['saving_id'],
					"status"		=>$data['status'],
					"customer_id"   	=> $data['customer_id'],
					"exchange_rate" 	=> $data['exchange_rate'],
					"net_total"     	=> $data['sub_total'],
					"discount_value" 	=> $data['discount'],
					"transport_fee"		=> $data["transport_fee"],
					"all_total"     	=> $data['total_dollar'],
					"all_total_riel"	=> $data['total_riel'],
					"paid_dollar"   	=> $data['receive_dollar'],
					"paid_riel"	    	=> $data["receive_riel"],
					"paid"	        	=> $data["total_paid"],
					'return_dollar'	 	=> $data["return_amount"],
					'return_riel'   	=> $data["return_amountriel"],
					"balance"      		=> $data['balance'],
					'is_completed'		=> ($data['balance']==0)?1:0,
					"user_mod"     	=>$GetUserId,
					"date"     		=> date("Y-m-d"),
					"branch_id"     => 1,
					"tax"			=> $data["tax"],
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order);
			
			$data['receipt'] = $db_global->getReceiptNumber(1);
			
			$info_purchase_order=array(
					"branch_id"   	=> 	1,//$branch_id['branch_id'],
					"customer_id"   => 	$data["customer_id"],
					"payment_type"  => 	1,//payment by customer/invoice
					"payment_id"    => 	1,	//payment by cash/paypal/cheque
					"receipt_no"    => 	$data['receipt'],
					"receipt_date"  =>  date("Y-m-d"),
					"date_input"    =>  date("Y-m-d"),
					"total"         => 	$data['total_dollar'],
					"paid"          => 	$data["total_paid"],
					"paid_dollar"   => 	$data['receive_dollar'],
					"paid_riel"     => 	$data['receive_riel'],
					"balance"       => 	$data['balance'],
// 					"remark"        => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status'        =>1,
					"bank_name"     => 	'',
					"cheque_number" => 	'',
					"exchange_rate" => 	$data['exchange_rate'],
			);
			$this->_name="tb_receipt";
			$reciept_id = $this->insert($info_purchase_order);
			
			$data_item= array(
					'receipt_id'  => $reciept_id,
					'invoice_id'  => $sale_id,
					'total'		  => $data['total_dollar'],
					'paid'	      => $data["total_paid"],
					'balance'	  => $data['balance'],
					'is_completed'=> ($data['balance']==0)?1:0,
					'status'      => 1,
					'date_input'  => date("Y-m-d"),
			);
			$this->_name='tb_receipt_detail';
			$this->insert($data_item);
	
			$ids=explode(',',$data['identity']);
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $sale_id,
						'pro_id'	  => $data['product_id'.$i],
						'qty_unit'	  => $data['qty_'.$i],
						'qty_detail'  => $data['qtydetail_'.$i],
						'qty_order'	  => $data['qty_sold'.$i],
						'point'	  	  => $data['qty_'.$i],
						'point_after' => $data['qty_'.$i],
						'price'		  => $data['price_'.$i],
						'old_price'   => $data['price_'.$i],
 						'cost_price'  => $data['cost_price'.$i],
						'disc_value'  => $data['discount_'.$i],
// 						'disc_value'  => str_replace("%",'',$data['dis_value'.$i]),//check it
// 						'disc_type'	  => $data['discount_type'.$i],//check it
						'sub_total'	  => $data['sub_total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
			}

			$ids=explode(',',$data['identity_term']);
			if(!empty($data['identity_term'])){
				foreach ($ids as $i)
				{
					$data_item= array(
							'quoation_id'=> $sale_id,
							'condition_id'=> $data['term_id'.$i],
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
	function getInvoiceById($id){
		$sql=" SELECT s.*,
				(net_total+transport_fee) AS net_total,
			(SELECT CONCAT(cust_name,' ',contact_name) FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
			(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,	
			(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS address,	
			(SELECT contact_phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_phone,	
			(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id =s.user_mod LIMIT 1) AS user_name
		FROM tb_sales_order AS s WHERE s.id= ".$id;
		return $this->getAdapter()->fetchRow($sql);
	}
	function getInvoiceDetailById($id){
		$sql=" SELECT si.*,
			(SELECT item_name FROM `tb_product` WHERE id=si.pro_id) As pro_name
		FROM tb_salesorder_item as si WHERE si.saleorder_id= ".$id;
		return $this->getAdapter()->fetchAll($sql);
	}
	function deleteSale($sale_id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		$rsdetail = $this->getInvoiceDetailById($sale_id);
			if(!empty($rsdetail)){
				foreach($rsdetail as $row){
					$rs = $this->getProductByProductId($row['pro_id'], 1);
					if(!empty($rs)){
						$this->_name='tb_prolocation';
						$arr = array(
								'qty'=>$rs['qty']+$row['qty_order']
						);
						$where=" id =".$rs['id'];
						$this->update($arr, $where);
					}
				}
			}
		
			$this->_name='tb_sales_order';
			$where=" id = ".$sale_id;
			$this->delete($where);
			
			$rsreceipt = $this->getReceiptDetailbysaleid($sale_id);
			if(!empty($rsreceipt)){
				$this->_name='tb_receipt';
				$where=" id =".$rsreceipt['receipt_id'];
				$this->delete($where);
			}
			$this->_name='tb_receipt_detail';
			$where=" invoice_id=".$sale_id;
			$this->delete($where);
			
			$this->_name='tb_salesorder_item';
			$where=" saleorder_id = ".$sale_id;
			$this->delete($where);
			$db->commit();
		
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	function getReceiptDetailbysaleid($sale_id){
// 		$data_item= array(
// 				'receipt_id'  => $reciept_id,
// 				'invoice_id'  => $sale_id,
// 				'total'		  => $data['total_dollar'],
// 				'paid'	      => $data["total_paid"],
// 				'balance'	  => $data['balance'],
// 				'is_completed'=> ($data['balance']==0)?1:0,
// 				'status'      => 1,
// 				'date_input'  => date("Y-m-d"),
// 		);
// 		$this->_name='tb_receipt_detail';
// 		$this->insert($data_item);
		
		$sql=" SELECT  receipt_id,invoice_id FROM tb_receipt_detail WHERE invoice_id = $sale_id LIMIT 1 ";
		return $this->getAdapter()->fetchRow($sql);				
	}
	function editSale($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$sale_id= $data['sale_id'];
			$rsdetail = $this->getInvoiceDetailById($sale_id);
			if(!empty($rsdetail)){
				foreach($rsdetail as $row){
					$rs = $this->getProductByProductId($row['pro_id'], 1);
					if(!empty($rs)){
						$this->_name='tb_prolocation';
						$arr = array(
								'qty'=>$rs['qty']+$row['qty_order']
						);
						$where=" id =".$rs['id'];
						$this->update($arr, $where);
					}
				}
			}
	
			$this->_name='tb_sales_order';
			$where=" id = ".$sale_id;
			$this->delete($where);
				
			$rsreceipt = $this->getReceiptDetailbysaleid($sale_id);
			if(!empty($rsreceipt)){
				$this->_name='tb_receipt';
				$where=" id =".$rsreceipt['receipt_id'];
				$this->delete($where);
			}
			$this->_name='tb_receipt_detail';
			$where=" invoice_id=".$sale_id;
			$this->delete($where);
				
			$this->_name='tb_salesorder_item';
			$where=" saleorder_id = ".$sale_id;
			$this->delete($where);
			
			$this->addSaleOrder($data);
			
			$db->commit();
	
		}catch(Exception $e){
			$db->rollBack();
		}
	}
	
	function getSaleByeId($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM tb_sales_order WHERE id=".$id;
		return $db->fetchRow($sql);
	}
	
	function getSaleDetailByeId($id){
		$db=$this->getAdapter();
		$sql="SELECT sd.id,
		       (SELECT p.item_name FROM tb_product AS p WHERE p.id=sd.pro_id LIMIT 1) AS pro_name,
		       sd.qty_unit,sd.qty_detail,sd.qty_order,sd.price,sd.disc_type,sd.sub_total
		       FROM tb_salesorder_item AS sd
		       WHERE sd.saleorder_id=".$id;
		return $db->fetchRow($sql);
	}
}