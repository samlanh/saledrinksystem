<?php
/*
 * Author: 	KRY CHANTO
 * Date	 : 	15-July-2011
 */
class Application_Form_FrmVeiwlist
{        
    /*
     * used to veiw order left
     */
    public function getCheckList($delete=0, $columns,$rows,$link=null,$editLink="", $class='items', $textalign= "left", $report=false, $id = "table")
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	/*
     	* Define string of pagination Sophen 27 June 2012
     	*/
    	$stringPagination = '<script type="text/javascript">
				$(document).ready(function(){
					$("#'.$id.'").tablesorter();
					
					$("#'.$id.'").tablesorter().tablesorterPager({container: $("#pagination_'.$id.'")});
					$("input:.pagedisplay").focus(function(){ this.blur(); });
					
					function changeColor(){
						alert("change color on mouse over");
					}
				});
		</script>
		<div id="pagination_'.$id.'" class="pager" >
					<form >
						<table  style="width: 200px;"><tr>
						<td><select class="pagesize" >
							<option selected="selected"  value="10">10</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option value="40">40</option>
							<option value="50">50</option>
							<option value="60">60</option>
							<option value="70">70</option>
							<option value="80">80</option>
							<option value="90">90</option>
							<option value="100">100</option>
							</select>
					    </td>
						</tr>
						</table>
					</form>
			</div>	';
    	/* end define string*/
    	
    	$head='<form name="list"><table class="collape tablesorter" id="'.$id.'" width="100%">';
    	$col_str='';
    	$col_str .='<thead><tr>';
    	$col_str .= '<th class="tdheader">'.$tr->translate("NUM").'</th>';
    	//add columns
    	foreach($columns as $column){
    		$col_str=$col_str.'<th class="tdheader">'.$tr->translate($column).'</th>';
    	}
    	$col_str.='</tr></thead>';
    	$row_str='<tbody>';
    	//add element rows	
    	if($rows==NULL) return $head.$col_str.'</table><center style="font-size:18pt;">No record</center></form>';
    	$temp=0;
    	/*------------------------Check param id----------------------------------*/

    	/*------------------------End check---------------------------------------*/
    	$r=0;
    	foreach($rows as $row){
    		if($r%2==0)$attb='normal';
    		else $attb='alternate';
    		$r++;
	    		//-------------------check select-----------------

    		//-------------------end check select-----------------
    		$row_str.='<tr class="'.$attb.'"> ';
    				$i=0;
		  			foreach($row as $key=>$read) {
		  				if($read==null) $read='&nbsp';
		  				if($i==0) {
		  					$temp=$read;
		  					$row_str.='<td class="items-no">'.$r.'</td>';
		  				} else {
    						if($link!=null){
    							foreach($link as $column=>$url)
    								if($key==$column){
    									$img='';
    									$array=array('tag'=>'a','attribute'=>array('href'=>Application_Form_FrmMessage::redirectorview($url).'?id='.$temp));
    									$read=$this->formSubElement($array,$img.$read);
    								}
    						}
    						$text='';
    						if($i!=1){
	    						$text=$this->textAlign($read);
	    						$read=$this->checkValue($read);

	    						if($textalign != 'left'){
	    							$text  = " align=". $textalign;
	    						}
    						}
    						$row_str.='<td class="'.$class.'" '.$text.'>'.$read.'</td>';
			  				if($i == count($columns)) {
	    					}
    					}
    					$i++;
		  			}
 			$row_str.='</tr>';
    	}
    	$counter='<span class="row_num">'.$tr->translate('NUM-RECORD').count($rows).'</span>';
    	$row_str.='</tbody>';
    	$footer='</table></form>';
    	if(!$report){
    		$footer .= '<div class="footer_list">'.$stringPagination.$counter.'<div>';
    	}
    	return $head.$col_str.$row_str.$footer;
    }
    public function formSubElement($array,$element='')
    {
    	$stat='';
    	foreach($array as $tag=>$name){
    		if($tag=='tag'){
    			$stat.='<'.$name.' ';
    			$closetag='</'.$name.'>';
    		}
    		else
    			foreach($name as $att=>$value)
    			$stat.=$att.'="'.$value.'" ';
    	}
    	$stat.=">".$element.$closetag;
    	return $stat;
    }
    private function textAlign($value){
    	$temp=str_replace(',','', $value);
    	if($this->is_date($temp) || strtolower($temp) == "yes" || strtolower($temp) == "no" ) return  'style="text-align:center"';
    	else{
    		$temp=explode('-', $value);
    		if(count($temp)>2){
    			if(is_numeric($temp[0]) && is_numeric($temp[2])){
    				if(!is_numeric($temp[1]) && strlen($temp[1])==3) return 'style="text-align:center"';
    			}
    		}
    		$pos = strpos($value, "class=\"colorcase");
    		if($pos){
    			return 'style="text-align:center"';
    		}
    	}
    	return '';
    }
    public function is_date($str)
    {
    	try{
    		$temp=explode('-', $str);
    		if(is_array($temp) && count($temp)>=3){
    			if(is_numeric($temp[0]) && is_numeric($temp[1]) && is_numeric(substr($temp[2],0,2))){
    
    				$d=substr($temp[2],0,2);
    				 
    				$m=$temp[1];
    				$y=$temp[0];
    				if(checkdate($m, $d, $y)) return true;
    			}
    		}
    		return false;
    	}catch(Zend_Exception $e){
    		return false;
    	}
    }
    public function checkValue($value){
    	//Sophen comment for number format
    	 
    	if($this->is_date($value)) return date_format(date_create($value), 'd-M-Y');
    	return $value;
    }
}

