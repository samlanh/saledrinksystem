<?php 
class Purchase_Form_FrmRecieve extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function add($data=null) {
		$db_r = new Purchase_Model_DbTable_DbRecieve();
		$db=new Application_Model_DbTable_DbGlobal();
		$rs_code = $db_r->getAllPuCode();
		$rp_code = $db_r->getRecieveCode();
		$date =new Zend_Date();
		$opt_code = array();
		if(!empty($rs_code)){
			foreach($rs_code as $rs){
				$opt_code[$rs["id"]] = $rs["order_number"];
			}
		}
		$pu_code = new Zend_Form_Element_Select('pu_code');
		$pu_code->setAttribs(array('class'=>'validate[required] form-control select2me' ,"readOnly"=>"readOnly",'placeholder'=>'Select Purchase No'));
    	$pu_code->setMultiOptions($opt_code);
		$this->addElement($pu_code);
		
		$recieve_no = new Zend_Form_Element_Text('recieve_no');
		$recieve_no->setAttribs(array('class'=>'validate[required] form-control','placeholder'=>'Select Purchase No'));
		$recieve_no->setValue($rp_code);
    	$this->addElement($recieve_no);
		
		$date_order = new Zend_Form_Element_Text('date_order');
		$date_order->setAttribs(array('class'=>'validate[required] form-control date-picker','placeholder'=>'Select Purchase No'));
		$date_order->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($date_order);
		
		$date_in = new Zend_Form_Element_Text('date_in');
		$date_in->setAttribs(array('class'=>'validate[required] form-control date-picker','placeholder'=>'Select Purchase No'));
		$date_in->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($date_in);
		
		$date_recieve = new Zend_Form_Element_Text('date_recieve');
		$date_recieve->setAttribs(array('class'=>'validate[required] form-control date-picker','placeholder'=>'Select Purchase No'));
		$date_recieve->setValue($date->get('MM/dd/YYYY'));
    	$this->addElement($date_recieve);
		
		$sql_v = "SELECT v.`vendor_id`,v.`v_name`  FROM `tb_vendor` AS v WHERE 1";
		$row_v = $db->getGlobalDb($sql_v);
		$opt_v = array(''=>'SELECT_VENDOR');
		if(!empty($row_v)){
			foreach($row_v as $rs){
				$opt_v[$rs["vendor_id"]] = $rs["v_name"];
			}
		}
		$vendor = new Zend_Form_Element_Select('vendor');
		$vendor->setAttribs(array('class'=>'validate[required] form-control select2me',"readOnly"=>"readOnly",'placeholder'=>'Select Purchase No'));
    	$vendor->setMultiOptions($opt_v);
		$this->addElement($vendor);
		
		$sql = "SELECT pl.id,pl.`name` FROM `tb_sublocation` AS pl WHERE 1";
		$row_b = $db->getGlobalDb($sql);
		$opt_b = array(''=>'SELECT_BRANCH');
		if(!empty($row_b)){
			foreach($row_b as $rs){
				$opt_b[$rs["id"]] = $rs["name"];
			}
		}
		$branch = new Zend_Form_Element_Select('branch');
		$branch->setAttribs(array('class'=>'validate[required] form-control select2me','readOnly'=>'readOnly'));
		$branch->setMultiOptions($opt_b);
    	$this->addElement($branch);
		
		
    	$paymentmethodElement = new Zend_Form_Element_Hidden('payment_name');
    	$this->addElement($paymentmethodElement);

    	$currencyElement = new Zend_Form_Element_Hidden('currency');
    	$currencyElement->setAttribs(array('class'=>'demo-code-language form-control'));
    	$this->addElement($currencyElement);
		
		$allTotalElement = new Zend_Form_Element_Hidden('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','style'=>'text-align:right'));
    	$this->addElement($allTotalElement);
		
		$allTotalElement_after= new Zend_Form_Element_Hidden('all_total_after');
    	$allTotalElement_after->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','style'=>'text-align:right'));
    	$this->addElement($allTotalElement_after);
    	
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',"class"=>"form-control"));
    	$this->addElement($discountTypeElement);    

    	$netTotalElement = new Zend_Form_Element_Hidden('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
		
		$netTotalElementAfter = new Zend_Form_Element_Hidden('net_total_after');
    	$netTotalElementAfter->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElementAfter);
    	
    	$discountValueElement = new Zend_Form_Element_Hidden('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px form-control','onblur'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Hidden('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px form-control',));
    	$this->addElement($discountRealElement);
    	
    	$globalRealElement = new Zend_Form_Element_Hidden('global_disc');
    	$globalRealElement->setAttribs(array("class"=>"form-control"));
    	$this->addElement($globalRealElement);
    	
    	
    	$discountValueElement = new Zend_Form_Element_Hidden('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onblur'=>'doTotal();','style'=>'text-align:right'));
    	$this->addElement($discountValueElement);
    	
    	$dis_valueElement = new Zend_Form_Element_Hidden('dis_value');
    	$dis_valueElement->setAttribs(array('placeholder' => 'Discount Value','style'=>'text-align:right'));
    	$dis_valueElement->setValue(0);
    	$dis_valueElement->setAttribs(array("onkeyup"=>"calculateDiscount();","class"=>"form-control"));
    	$this->addElement($dis_valueElement);
    	
    	$totalAmountElement = new Zend_Form_Element_Hidden('totalAmoun');
    	$totalAmountElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"
    	));
    	$this->addElement($totalAmountElement);
    	
		$totalAmountAfterElement = new Zend_Form_Element_Hidden('totalAmoun_after');
    	$totalAmountAfterElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"
    	));
    	$this->addElement($totalAmountAfterElement);
    	
    	$remainlElement = new Zend_Form_Element_Hidden('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"red form-control"));
    	$this->addElement($remainlElement);
		
		$remain_after = new Zend_Form_Element_Hidden('remain_after');
    	$remain_after->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"red form-control"));
    	$this->addElement($remain_after);
    	
    	$balancelElement = new Zend_Form_Element_Hidden('balance');
    	$balancelElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"));
    	$this->addElement($balancelElement);
		
		$balance_after = new Zend_Form_Element_Hidden('balance_after');
    	$balance_after->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"));
    	$this->addElement($balance_after);
		
		$totaTaxElement = new Zend_Form_Element_Hidden('total_tax');
    	$totaTaxElement->setAttribs(array('class'=>'custom[number] form-control','style'=>'text-align:right'));
    	$this->addElement($totaTaxElement);
    	
    	$paidElement = new Zend_Form_Element_Hidden('paid');
    	$paidElement->setAttribs(array('class'=>'custom[number] form-control','onkeyup'=>'doRemain();','style'=>'text-align:right'));
    	$this->addElement($paidElement);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array('class'=>'form-control','placeholder'=>'Remark Here'));
    	$this->addElement($remark);
		
		$re_id = new Zend_Form_Element_Hidden('re_id');
		$this->addElement($re_id);
    	
    	if($data != null) {
			//$re_id->setValue($data["re_id"]);
	        $branch->setValue($data["branch_id"]);
		   $pu_code->setValue($data["id"]);
		   $vendor->setValue($data["vendor_id"]);
		   $date_order->setValue(date("m/d/Y",strtotime($data["date_order"])));
			$date_in->setValue(date("m/d/Y",strtotime($data["date_in"])));
		$paymentmethodElement->setValue($data["payment_method"]);
		$currencyElement->setValue($data["currency_id"]);
		$allTotalElement->setValue($data["all_total"]);
		$allTotalElement_after->setValue($data["all_total"]);
		$netTotalElement->setValue($data["net_total"]);
		$netTotalElementAfter->setValue($data["net_total"]);
		$discountValueElement->setValue($data["discount_value"]);
		$dis_valueElement->setValue($data["discount_value"]);
		$totalAmountElement->setValue($data["net_total"]);
		$totalAmountAfterElement->setValue($data["net_total"]);
		$remainlElement->setValue($data["balance"]);
		$remain_after->setValue($data["balance"]);
		$globalRealElement->setValue($data["discount_real"]);
		$paidElement->setValue($data["paid"]);
		}
    	return $this;
	}
}