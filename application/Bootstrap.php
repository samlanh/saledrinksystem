<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	function _initViewHelpers(){
	    date_default_timezone_set('Asia/Phnom_Penh');
	}
	//init Auth Plugin
	protected function _initAuthPlugin()
	{
		//Have been blocked for development process		
	 //Zend_Controller_Front::getInstance()->registerPlugin(
 	 //new Application_Model_CustomAuth(Zend_Auth::getInstance()));
	}
}

