<?php

class Application_Form_FrmElementGlobal
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */    	
    }
    
    /**
     * Generate Options From Sql to use form
     * @param string $sql
     * @param string $field
     * @param string $value
     * @param bool $dataonly
     * @return array
     */
    public function getOptions($sql, $field, $value, $dataonly=true){
    	$db = new Application_Model_DbTable_DbGlobal();    	
    	$rs = $db->getAdapter()->fetchAll($sql);
    	
    	$options=array();
    	
    	if(!$dataonly){
    		$options=array(''=>'Please select');
    	}
    	foreach($rs as $read) $options[$read[$value]]=$read[$field];
    	
    	return $options;
    	
    }
	
    /**
     * To get Zend form element text
     * @param string $id
     * @param string $value
     * @param bool $elementequired = true
     * @return Zend_Form_Element_Text
     */
    public function getTextBox($id, $value="", $elementequired = true, $othervalidate=null,$disable=false){
    	$element=new Zend_Form_Element_Text($id);    	
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    	
    	$element->setValue($value);
    	
    	$class = "";
    	if($elementequired){
    		if($othervalidate==null)	$class = 'validate[required] text-input';
	    	else	$class = 'validate[required,'.$othervalidate.'] text-input';
    	}else{
    		$class = 'validate['.$othervalidate.'] text-input';
    	}
    	
    	$element->setAttrib('class',$class);
    	if($disable){
    		$element->setAttrib('disabled', 'disabled');
    	}
    	
    	return  $element;
    }
    
    /**
     * Te get Zend Form Element Select
     * @param string $id
     * @param array $options
     * @param string $select = ''
     * @param bool $elementequired = true
     * @return Zend_Form_Element_Select
     */
    public function getComboBox($id, $options,  $select='', $elementequired = true,$disable=false){
    	$element=new Zend_Form_Element_Select($id);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');    		
    	
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required]',
    		));
    	}
    	$element->addMultiOptions($options);
    	if($select !== ''){
    		$element->setValue($select);
    	}
    	
    	if($disable){
    		$element->setAttrib('disabled', 'disabled');
    	}
    	
    	return  $element;
    }
    
    /**
     * To get Zend form element text with date picker
     * @param string $id
     * @param bool $elementequired = true
     * @return Zend_Form_Element_Text with date picker
     */
    public function getDatePicker($id, $value="" ,$elementequired = true){
    	$element=new Zend_Form_Element_Text($id);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    	 
    	$element->setValue(date_format(date_create($value), 'd-m-Y'));
    	
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required] text-input',
    		));
    	}
    	 
    	Application_Model_GlobalClass::addDateField(array($id));
    	
    	return  $element;
    }
    
    /**
     * To get Zend form element text with date time picker
     * @param string $id
     * @param bool $elementequired = true
     * @return Zend_Form_Element_Text with date picker
     */
    public function getDateTimePicker($id, $value="" ,$elementequired = true){
    	$element=new Zend_Form_Element_Text($id);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    
    	if(empty($value)){
    		$value = date('Y-m-d H:i');
    	}
    	$element->setValue(date_format(date_create($value), 'Y-m-d H:i'));
    	 
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required] text-input',
    		));
    	}
    
    	Application_Model_GlobalClass::addDateTimeField($id);
    	 
    	return  $element;
    }
    
    /**
     * Get Radio Option
     * @param string $id
     * @param array $option
     * @param string $value
     * @return Zend_Form_Element_Radio
     */
    public function getRadios($id,$option, $value='', $elementequired = false){
    	$element = new Zend_Form_Element_Radio($id);
    	$element->addMultiOptions($option);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    	$element->setSeparator('<br/>');
    	
    	if($value !== ''){    		
    		$element->setValue($value);
    	}
    	
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required] radio',
    		));
    	}
    	
    	return $element;    	
    }

    /**
     * Get textarea
     * @param string $id
     * @param string $value
     * @param array $elementequired
     * @return Zend_Form_Element_Textarea
     */
    public function getTextArea($id, $value="", $elementequired = false,$disable=false){
    	$element=new Zend_Form_Element_Textarea($id);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    	 
    	$element->setValue($value);
    	 
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required]',
    		));
    	}
    	if($disable){
    		$element->setAttrib('disabled', 'disabled');
    	}
    	return  $element;
    }   

    public function getCheckBox($id, $value="", $elementequired = false){
    	$element=new Zend_Form_Element_Checkbox($id);
    	$element->removeDecorator('HtmlTag');
    	$element->removeDecorator('DtDdWrapper');
    	$element->removeDecorator('Label');
    	 
    	$element->setValue($value);
    	 
    	if($elementequired){
    		$element->setAttribs(array(
    				'class'=>'validate[required]',
    		));
    	}
    	 
    	return  $element;
    }   
    
    
     
}

