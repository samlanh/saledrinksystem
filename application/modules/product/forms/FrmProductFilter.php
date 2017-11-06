<?php 
class Product_Form_FrmProductFilter extends Zend_Form
{
	public function init()
    {$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	/////////////Filter Product/////////////////
    	
    	$nameValue = $request->getParam('g_name');
    	$nameElement = new Zend_Form_Element_Text('g_name');
    	$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	$rs=$db->getGlobalDb('SELECT id, Name FROM tb_sublocation WHERE Name!="" ');
    	$options=array(''=>$tr->translate('Please_Select_Location'));
    	$locationValue = $request->getParam('id');
    	foreach($rs as $read) $options[$read['id']]=$read['Name'];
    	$location_id=new Zend_Form_Element_Select('id');
    	$location_id->setMultiOptions($options);
    	$location_id->setAttribs(array(
    			'id'=>'id',
    			'onchange'=>'this.form.submit()',
    	));
    	$location_id->setValue($locationValue);
    	$this->addElement($location_id);
    
    	
    	$nameValue = $request->getParam('p_name');
		$nameElement = new Zend_Form_Element_Text('p_name');
		$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	$nameValue = $request->getParam('p_price');
    	$nameElement = new Zend_Form_Element_Text('p_price');
    	$nameElement->setValue($nameValue);
    	$this->addElement($nameElement);
    	
    	/*
    	$rs=$db->getGlobalDb('SELECT CategoryId, Name FROM tb_category');
    	$sql="SELECT CategoryId, parent_id Name FROM  `tb_category`  WHERE parent_id  = $parent_id";
    	$i=1;
    	
    	if ($level != 0)
    	{
    		for($i=1; $i<=$level; $i++)
    		{
    		$minus .= " - - ";
    		}
       }
    		
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
    	
    	*/
    	$rs=$db->getGlobalDb('SELECT pro_id, item_name,item_code FROM tb_product WHERE item_name!="" ');
    	$options=array(''=>$tr->translate('Select_Products'));
    	$proValue = $request->getParam('pro_id');
    	foreach($rs as $read) $options[$read['pro_id']]=$read['item_code']." ".$read['item_name'];
    	$pro_id=new Zend_Form_Element_Select('pro_id');
    	$pro_id->setMultiOptions($options);
    	$pro_id->setAttribs(array(
    			'id'=>'pro_id',
    			'onchange'=>'this.form.submit()'
    	));
    	$pro_id->setValue($proValue);
    	$this->addElement($pro_id);
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$rs=$db->getGlobalDb('SELECT id, Name FROM tb_category WHERE Name!="" ');
    	$options=array(''=>$tr->translate('Please_Select'));
    	$cateValue = $request->getParam('category_id');
    	foreach($rs as $read) $options[$read['CategoryId']]=$read['Name'];
    	$cate_element=new Zend_Form_Element_Select('category_id');
    	$cate_element->setMultiOptions($options);
    	$cate_element->setAttribs(array(
    			'id'=>'category_id',
    			'onchange'=>'this.form.submit()',
    	));
    	$cate_element->setValue($cateValue);
    	$this->addElement($cate_element);
    	
    	$rs=$db->getGlobalDb('SELECT branch_id, Name FROM tb_branch WHERE Name!="" ORDER BY Name');
    	$options=array(''=>$tr->translate('Please_Select'));
    	$branchValue = $request->getParam('branch_id');
    	foreach($rs as $read) $options[$read['branch_id']]=$read['Name'];
    	$branch_element=new Zend_Form_Element_Select('branch_id');
    	$branch_element->setMultiOptions($options);
    	$branch_element->setAttribs(array(
    			'id'=>'branch_id',
    			'onchange'=>'this.form.submit()',
    	));
    	$branch_element->setValue($branchValue);
    	$this->addElement($branch_element);
    	
    	$s_value=$request->getParam("status");
    	$s_option = array(""=>$tr->translate('ALL'),1=>$tr->translate('ACTIVE'),0=>$tr->translate('DEACTIVE'));
    	$status = new  Zend_Form_Element_Select("status");
    	$status->setMultiOptions($s_option);
    	$status->setAttribs(array("Onchange"=>"this.form.submit()"));
    	$status->setValue($s_value);
    	$this->addElement($status);
    	
    	
    	
//     	/////////////Filter Item/////////////////
//     	$itemNameValue = $request->getParam('i_name');
// 		$itemNameElement = new Zend_Form_Element_Text('i_name');
// 		$itemNameElement->setValue($itemNameValue);
//     	$this->addElement($itemNameElement);
    	 
//     	//select for qty
//     	$qtyValue = $request->getParam('qty');
//     	$options=array(0=>'ALL',1=>'Qty in Stock > Qty Demand', 2=> 'Qty in Stock < Qty Demand');
// 		$qty=new Zend_Form_Element_Select('qty');
//     	$qty->setMultiOptions($options);
//     	$qty->setattribs(array(
//     						'onchange'=>'this.form.submit()',
//     						));
//     	$qty->setValue($qtyValue);
//     	$this->addElement($qty);

    	// form has rename from c_name to p_code
    	$codeValue = $request->getParam('p_code');
		$codeElement = new Zend_Form_Element_Text('p_code');
		$codeElement->setValue($codeValue);
    	$this->addElement($codeElement);
    }
    
    public function add($data=null){
    	$name = new Zend_Form_Element_Text("name");
    	$name->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$serial = new Zend_Form_Element_Text("serial");
    	$serial->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$barcode = new Zend_Form_Element_Text("barcode");
    	$barcode->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$brand = new Zend_Form_Element_Text("brand");
    	$brand->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$model = new Zend_Form_Element_Text("model");
    	$model->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$category = new Zend_Form_Element_Text("category");
    	$category->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$color = new Zend_Form_Element_Text("color");
    	$color->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$size = new Zend_Form_Element_Text("size");
    	$size->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$unit = new Zend_Form_Element_Text("unit");
    	$unit->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$qty_per_unit = new Zend_Form_Element_Text("qty_unit");
    	$qty_per_unit->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$measure = new Zend_Form_Element_Text("measure");
    	$measure->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$label = new Zend_Form_Element_Text("label");
    	$label->setAttribs(array(
    			'class'=>'form-control',
    			'required'=>'required'
    	));
    	
    	$this->addElements(array($name,$serial,$brand,$model,$barcode,$category,$size,$color,$measure,$qty_per_unit,$unit,$label));
    	return $this;
    }
    public function searchLocation(){
    	$request = Zend_Controller_Front::getInstance()->getRequest();
    	$nameElement = new Zend_Form_Element_Text('location_name');
    	$nameElement->setAttribs(array("placeholder"=>"Locatin Name..."));
    	$valueloc=$request->getParam("location_name");
    	$nameElement->setValue($valueloc);
    	$this->addElement($nameElement);
    	
    	$contact_nameElement = new Zend_Form_Element_Text('contact_name');
    	$contact_nameElement->setAttribs(array("placeholder"=>"Contact Name..."));
    	$valuecont=$request->getParam("contact_name");
    	$contact_nameElement->setValue($valuecont);
    	$this->addElement($contact_nameElement);
    	
    	$address_Element = new Zend_Form_Element_Text('address_name');
    	$address_Element->setAttribs(array("placeholder"=>"Address Name..."));
    	$valuename=$request->getParam("address_name");
    	$address_Element->setValue($valuename);
    	$this->addElement($address_Element);
    	
    	$phone_Element = new Zend_Form_Element_Text('phone');
    	$phone_Element->setAttribs(array("placeholder"=>"Phone Number..."));
    	$valuephone=$request->getParam("phone");
    	$phone_Element->setValue($valuephone);
    	$this->addElement($phone_Element);
    	return $this;
    }
    private function subCategory($parent_id=0, $level=0){
    	$db= new Application_Model_DbTable_DbGlobal();
    	$rs=$db->getGlobalDb("SELECT CategoryId, parent_id ,Name FROM  `tb_category`  WHERE parent_id =$parent_id");
    	$i=1;$minus = "->";
    	 
    	/*if ($level != 0)
    	{
    		for($i=1; $i<=$level; $i++)
    		{
    		   $minus .= " - - ";
    		}
    	} */
    	if(!empty($rs)) 
    	{
    		foreach($rs as $read) 
    	    {
    			$option[$read['CategoryId']]=$read['Name'];
    	  
    	    }
    	    $this->subCategory($read["CategoryId"],$level+1);
       }
      else{
      	   $rs=$db->getGlobalDb("SELECT CategoryId, parent_id ,Name FROM  `tb_category`  WHERE CategoryId =$parent_id");
	      	if(!empty($rs))
	      	foreach($rs as $read)
	      	{
	      		$test.=$option[$read['CategoryId']]=$read['Name'];
	      		 
	      	}
      	       // $this->subCategory($read["CategoryId"],$level+1);*/
      	
       }
    	
    	return $test;
    }
    public function listCategory(){
    	/*$location=new Zend_Form_Element_Select('CategoryId');
    	$location->setMultiOptions($this->subCategory());
    	$this->addElement($location);
    	return $this;*/
    }
}