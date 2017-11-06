<?php
class Application_Form_FrmAutocomplete extends Zend_Form
{
		
	
	public static function getDataAutocompleteData($sql){
		$gb_db = new Application_Model_DbTable_DbGlobal();
    	$rs = $gb_db->getGlobalDb($sql);
    	//echo Zend_Json::encode($rs);
    	$data = Zend_Json::encode($rs);
			
		return $data; 
	} 
	
	public static function getScriptAutocomplete($sql,$id_field,$label_field)
	{
		$str_auto = Application_Form_FrmAutocomplete::getDataAutocompleteData($sql);
		$script = '<script type="text/javascript">
							// Autocomplete
							var availableTags = '.$str_auto.';
							$(function() {
								$("#'.$label_field.'").autocomplete({
									source: availableTags,
									select: function( event, ui ) {
										$( "#'.$label_field.'" ).val( ui.item.label );
										$( "#'.$id_field.'" ).val( ui.item.value );
										return false;
									},
									focus: function( event, ui ) {
										$( "#'.$label_field.'" ).val( ui.item.label );
										return false;
									},
									 
									change: function(event, ui) {
									     	if (!ui.item) {
										     	$( "#'.$label_field.'" ).val("");
										     	$( "#'.$id_field.'" ).val("");
									     	}
						     		}
								});
							});
				</script>';
		return $script;
	}

}