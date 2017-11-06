<?php

class Application_Model_DbTable_DbSiteLanguages extends Zend_Db_Table_Abstract
{

    protected $_name = 'stie_languages';
    
	public function getSiteLanguageActive()
	{	
		$db = $this->getAdapter();
		$sql = "SELECT 						
						l.`language`
						, l.`language_short`
						, l.`icon`												
				FROM `stie_languages` AS sl
					 INNER JOIN `languages` AS l ON(l.`id` = sl.`language_id`)
				WHERE sl.`active` = 1 ";	
			
		return $db->fetchAll($sql);
	}
	
	public function getAllSiteLanguage()
	{
		$db = $this->getAdapter();
		$sql = "SELECT
		l.`language`
		, l.`language_short`
		, l.`icon`
		,sl.`active`
		,sl.`id`
		,sl.`language_id`
		FROM `stie_languages` AS sl
		INNER JOIN `languages` AS l ON(l.`id` = sl.`language_id`) and l.id NOT IN (31,32)";					
		return $db->fetchAll($sql);
	}	
	
	public function updateStatus($active,$id)
	{
		$data=array('active'=>$active);
		$where=$this->getAdapter()->quoteInto('id=?', $id); 
		$this->update($data, $where);
	}
		
	public function getLanguageSelectJson($exp='')
	{
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$sql="SELECT id,language,icon FROM languages WHERE NOT ISNULL(icon) AND id NOT IN (SELECT language_id FROM stie_languages) AND id NOT IN(31,32)".$exp;
		$db=$this->getAdapter();
		$rs=$db->fetchAll($sql);
		$result=array();
		if($rs){
			$i=0;			
			foreach($rs as $read)
			{
				$result[$i]['label']="<table><tr><td style='vertical-align:bottom'><img src='".$baseurl."/images/flag/".$read['icon']."' width='27' height='27' /></td><td style='vertical-align:top'>".$read['language']."</td></tr></table>";
				$result[$i]['abbr']=$read['id'];
				$result[$i]['name']=$read['language'];
				//$result[$i]['capital']=$read['language'];
				$i++;
			}
		}
		return Zend_Json::encode($result); 
	}	
	public function getAbbr($id)
	{
	 	$sql="SELECT language_short AS abbr FROM languages WHERE id='".$id."'";
	 	$rs=$this->getAdapter()->fetchAll($sql);
	 	if(!$rs) return 'en.ini';
	 	return strtolower($rs[0]['abbr']).'.ini';	
	}
	public function getLanguage($id)
	{
		$sql="SELECT language FROM languages WHERE id='".$id."'";
		$rs=$this->getAdapter()->fetchAll($sql);
		if(!$rs) return '';
		return $rs[0]['language'];
	}
	public function isExistLanguage($id)
	{
		$sql="SELECT id FROM stie_languages WHERE language_id='".$id."'";
		$rs=$this->getAdapter()->fetchAll($sql);
		if(!$rs) return false;
		return true;
		
	}	
}

