<?php

class RsvAcl_Model_DbTable_DbUser extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_acl_user';
	//function for getting record user by user_id
	public function getUser($user_id)
	{
		$select=$this->select();		
		$select->where('user_id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	//get user name
	public function getUserName($user_id)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['username'];
	}
	//change password user wanted
	public function changePassword($user_id,$password)
	{
		$data=array('password'=>$password);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	//is valid password
	public function isValidCurrentPassword($user_id,$current_password)
	{
		$select=$this->select();
		$select->from($this,'password')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if($row){
			$current_password=md5($current_password);
			$password=$row['password'];			 
			if($password==$current_password) return true;
		}
		return false;
	}
	//get infomation of user
	public function getUserInfo($sql)
	{
		$db=$this->getAdapter();
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserID($username)
	{
		$select=$this->select();
			$select->from($this,'user_id')
			->where('username=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_id'];
	}
	//function retrieve record users by column 
	public function getUsers($column)
	{		
		$sql='user_id not in(select user_id from pdbs_acl) AND status=1 ';	
		$select=$this->select();
		$select->from($this,$column)
			   ->where($sql);
		$row=$this->fetchAll($select);
		if(!$row) return NULL;		
		return $row->toArray();
	}
	//function check user have exist
	public function isUserExist($username)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("username=?",$username);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	public function ifUserExist($username)
	{
		$db=$this->getAdapter();
		$sql = "SELECT user_id FROM tb_acl_user WHERE username = '".$username."' LIMIT 1";
		$row = $db->fetchRow($sql);
		if(!$row) return false;
		return true;
	}
	//function check id number have exist
	public function isIdNubmerExist($id_number)
	{
		$select=$this->select();
		$select->from($this,'id_number')
			->where("id_number=?",$id_number);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//add user
	public function insertUser($arr)
	{ 
		$arr['password']=md5($arr['password']);
     	$db = $this->getAdapter();
     	$array_data = array(
     			"title"			=>	$arr["title"],
     			"fullname"		=>	$arr["fullname"],
     			"username"		=>	$arr["username"],
     			"password"		=>	$arr['password'],
     			"email"			=>	$arr["email"],
     			"user_type_id"	=>	$arr["user_type_id"],
     			"LocationId"	=>	$arr["LocationId"],
     			"status"		=>	1,
     			"created_date"	=>	date("Y-m-d H:i:s")
     			);
     	$id=$this->insert($array_data);
     	$ids = explode(",", $arr["identity"]);
     	foreach ($ids as $i){
     		$exist=$this->getUserBranchExist($id, $arr["location_id_".$i]);
     		if($exist=="" AND $arr["LocationId"]!==$arr["location_id_".$i]){
     			$_arrdata = array(
     					"user_id"=>$id,
     					"location_id"=>$arr["location_id_".$i]
     			);
     			$db->insert("rsv_acl_ubranch", $_arrdata);
     		}
     	}
     	$_arrdata = array(
     			"user_id"=>$id,
     			"location_id"=>$arr["LocationId"]
     	);
     	$db->insert("tb_acl_ubranch", $_arrdata);
     	
	}
	public function getUserBranchExist($user_id, $location_id){
		$db=$this->getAdapter();
		$sql="SELECT user_loca FROM rsv_acl_ubranch WHERE user_id = $user_id AND location_id = $location_id LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateUser($arr,$user_id)
	{
		try{
			$db=$this->getAdapter();
			$db->beginTransaction();
			$data = array(
					"title"			=>	$arr["title"],
					"fullname"		=>	$arr["fullname"],
					"username"		=>	$arr["username"],
					"password"		=>	$arr['password'],
					"email"			=>	$arr["email"],
					"user_type_id"	=>	$arr["user_type_id"],
					"LocationId"	=>	$arr["LocationId"],
					"status"		=>	1,
					"created_date"	=>	date("Y-m-d H:i:s")
			);
			$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
			$id=$this->update($data, $where);
			$ids = explode(",", $arr["identity"]);
			$db->query("DELETE FROM rsv_acl_ubranch WHERE user_id = $user_id");
			foreach ($ids as $i){
				$exist=$this->getUserBranchExist($user_id, $arr["location_id_".$i]);
				if($exist=="" AND $arr["LocationId"]!==$arr["location_id_".$i]){
					$_arrdata = array(
							"user_id"=>$user_id,
							"location_id"=>$arr["location_id_".$i]
					);
					$db->insert("rsv_acl_ubranch", $_arrdata);
				}
					
			}
			$_arrdata = array(
					"user_id"=>$user_id,
					"location_id"=>$arr["LocationId"]
			);
			$db->insert("rsv_acl_ubranch", $_arrdata);
			
			$db->commit();
		}
		catch (Exception $e){
			$db->rollBack();
		}
		
		
	}
	
	//function dupdate field status user to force use become inaction
	public function inactiveUser($user_id)
	{
		$data=array('status'=>0);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	public function userAuthenticate($username,$password)
	{
		try{
	              $db_adapter = $this->getDefaultAdapter(); 
	              $auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
	              
	              $auth_adapter->setTableName('rsv_acl_user') // table where users are stored
	                           ->setIdentityColumn('username') // field name of user in the table
	                           ->setCredentialColumn('password') // field name of password in the table
	                           ->setCredentialTreatment('MD5(?) AND status=1'); // optional if password has been hashed
	 
	              $auth_adapter->setIdentity($username); // set value of username field
	              $auth_adapter->setCredential($password);// set value of password field
	 
	              //instantiate Zend_Auth class
	              $auth = Zend_Auth::getInstance();
	 
	              $result = $auth->authenticate($auth_adapter);
	 
	              if($result->isValid()){
	              	  return true;
	              }else{
	                  // validation errors here
					  return false;
	              }
		}catch(Zend_Exception $ex){}
	}
}

