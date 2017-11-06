<?php
class Application_Form_DateTimePicker 
{
	public static function addDateField($date_fields)
	{
		$template='$("#template").datepicker({"changeYear":"true","changeMonth":"true","yearRange":"-40:+100","dateFormat":"yy-mm-dd"} );';
		$script='<script language="javascript"> $(document).ready(function() {  #template#		});</script>';
		$value='';
		if(is_array($date_fields)){
			foreach($date_fields as $read){
				$value.=str_replace('#template', '#'.$read, $template);				
			}		
		}
		else{
			$value=str_replace('#template', '#'.$date_fields, $template);
		}
		echo str_replace('#template#', $value, $script);
	}
	/**
	 * Get Datepicker with event Onselect
	 * @param array() $date_fields
	 * @example
	 * 		addDateFieldCalDOB(array(array('dob_1','age_1'), array('dob_2','age_2')));
	 */
	public static function addDateFieldCalDOB($date_fields)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$template='$("#template").datepicker({"changeYear":"true","changeMonth":"true","yearRange":"-40:+100","dateFormat":"dd-mm-yy" , onslect});';
		$script='<script language="javascript"> $(document).ready(function() {  #template#		});</script>';
		$onselect = 'onSelect: function(dateText, inst) {
						var prefix = " '. $tr->translate("YEARS") .'";
						var _d = dateText.split("-");
						var d = new Date(_d[2], _d[1], _d[0]);
						var now = new Date();
						var age = now.getFullYear() - d.getFullYear();
						$("#dob_id").val(age +  " " + prefix);
					}';
		$value='';
		if(is_array($date_fields)){
			foreach($date_fields as $read){
				$tmpsel = str_replace('#dob_id', '#'.$read[1], $onselect);
				$tmptem = str_replace('onslect', $tmpsel, $template);
				$value.=str_replace('#template', '#'.$read[0], $tmptem);
			}
		}
		else{
			$tmpsel = str_replace('#dob_id', '#'.$read[1], $onselect);
				$tmptem = str_replace('onslect', $tmpsel, $template);
				$value.=str_replace('#template', '#'.$read[0], $tmptem);
		}
		echo str_replace('#template#', $value, $script);
	}
	public static function addDateTimeField($date_fields)
	{
		$template='$("#template").datetimepicker({"changeYear":"true","changeMonth":"true","yearRange":"-40:+100","dateFormat":"dd-mm-yy"});';
		$script='<script language="javascript"> $(document).ready(function() {	#template#	});</script>';
		$value='';
		if(is_array($date_fields)){
			foreach($date_fields as $read){
				$value.=str_replace('#template', '#'.$read, $template);
			}
		}
		else{
			$value=str_replace('#template', '#'.$date_fields, $template);
		}
		echo str_replace('#template#', $value, $script);
	}
}