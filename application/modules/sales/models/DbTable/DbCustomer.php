<?php

class Sales_Model_DbTable_DbCustomer extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_customer";
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
	function updatecustomerId(){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `tb_customer` ";
		$row = $db->fetchAll($sql);
		foreach($row as $rs){
			$acc_no = $rs['id'];
			$new_acc_no= (int)$acc_no;
			$acc_no= strlen((int)$acc_no+1);
			$pre = "CID";
			for($i = $acc_no;$i<5;$i++){
				$pre.='0';
			}
			$where = " id = ".$rs['id'];
			
			$arr = array(
					'cu_code'=>$pre.$new_acc_no
					);

			$this->update($arr, $where);
		}
	}
	
	function getAllCustomer($search){
		$db = $this->getAdapter();
		$sql=" SELECT id,cust_name,(SELECT name_en FROM `tb_view` WHERE type=6 AND key_code=cu_type LIMIT 1) customer_type,
		 contact_name,contact_phone,address,
		 credit_team,credit_limit,
		( SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=tb_customer.status LIMIT 1) status,
		( SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=tb_customer.user_id LIMIT 1) AS user_name
		 FROM `tb_customer` WHERE cust_name!='' ";
		
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
	
	public function addCustomer($post)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$data=array(
 				'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
				'province_id'=> $post['province'],
				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
				'website'		=> $post['txt_website'],//test
				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
		);
		
		$this->insert($data);
	}
	public function updateCustomer($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db = $this->getAdapter();
		$data=array(
				//'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
				'province_id'=> $post['province'],
				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
				'website'		=> $post['txt_website'],//test
				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
				'status'	=>	$post["status"],
		);
		$where=$this->getAdapter()->quoteInto('id=?',$post["id"]);
		$this->_name="tb_customer";
		$this->update($data,$where);
	}
	//for add new customer from sales
	final function addNewCustomer($post){
		$rsterm = $this->getCustomerLimit($post['customer_type']);
		$credit_limit=0;
		$credit_term=0;
		if(!empty($rsterm)){
			$credit_limit = $rsterm['credit_limit'];
			$credit_term = $rsterm['credit_term'];
		}
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
			$data=array(
 				'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
// 				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
// 				'website'		=> $post['txt_website'],//test
// 				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$credit_limit,
				'credit_team'	=>	$credit_term,
		);
// 		$result=$db->addRecord($data, "tb_customer");

		return $this->insert($data);;	
	}
	
	function getCustomerType($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,block_name,remark,`status`,
				(SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=user_id LIMIT 1) AS user_name
				FROM tb_zone WHERE `status`=1";
    	$where = '';
//     	if($data["adv_search"]!=""){
//     		$s_where=array();
//     		$s_search = addslashes(trim($data['adv_search']));
//     		$s_where[]= " v.`name_en` LIKE '%{$s_search}%'";
//     		$s_where[]=" v.`key_code` LIKE '%{$s_search}%'";
//     		//$s_where[]= " cate LIKE '%{$s_search}%'";
//     		$where.=' AND ('.implode(' OR ', $s_where).')';
//     	}
//     	if($data["status_search"]!=""){
//     		$where.=' AND v.status='.$data["status_search"];
//     	}
    	//echo $sql.$where;
		$where.=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where);
    }
	
	function getCustomerTypeId($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_zone WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    
    function addCustomerType($data){
    	$db = $this->getAdapter();
    	$db_other = new Product_Model_DbTable_DbOther();
    	$key_code = $db_other->getLastKeycodeByType(6);
		//echo $key_code;exit();
    	$arr = array(
				'name_en'			=>	$data["title_en"],
    			'key_code'			=>	$key_code,
    			'type'				=>	6,
    			'status'			=>	$data["status"],
				'credit_limit'		=>	$data["credit_limit"],
    			'credit_term'		=>	$data["credit_term"],
    	);
    	$this->_name = "tb_view";
    	$this->insert($arr);
    }
    function editCustomerType($data){
    	$session_user=new Zend_Session_Namespace('auth');
    	$db = new Application_Model_DbTable_DbGlobal();
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$db = $this->getAdapter();
		$db_other = new Product_Model_DbTable_DbOther();
    	$key_code = $db_other->getLastKeycodeByType(6);
    	$arr = array(
    			'block_name'	=>	$data["title_en"],
    			//'key_code'	=>	$key_code,
    			'create_date'	=>  date("Y-m-d"),
    			'remark'		=>	$data["txt_address"],
    			'user_id'		=>	$GetUserId,
    			'status'		=>	$data["status"],
    	);
    	$this->_name = "tb_zone";
    	$where = $db->quoteInto("id=?", $data["id"]);
    	$this->update($arr, $where);
    }
    //add zone name 
    function addZone($data){
    	$session_user=new Zend_Session_Namespace('auth');
    	$db = new Application_Model_DbTable_DbGlobal();
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$db = $this->getAdapter();
    	$db_other = new Product_Model_DbTable_DbOther();
    	$key_code = $db_other->getLastKeycodeByType(6);
    	//echo $key_code;exit();
    	$arr = array(
    			'block_name'	=>	$data["title_en"],
    			//'key_code'	=>	$key_code,
    			'create_date'	=>  date("Y-m-d"),
    			'remark'		=>	$data["txt_address"],
    			'user_id'		=>	$GetUserId,
    			'status'		=>	$data["status"],
    	);
    	$this->_name = "tb_zone";
    	$this->insert($arr);
    }
    
    //Insert Popup=====================================================================
	
	function addNewCustomerType($data){
    	$db = $this->getAdapter();
    	$key_code = $this->getLastKeycodeByType(6);
    	$arr = array(
    		'name_en'	=>	$data["title_en"],
    		'key_code'	=>	$key_code,
    		'type'		=>	$data["type"],
    		'status'	=>	$data["status"],
    	);
    	$this->_name = "tb_view";
    	$id = $this->insert($arr);
		return $key_code;
    }
	function getCustomerLimit($id){
		$db = $this->getAdapter();
		$sql = "SELECT v.`credit_limit`,v.`credit_term` FROM `tb_view` AS v WHERE v.`type`=6 AND v.`key_code`=$id";
		return $db->fetchRow($sql);
	}
	function getCustomerinfo($customer_id){
		$db = $this->getAdapter();
		$sql = "SELECT *,
					(SELECT SUM(v.sub_total_after) FROM `tb_invoice` AS v ,`tb_sales_order` AS s WHERE v.sale_id=s.id 
					AND v.status=1 AND v.balance_after>0 AND v.is_approved=1 AND 
					customer_id= $customer_id LIMIT 1) AS total_credit,
					(SELECT v.invoice_date FROM `tb_invoice` AS v ,`tb_sales_order` AS s WHERE v.sale_id=s.id 
					AND v.status=1 AND v.balance_after>0 AND v.is_approved=1 AND 
					customer_id= $customer_id ORDER BY v.invoice_date DESC LIMIT 1) AS current_creditterm
				FROM `tb_customer` WHERE id=".$customer_id;
		return $db->fetchRow($sql);
	}

}