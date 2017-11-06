<?php

class Application_Model_Decorator
{
	public static function removeAllDecorator($form)
	{
		$elements=$form->getElements();
		foreach($elements as $element){
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');		
			$element->removeDecorator('Errors');	
		}
		
	}	
	public static function removeAllDecoratorExceptError($form)
	{
		$elements=$form->getElements();
		foreach($elements as $element){
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');		
		}
		
	}
	
	/**
	 * Set Value to form
	 * @param ZendForm $form
	 * @param array_fetchAll $arr_value
	 */
	public static function setForm($form,$arr_value)
	{
		if($arr_value){
			foreach($arr_value as $read)
				foreach($read as $key=>$value)
				foreach($form->getElements() as $element){
				if($key==$element->getName()){
					$element->setValue($value);
				}
			}
		}
	}	
}

