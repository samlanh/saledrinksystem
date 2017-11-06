<?php
class Application_Form_FrmAction extends Zend_Form
{       public function init()
	    {
	        /* Form Elements & Other Definitions Here ... */
	    }
        public function AllAction($data=null)
        {	
        	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        	$newElement = new Zend_Form_Element_Submit('New');
        	$newElement->setLabel($tr->translate("NEW"));
        	$this->addElement($newElement);
        	 
        	$saveElement = new Zend_Form_Element_Submit('Save');
        	$saveElement->setAttribs(array('class'=>'save'));
        	$saveElement->setLabel($tr->translate("SAVE_CLOSE"));
        	$this->addElement($saveElement);
        	
        	$saveNewElement = new Zend_Form_Element_Submit('SaveNew');
        	$saveNewElement->setAttribs(array('class'=>'savenew'));
        	$saveNewElement->setLabel($tr->translate("SAVE_NEW"));
        	$this->addElement($saveNewElement);
        	 
        	$updateElement = new Zend_Form_Element_Submit('Update');
        	$updateElement->setAttribs(array('class'=>'update'));
        	$updateElement->setLabel($tr->translate("UPDATE"));
        	$this->addElement($updateElement);
        	 
        	$deactiveElement = new Zend_Form_Element_Submit('Deactive');
        	$deactiveElement->setAttribs(array('class'=>'deactive'));
        	$deactiveElement->setLabel($tr->translate("DEACTIVE"));
        	$this->addElement($deactiveElement);
        	
        	$activeElement = new Zend_Form_Element_Submit('Active');
        	$activeElement->setAttribs(array('class'=>'activate'));
        	$activeElement->setLabel($tr->translate("ACTIVE"));
        	$this->addElement($activeElement);
        	
        	$CancelElement = new Zend_Form_Element_Submit('Cancel');
        	$CancelElement->setAttribs(array('class'=>'cancel'));
        	$this->addElement($CancelElement);
        	 
        	return $this;
        } 
}
?>