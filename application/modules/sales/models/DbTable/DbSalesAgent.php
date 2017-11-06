<?php

class Sales_Model_DbTable_DbSalesAgent extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_sale_agent";
	function getAllSaleAgent($search){
		$start_date=$search["start_date"];
		$end_date=$search["end_date"];
		$sql = "SELECT 
				  sg.id,
				  l.name AS branch_name,
				  sg.`code`,
				  sg.name,
				  sg.phone,
				  sg.email,
				  sg.address,
				  sg.job_title,
				  sg.`start_working_date`,
				  sg.description ,
				  (SELECT v.name_kh FROM tb_view as v WHERE v.key_code=sg.status AND v.type=5) AS status
				FROM
				  tb_sale_agent AS sg 
				  INNER JOIN tb_sublocation AS l 
					ON sg.branch_id = l.id 
				WHERE 1 
				  AND sg.name != '' AND sg.date>="."'".$start_date."' AND sg.date<="."'".$end_date."'";
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
		if($search['status']!=-1){
			$where .= " AND sg.status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
		$db =$this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	public function getSaleAgentCode($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
	
		$sql=" SELECT id FROM $this->_name AS s WHERE s.`branch_id`=$id ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);

		//$pre = $prefix."EID";
		$pre = "EID";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public function addSalesAgent($data)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$db->beginTransaction();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		
		try{
		// photo image
			$part= PUBLIC_PATH.'/images/stuffdocument/';
			
			$arr=array(
					"username"  		=>	$data['user_name'],
					"password"   		=> 	md5($data['password']),
					"email"      		=>	$data['email'],
					"LocationId"   		=>	$data['branch_id'],
					"user_type_id"		=>	$data["user_type"],
					"fullname"			=>	$data['name'],
					'created_date'		=>	date("Y-m-d"),
					"modified_date"		=>	date("Y-m-d"),
					"status"			=>	$data["status"]
			);
			$this->_name = "tb_acl_user";
			$id = $this->insert($arr);
			
			$arr_u= array(
				'user_id'		=>	$id,
				'location_id'	=>	$data["branch_id"],
			);
			$this->_name="tb_acl_ubranch";
			$this->insert($arr_u);
			
			$photo = $_FILES['photo'];
			if($photo["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "photo".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['photo']["tmp_name"], $part . $newfilename);
				$photo_name = $newfilename;
			}
			
			$document = $_FILES['document'];
			if($document["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "document".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['document']["tmp_name"], $part . $newfilename);
				$document_name = $newfilename;
			}
			
			$signature = $_FILES['signature'];
			if($signature["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "signature".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['signature']["tmp_name"], $part . $newfilename);
				$signature_name= $newfilename;
			}
			
			
			$datainfo=array(
					"code"					=>	$data["code"],
					"name"		 			=>	$data['name'],
					"user_name"  			=>	$data['user_name'],
					"password"   			=> 	md5($data['password']),
					"phone"      			=>	$data['phone'],
					"email"      			=>	$data['email'],
					"address"    			=>	$data['address'],
					"pob"		 			=>	$data['pob'],
					"dob"		 			=>	$data['dob'],
					"job_title"  			=>	$data['job_title'],
					"branch_id"   			=>	$data['branch_id'],
					"user_type"				=>	$data["user_type"],
					"manage_by"				=>	$data["manage_by"],
					"bank_acc"				=>	$data["bank_acc"],
					"start_working_date"	=>	$data["start_working_date"],
					"refer_name"			=>	$data["refer_name"],
					"refer_phone"			=>	$data["refer_phone"],
					"refer_add"				=>	$data["refer_address"],
					"photo"					=>	$photo_name,
					"document"				=>	$document_name,
					"signature"				=>	$signature_name,
					"description"			=>	$data['description'],	
					'user_id'				=>	$GetUserId,
					"date"					=>	date("Y-m-d"),
					"acl_user"				=>	$id,
					"status"			=>	$data["status"]
			);
			$this->_name="tb_sale_agent";
			$this->insert($datainfo);
			
			
		$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			$err = $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err; exit();
		}
	}
	
	public function editSalesAgent($data)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$db =$this->getAdapter();
		$db->beginTransaction();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		try{
			// photo image
			$part= PUBLIC_PATH.'/images/stuffdocument/';
				
			$sql = "SELECT u.* FROM `tb_acl_user` AS u WHERE u.`user_id`="."'".$data['user_id']."'";
			//echo $sql;
			$row = $db->fetchRow($sql);
			$arr=array(
					"username"  		=>	$data['user_name'],
					//"password"   		=> 	md5($data['password']),
					"email"      		=>	$data['email'],
					"LocationId"   		=>	$data['branch_id'],
					"user_type_id"		=>	$data["user_type"],
					"fullname"			=>	$data['name'],
					//'created_date'		=>	date("Y-m-d"),
					"modified_date"		=>	date("Y-m-d"),
					"status"			=>	$data["status"]
			);
			$this->_name = "tb_acl_user";
				
			if(!empty($row)){
				$where=$this->getAdapter()->quoteInto('user_id=?',$data['user_id']);
				$this->update($arr,$where);
				$id = $data['user_id'];
			}else{
				$id = $this->insert($arr);
			}
			
			$sqls = "DELETE FROM `tb_acl_ubranch` WHERE `user_id`="."'".$data["user_id"]."'"." AND `location_id`=".$data["branch_id"];
			$db->query($sqls);
			
			$arr_u= array(
					'user_id'		=>	$id,
					'location_id'	=>	$data["branch_id"],
			);
			$this->_name="tb_acl_ubranch";
				$this->insert($arr_u);
			
			$photo = $_FILES['photo'];
			if($photo["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "photo".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['photo']["tmp_name"], $part . $newfilename);
				$photo_name = $newfilename;
			}else {
				$photo_name = $data["old_photo"];
			}
				
			$document = $_FILES['document'];
			if($document["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "document".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['document']["tmp_name"], $part . $newfilename);
				$document_name = $newfilename;
			}else {
				$document_name = $data["old_document"];
			}
				
			$signature = $_FILES['signature'];
			if($signature["name"]!=""){
				$temp = explode(".", $photo["name"]);
				$newfilename = "signature".$data["code"]. '.' . end($temp);
				move_uploaded_file($_FILES['signature']["tmp_name"], $part . $newfilename);
				$signature_name= $newfilename;
			}else{
				$signature_name = $data["old_signature"];
			}
				
			
				
			$datainfo=array(
					"code"					=>	$data["code"],
					"name"		 			=>	$data['name'],
					"user_name"  			=>	$data['user_name'],
					//"password"   			=> 	md5($data['password']),
					"phone"      			=>	$data['phone'],
					"email"      			=>	$data['email'],
					"address"    			=>	$data['address'],
					"pob"		 			=>	$data['pob'],
					"dob"		 			=>	$data['dob'],
					"job_title"  			=>	$data['job_title'],
					"branch_id"   			=>	$data['branch_id'],
					"user_type"				=>	$data["user_type"],
					"manage_by"				=>	$data["manage_by"],
					"bank_acc"				=>	$data["bank_acc"],
					"start_working_date"	=>	$data["start_working_date"],
					"refer_name"			=>	$data["refer_name"],
					"refer_phone"			=>	$data["refer_phone"],
					"refer_add"				=>	$data["refer_address"],
					"photo"					=>	$photo_name,
					"document"				=>	$document_name,
					"signature"				=>	$signature_name,
					"description"			=>	$data['description'],
					'user_id'				=>	$GetUserId,
					"date"					=>	date("Y-m-d"),
					"acl_user"				=>	$id,
					"status"			=>	$data["status"],
			);
			$this->_name="tb_sale_agent";
			$where=$this->getAdapter()->quoteInto('id=?',$data['id']);
			$this->update($datainfo,$where);
			//exit();
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			$err = $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			echo $err; exit();
		}
	}
	
	public function uploadFile($part,$name){
		$adapter = new Zend_File_Transfer_Adapter_Http();
		$adapter->setDestination($part);
		$files = $adapter->getFileInfo();
		//
		foreach($files as $file => $fileInfo) {
			if ($adapter->isUploaded($file)) {
				if ($adapter->isValid($file)) {
					if ($adapter->receive($file)) {
						$info = $adapter->getFileInfo($file);
						$tmp  = $info[$file]['tmp_name'];
						// here $tmp is the location of the uploaded file on the server
						// var_dump($info); to see all the fields you can use
						print_r($tmp);
						$adapter->addFilter(new Zend_Filter_File_Rename( array('target' => $part.$name)));
						//$adapter->receive();
					}
				}
			}
		}
		
	}
	public function addNewAgent($data){
		$db = new Application_Model_DbTable_DbGlobal();
		$datainfo=array(
				"name"		 =>$data['agent_name'],
				"phone"      =>$data['phone'],
				"job_title"  =>$data['position'],
				"stock_id"   =>$data['location'],
				"description"=>$data['desc'],
		);
		$agent_id=$db->addRecord($datainfo,"tb_sale_agent");
		return $agent_id; 
	}
}