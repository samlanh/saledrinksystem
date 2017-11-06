<?php

class Application_Form_Frmlogin extends Zend_Form
{
    public function init()
    {
    	/*
    	 * Author Kry Chanto
    	 */
        /* Form Elements & Other Definitions Here ... */	    	   	    	
    	$this->setDisableLoadDefaultDecorators(true);
 
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => 'index/_login.phtml')),
            'Form'
        ));            
		$user_name_login=new Zend_Form_Element_Text('txt_email');		
		$user_name_login->setLabel('Email')		
			->setRequired(true)
			->addFilter('StringTrim')			
			->addValidator('notEmpty');			
		//$user_name_login->setDecorators($decorators);
		$password_login=new Zend_Form_Element_Password('txt_password');
		$password_login->setLabel('Password')
			->setRequired(true)			
			->addFilter('StringTrim')			
			->addValidator('notEmpty');
		//$password_login->setDecorators($decorators);
		$submit_login=new Zend_Form_Element_Submit('submit_login');				
		$submit_login->setLabel('Login ...');				
		//$submit_login->setDecorators($decorator_button);	
							
		$this->addElements(array($user_name_login,$password_login,$submit_login));								
    }
}

