<?php 
class Product_Form_FrmItemPrice extends Zend_Form
{
	function add($data=null){
		$db=new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name= new Zend_Form_Element_Text("price_name");
		$name->setAttribs(array('class'=>'form-control'));
		
		$remark = new Zend_Form_Element_Text('price_decs');
		$remark->setAttribs(array('class'=>'form-control'));
		
		$optionsStatus=array(1=>$tr->translate("ACTIVE"),0=>$tr->translate('DEACTIVE'));
		$statusElement = new Zend_Form_Element_Select('status');
		$statusElement->setAttribs(array('class'=>'form-control'));
		$statusElement->setMultiOptions($optionsStatus);
		
		if($data!=""){
			$name->setValue($data["name"]);
			$remark->setValue($data["desc"]);
			$statusElement->setValue($data["status"]);
		}
		
		return $this->addElements(array($name,$remark,$statusElement));
		
	}
	public function AddItemPrice($data=null) {
		$db=new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
    	$rs = $db->getGlobalDb('SELECT DISTINCT item_name, pro_id FROM tb_product WHERE item_name!="" ');
    	$item_option=array();
    	if($rs) {
    		foreach($rs as $item) $item_option[$item['pro_id']]=$item['item_name'];
    	}
		$itemElement = new Zend_Form_Element_Select('pro_id');
		$itemElement->setAttribs(array('class'=>'validate[required]',));
		$itemElement->setMultiOptions($item_option);
    	$this->addElement($itemElement);
    	
    	$descElement = new Zend_Form_Element_Textarea('price_desc');
    	$this->addElement($descElement);
    	
    	if($data != null) {
    		
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement ->setValue($data[0]["product_id"]);
    		
    		$itemElement->setValue($data[0]["product_id"]);
    		$descElement->setValue($data[0]["desc"]);
    	}
    	return $this;
	}
	public function AddClassPrice($data=null) {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 		$db=new Application_Model_DbTable_DbGlobal();
		$priceElement = new Zend_Form_Element_Text('price_name');
		$priceElement->setAttribs(array('class'=>'validate[required]',));
		$this->addElement($priceElement);
		
		$price_descElement = new Zend_Form_Element_Text('price_decs');
		$this->addElement($price_descElement);
		
		$optionsStatus=array(1=>$tr->translate("ACTIVE"),0=>$tr->translate('DEACTIVE'));
		$statusElement = new Zend_Form_Element_Select('status');
		$statusElement->setMultiOptions($optionsStatus);
		$this->addElement($statusElement);
		
		 
		if($data != null) {
			
			$idElement = new Zend_Form_Element_Hidden('type_id');
			$idElement->setValue($data["id"]);
			$this->addElement($idElement);
			
			$priceElement->setValue($data["name"]);
			$price_descElement->setValue($data["desc"]);
			$statusElement->setValue($data["status"]);
		}
		return $this;
	}
	function searchPrice(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		/////////////Filter Product/////////////////
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$nameValue = $request->getParam('g_name');
		$nameElement = new Zend_Form_Element_Text('g_name');
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		
		$rs=$db->getGlobalDb('SELECT type_id, price_type_name FROM tb_price_type WHERE public = 1 AND price_type_name!=""');
		$options=array(''=>$tr->translate('Please_Select_Type_Price'));
		$pricetypeValue = $request->getParam('type_id');
		if(!empty($rs))
		foreach($rs as $read) $options[$read['type_id']]=$read['price_type_name'];
		$pricetype_id=new Zend_Form_Element_Select('type_id');
		$pricetype_id->setMultiOptions($options);
		$pricetype_id->setAttribs(array(
				'id'=>'type_id',
				'onchange'=>'this.form.submit()',
		));
		$pricetype_id->setValue($pricetypeValue);
		$this->addElement($pricetype_id);
		
		 
		$nameValue = $request->getParam('p_name');
		$nameElement = new Zend_Form_Element_Text('p_name');
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		 
		$nameValue = $request->getParam('p_price');
		$nameElement = new Zend_Form_Element_Text('p_price');
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		 
		$rs=$db->getGlobalDb('SELECT CategoryId, Name FROM tb_category WHERE Name!="" ');
		$options=array('Please Select Product');
		$cateValue = $request->getParam('category_id');
		foreach($rs as $read) $options[$read['CategoryId']]=$read['Name'];
		$cate_element=new Zend_Form_Element_Select('category_id');
		$cate_element->setMultiOptions($options);
		$cate_element->setAttribs(array(
				'id'=>'category_id',
				'class'=>'demo-code-language',
				'onchange'=>'this.form.submit()',
		));
		$cate_element->setValue($cateValue);
		$this->addElement($cate_element);

		$codeValue = $request->getParam('p_code');
		$codeElement = new Zend_Form_Element_Text('p_code');
		$codeElement->setValue($codeValue);
		$this->addElement($codeElement);
		
		$_arroption = array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
		$_var = $request->getParams("status");
		$statusElement = new Zend_Form_Element_Select("status");
		$statusElement->setMultiOptions($_arroption);
		$statusElement->setAttribs(array(
				'id'=>'status',
				'onchange'=>'this.form.submit()',
		));
		$statusElement->setValue($_var);
		$this->addElement($statusElement);
		
		return $this;
		
	}
	public function searchPriceType(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$priceElement = new Zend_Form_Element_Text('price_type');
		$priceElement->setAttribs(array('class'=>"form-control"));
		$pricevalue = $request->getParam("price_type");
		$priceElement->setValue($pricevalue);
		$this->addElement($priceElement);
		
		$optionsStatus=array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
		$statusElement = new Zend_Form_Element_Select('status');
		$statusvalue = $request->getParam("status");
		$statusElement->setAttribs(array('class'=>"form-control"));
		$statusElement->setValue($statusvalue);
		$statusElement->setMultiOptions($optionsStatus);
		$this->addElement($statusElement);
		return $this;
		
	}
}