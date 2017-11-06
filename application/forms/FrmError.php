<?php

class Application_Form_FrmError
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	
    }
	public static function messageError($msg)
	{
		$form=new Application_Form_Frmlist();
		$array=array('tag'=>'div','attribute'=>array('id'=>'error'));
		return $form->formSubElement($array,$msg);		
	}
	public function messageErr($msg)
	{
		$form=new Application_Form_Frmlist();
		$array=array('tag'=>'div','attribute'=>array('id'=>'error'));
		return $form->formSubElement($array,$msg);		
	}
	public static function messageSucess($msg)
	{
		$form=new Application_Form_Frmlist();
		$array=array('tag'=>'div','attribute'=>array('id'=>'sucess'));
		return $form->formSubElement($array,$msg);		
	}
	public static function jsMsg($msg)
	{
		$form=new Application_Form_Frmlist();
		$array=array('tag'=>'script','att'=>array('language'=>'javascript'));
		return $form->formSubElement($array,'alert("'.$msg.'");');
	}
}

