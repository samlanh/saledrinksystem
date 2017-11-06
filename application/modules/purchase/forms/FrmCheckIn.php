<?php 
class purchase_Form_FrmCheckIn extends Zend_Form
{
	public function init()
    {	
	}
	
	public function checkIn(){
		$inviceno = new Zend_Form_Element_Text("invoiceno");
		$v_name = new Zend_Form_Element_Select("v_name");
		$opt_vname = array(1=>"abc",2=>"ugu");
		$v_name->setMultiOptions($opt_vname);
//	$v_name->setAttribs(array("readonly"=>"readonly"));
	}
}