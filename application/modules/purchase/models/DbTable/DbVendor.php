<?php

class Purchase_Model_DbTable_DbVendor extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_vendor";
	public function setName($name)
	{
		$this->_name=$name;
	}
	function getAllVender($search){
		$db = $this->getAdapter();
		$sql=" SELECT v.vendor_id, v.v_name,v.v_phone,v.contact_name,v.phone_person,v.email, v.website,v.add_name,
		(SELECT vi.`name_en` FROM `tb_view` AS vi WHERE vi.`type`=5 AND vi.key_code=v.status) AS status
		FROM tb_vendor AS v WHERE v_name!='' ";
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " v.v_name LIKE '%{$s_search}%'";
			$s_where[] = " v.v_phone LIKE '%{$s_search}%'";
			$s_where[] = " v.contact_name LIKE '%{$s_search}%'";
			$s_where[] = " v.phone_person LIKE '%{$s_search}%'";
			$s_where[] = " v.add_name LIKE '%{$s_search}%'";
			$s_where[] = " v.fax LIKE '%{$s_search}%'";
			$s_where[] = " v.email LIKE '%{$s_search}%'";
			$s_where[] = " v.website LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['status']>-1){
			$where .= " AND v.status = ".$search['status'];
		}
		$order=" ORDER BY v.vendor_id DESC";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getvendorById($id){
		$sql = "SELECT * FROM tb_vendor WHERE vendor_id=".$id;
		$db = $this->getAdapter();
		return $db->fetchRow($sql);
	}
	final public function addVendor($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$db->beginTransaction();
		try{
			if(@$post["is_over_sea"]==1){
				$data=array(
						'v_name'		=> $post['txt_name'],
// 						'v_phone'		=> $post['txt_phone'],
// 						'contact_name'	=> $post['txt_contact_name'],
// 						'phone_person'	=> $post['contact_phone'],
// 						'add_name'		=> $post['txt_address'],
// 						'email'			=> $post['txt_mail'],
// 						'website'		=> $post['txt_website'],
// 						'fax'			=> $post['txt_fax'],
// 						'note'	=> $post['remark'],
						'is_over_sea'	=>	$post["is_over_sea"],
						'last_usermod'	=> $GetUserId,
						'last_mod_date' => new Zend_Date(),
						'date'			=>	date("Y-m-d"),
						'status'			=>	$post["status"],
				);
			}else {
				$data=array(
						'v_name'		=> $post['txt_name'],
						'v_phone'		=> $post['txt_phone'],
						'contact_name'	=> $post['txt_contact_name'],
						'phone_person'	=> $post['contact_phone'],
						'add_name'		=> $post['txt_address'],
						'email'			=> $post['txt_mail'],
						'website'		=> $post['txt_website'],
						'fax'			=> $post['txt_fax'],
						'note'			=> $post['remark'],
						'is_over_sea'	=>	0,
						'last_usermod'	=> $GetUserId,
						'last_mod_date' => new Zend_Date(),
						'date'			=>	date("Y-m-d"),
						'status'			=>	$post["status"],
				);
			}
			if(!empty($post['id'])){
				$where = "vendor_id = ".$post["id"];
				$this->update($data, $where);
			}else{
				$db->insert("tb_vendor", $data);
			}
			return $db->commit();
		}
		catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	final public function addnewvendor($post){//ajax
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		try{
			$data=array(
					'v_name'		=> $post['vendor_name'],
					'v_phone'		=> $post['com_phone'],
					'contact_name'	=> $post['txt_contact_name'],
					'phone_person'	=> $post['v_phone'],
					'add_name'		=> $post['txt_address'],
					'email'			=> $post['txt_mail'],
	// 				'website'		=> $post['txt_website'],
	// 				'fax'			=> $post['txt_fax'],
					'note'			=> $post['vendor_note'],
					'is_over_sea'	=>	0,
					'last_usermod'	=> $GetUserId,
					'last_mod_date' => new Zend_Date(),
					'date'			=>	date("Y-m-d"),
			);
		   return $this->insert($data);
		}catch(Exception $e){
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
		
	}

	
	
}