<?php
class Application_Form_FrmFormatNumber extends Zend_Form
{
	public static function format_percent($number, $decimals = 0) {		
		$number *= 100;
		return number_format($number, $decimals)." %";
	}
	
	public static function format_date($string, $format){
		$timestamp = strtotime($string);
		return date($format, $timestamp);
	}
}