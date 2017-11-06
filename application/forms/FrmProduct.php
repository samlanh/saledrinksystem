<?php

class Application_Form_FrmProduct extends Zend_Form
{

    public function init()
    {
    }
    public function AddProductForm($data=null) {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$db=new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$nameElement = new Zend_Form_Element_Text('txt_name');
    	$nameElement->setAttribs(array('class'=>'validate[required]','placeholder'=>$tr->translate('ENTER_ITEM')));
    	$this->addElement($nameElement);
    	
    	$product_size = new Zend_Form_Element_Text('product_size');
    	$product_size->setAttribs(array('placeholder'=>$tr->translate('ENTER_ITME_SIZE')));
    	
    	$this->addElement($product_size);
    	
    	$codeElement = new Zend_Form_Element_Text('txt_code');
    	$codeElement->setAttribs(array(
    			'placeholder'=>$tr->translate("ENTER_ITEM_CODE"),
    			'class'=>'validate[required]',
    			'Onblur'=>'CheckPCode()',
    			));
    	$this->addElement($codeElement);
    	
    	$rowsBranch= $db->getGlobalDb('SELECT CategoryId, Name FROM tb_category WHERE Name!="" AND IsActive=1 ORDER BY CategoryId DESC ');
    	$options = "";
    	if($result["level"]==1 OR $result["level"]==2){
    		$options = array(""=>$tr->translate("SELECT_CATEGORY"),"-1"=>$tr->translate("ADD_NEW"));
    	}
    	if($rowsBranch) {
    		foreach($rowsBranch as $readCategory) $options[$readCategory['CategoryId']]=$readCategory['Name'];
    	}
    	$categoryElement = new Zend_Form_Element_Select('category');
    	$categoryElement->setAttribs(array('class' => 'validate[required]',
    			"Onchange"=>"showPopupCategory()"
    			));
    	$categoryElement->setMultiOptions($options);
    	$this->addElement($categoryElement); 

    	$rowsBranch= $db->getGlobalDb('SELECT branch_id, Name FROM tb_branch WHERE Name!="" AND IsActive=1 ORDER BY Name DESC ');
    	$options = "";
    	if($result["level"]==1 OR $result["level"]==2){
    		$options = array(""=>$tr->translate("SELECT_BRAND"),"-1"=>$tr->translate("ADD_NEW"));
    	}
    	if($rowsBranch) {
    		foreach($rowsBranch as $readBranch) $options[$readBranch['branch_id']]=$readBranch['Name'];
    	}
    	$branchElement = new Zend_Form_Element_Select('branch_id');
    	$branchElement->setAttribs(array('class' => 'validate[required]',"Onchange"=>"showPopupBranch()"));
    	$branchElement->setMultiOptions($options);
    	$this->addElement($branchElement);
    	
    	$typeElement = new Zend_Form_Element_Select('stock_type');
    	$typeElement ->setAttribs(array('id'=>'single'));
    	$typeElement->setMultiOptions(array(1=>$tr->translate("STOCKABLE"), 2=>$tr->translate("NON_STOCKED"),3=>$tr->translate("SERVICE")));
    	$this->addElement($typeElement);
    	
    	$rowsmeasures= $db->getGlobalDb("SELECT id,measure_name FROM `tb_measure` WHERE measure_name!='' AND public =1");
    	$options = array(""=>$tr->translate("SELECT_MEASURE"),"-1"=>$tr->translate("ADD_NEW"));
    	if($rowsmeasures) {
    		foreach($rowsmeasures as $readmeasure) $options[$readmeasure['id']]=$readmeasure['measure_name'];
    	}
    	$measure_unit = new Zend_Form_Element_Select('measure_unit');
    	$measure_unit->setMultiOptions($options);
    	$measure_unit->setAttribs(array('class' => 'validate[required]',"Onchange"=>"showPopupMeasure()"));
    	$measure_unit->setMultiOptions($options);
    	$this->addElement($measure_unit);
    	
    	$uom = new Zend_Form_Element_Text('uom');//uom = unit of measure
    	$uom->setAttribs(array('class'=>'validate[custom[number]',"style"=>"width:initial;","size"=>"4",'readonly'=>'readonly'));
    	$uom->setValue(1);
    	$this->addElement($uom);
    	
    	$qty_perunit = new Zend_Form_Element_Text('qty_perunit');//uom = unit of measure
    	$qty_perunit->setAttribs(array('class'=>'validate[required[custom[number]]]','placeholder'=>$tr->translate("QTY_PERUNIT"),"style"=>"width:initial;","size"=>"4"));
    	$this->addElement($qty_perunit);
    	
    	$label_perunit = new Zend_Form_Element_Text('label_perunit');//uom = unit of measure
    	$label_perunit->setAttribs(array('class'=>'validate[required]','placeholder'=>$tr->translate("LABEL_UNIT"),"style"=>"width:initial;","size"=>"10"));
    	$this->addElement($label_perunit);
    	
    	$itemImageElement = new Zend_Form_Element_File('photo');
    	$this->addElement($itemImageElement);
    	
//     	$costingMethodElement = new Zend_Form_Element_Select('costingmethod');
//     	$costingMethodElement->setMultiOptions(array(1=>$tr->translate("Manual"), 2=>$tr->translate("Moving Average"),3=>$tr->translate("Last Purchase")));
//     	$this->addElement($costingMethodElement);
    	
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    	 
    	$status = new Zend_Form_Element_Radio('status');
    	$status->setMultiOptions(array(1=>$tr->translate("ALL"), 2=>$tr->translate("IS_PURCHASE"),3=>$tr->translate("IS_SALES"),4=>$tr->translate("ALL_NONE")));
    	$status->setValue(1);
    	$this->addElement($status);
    	
    	$purchase_tax = new Zend_Form_Element_Text("pur_tax");
    	$purchase_tax->setAttribs(array('class'=>'validate[required]','placeholder'=>$tr->translate("Purchase Tax"),"style"=>"width:initial;text-align:right;padding-right:20px;","size"=>"14"));
    	$this->addElement($purchase_tax);
    	
    	$sale_tax = new Zend_Form_Element_Text("sale_tax");
    	$sale_tax->setAttribs(array('class'=>'validate[required[custom[number]]]','placeholder'=>$tr->translate("Sale Tax"),"style"=>"width:initial;text-align:right;padding-right:20px;","size"=>"14"));
    	$this->addElement($sale_tax);
    	
    	$rssetting=$db->getSetting();
    	$unit_sale_price = new Zend_Form_Element_Text('unit_sale_price');
    	if($rssetting[0]['key_value']==1){//if set default salse price
    		$unit_sale_price = new Zend_Form_Element_Text('unit_sale_price');
    		$unit_sale_price->setLabel("Unit Sale Price");
    		$unit_sale_price->setAttribs(array('class'=>'validate[required[custom[number]]]'));
    	}else{ 
    		$unit_sale_price = new Zend_Form_Element_Hidden('unit_sale_price');
    		$unit_sale_price->setLabel("");}
    	$this->addElement($unit_sale_price);
    	//echo $rs['key_valu'];
    	
    	
    	if(!$data==null)
    	{
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement->setValue($data['pro_id']);
    		$itemImageElement->setValue($data['photo']);
    		$codeElement->setValue($data['item_code']);
    		$categoryElement->setValue($data['cate_id']);
    		$branchElement->setValue($data['brand_id']);
    		$measure_unit->setValue($data['measure_id']);
    		$qty_perunit->setValue($data['qty_perunit']);
    		$label_perunit->setValue($data['label']);
    		
    		
    		$nameElement->setValue($data['item_name']);
    		$typeElement->setValue($data['stock_type']);
    		$remarkElement->setValue($data['remark']);
    		$unit_sale_price->setValue($data['unit_sale_price']);
    		$purchase_tax->setValue($data["purchase_tax"]);
    		$sale_tax->setValue($data["sale_tax"]);
    	}	 
    	return $this;
    }
}
?>