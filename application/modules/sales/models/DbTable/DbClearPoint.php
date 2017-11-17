<?php

class Sales_Model_DbTable_DbClearPoint extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_clearpoint";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function getCustomerCode($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
		
		$sql=" SELECT id FROM $this->_name AS s WHERE s.`branch_id`=$id ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = $prefix."CID";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	 
	
	function getAllCustomer($search){
		$db = $this->getAdapter();
		$sql=" SELECT id,cust_name,
				 contact_name,contact_phone,address,
				 (SELECT block_name FROM tb_zone WHERE tb_zone.id=zone_id LIMIT 1) AS zone,
				 (SELECT p.province_en_name FROM ln_province AS p WHERE p.province_id=tb_customer.province_id LIMIT 1)AS province,
				( SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code=tb_customer.status LIMIT 1) STATUS,
				( SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=tb_customer.user_id LIMIT 1) AS user_name
				 FROM `tb_customer` WHERE (cust_name!=''OR contact_name!=''  )";
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_search = str_replace(' ', '',$s_search);
			$s_where[] = "REPLACE(cust_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(phone,' ','')  		LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(contact_name,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(contact_phone,' ','')	LIKE '%{$s_search}%'";
			$s_where[] = "REPLACE(address,' ','')  		LIKE '%{$s_search}%'";
			
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " website LIKE '%{$s_search}%'";
			$s_where[] = " remark LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['customer_id']>0){
			$where .= " AND id = ".$search['customer_id'];
		}
		if($search['customer_type']>0){
			$where .= " AND cu_type = ".$search['customer_type'];
		}
		//$order=" ORDER BY id DESC ";
		$order=" ORDER BY id DESC";
		
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addClearPointr($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$info_purchase_order=array(
					"clearpont_type"=>$data['payment_method'],
					"customer_id"  	=>$data['customer_id'],
					"invoice_id"    =>$data['invoice_id'],
					"create_date"   =>date("Y-m-d"),
					"reamark"       =>$data['remark'],
					"total_point"   =>$data['all_total'],
					"clear_point"   =>$data['clear_point'],
					'balance_point' =>$data['balance_point'],
					"is_clearpoint" =>($data['balance_point']==0)?0:1,
					'user_id'       =>$GetUserId,
					"status"		=>1,
			);
			$this->_name="tb_clearpoint";
			$point_id = $this->insert($info_purchase_order);
			unset($info_purchase_order);
		 
			$ids=explode(',',$data['identity']);
			$paid = $data['clear_point'];
			$compelted = 0;
			foreach ($ids as $key => $i)
			{
				$paid = $paid -($data['total_pointafter'.$i]);
				$recipt_paid = 0;
				if($paid>=0){
					$paided = $data['total_pointafter'.$i];
					$balance=0;
					$compelted=0;
				}else{
					if($paid<0){
						$paided =$data['total_pointafter'.$i]-abs($paid);
						$balance= abs($paid);
						$compelted=1;
					}else{
						$paided =$paid;
						$balance= $data['total_pointafter'.$i];
						$compelted=1;
					}
				}
				$data_item= array(
						'clearpoint_id' => 	$point_id,
						'invoice_id' 	=> 	$data['invoice_no'.$i],
						'create_date' 	=> 	date("Y-m-d"),
						'sole_qty'      =>		$data['total_point'.$i],
						'sub_point'     =>		$data['total_pointafter'.$i],
						'status'      	=> 1,
						'user_id'  		=> $GetUserId,
				);
				$this->_name='tb_clearpoint_detail';
				$this->insert($data_item);
				$rsinvoice = $this->getBranchByInvoice($data['invoice_no'.$i]);
				if(!empty($rsinvoice)){
					$data_invoice = array(
							'total_pointafter'	=> $rsinvoice['total_pointafter']-$paided,
							'is_pointclear'	  	=> $compelted,
							'user_mod'        	=> $GetUserId,
					);
					$this->_name='tb_sales_order';
					$where = ' id= '.$data['invoice_no'.$i];
					$this->update($data_invoice, $where);
				}
				if($paid<=0){
					break;
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
	
	function getBranchByInvoice($inv_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_sales_order` AS s WHERE s.id = $inv_id LIMIT 1";
		return $db->fetchRow($sql);
	}
}