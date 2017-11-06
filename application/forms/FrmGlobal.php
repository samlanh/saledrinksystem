<?php

class Application_Form_FrmGlobal
{
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */  
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    
    /**
     * get array of label of each chilsafe type
     * @return array $arr_type
     */
    public function getArrCsType(){
    	$arr_type = array(
    			"fi_cs_type" => "CS-TYPE",
    			"fi_cs_zone" => "CS-ZONE",
    			"fi_cs_items" => "CS-ITEMS",
    			"fi_cs_subject" => "CS-SUBJECT",
    			"fi_cs_living_situation" => "CS-LIVING",
    			"fi_cs_ass_provided" => "CS-ASS-PROVIDED",
    			"fi_cs_ass_requested" => "CS-ASS-REQUESTED",
    			"fi_cs_current_condition" => "CS-CONDITION",
    			"fi_cs_referal" => "CS-REFERAL",
    			"fi_cs_type_caller" => "CS-TYPE-CALLER",
    			"fi_cs_type_case" => "CS-TYPE-CASE",
    			"fi_cs_ar_category" => "CS-AR-CATEGORY",
    			
    			"cs-refresh" => "CS-REFRESH",
    			"cs-member" => "CS-MEMBER"
    	);
    	return $arr_type;    	
    }
	
    /**
     * get dynamic form to edit, add new for submit data to server
     * @param string $action
     * @param string $method
     * @param array $elemets
     * @param array $hidenvalues  
     * @param string $type
     * @param string $page
     * @var $elemets Ex: array('label-element-1'=>type);
     * 				type tag html element
     * @return string
     */
    public function getForm($action, $method, $url_cancel, $elements, $legend = null, $hidenvalues = null, $type=null, $page=null, $tableadd=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	if($type!=null && $page!=null){
    		$arr_type = $this->getArrCsType();
    	}
    	
    	$script= '<script type="text/javascript">
						jQuery(document).ready(function(){
							//binds form submission and fields to the validation engine
							jQuery("#frm").validationEngine();
						});
					</script>';
    	$form = "<form  id=\"frm\" method='". $method ."' action='". $action ."' accept-charset=\"utf-8\" enctype=\"multipart/form-data\" style=\"position:relative;\"> ";
    	
    	$form .= '<div class="btn" align="right">
				    <button type="submit" class="positive">
				        <img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>
				        Save
				    </button>
				    <a href="'. $url_cancel .'" class="negative">
				        <img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
				        Cancel
				    </a>
			   </div>
				<fieldset>
					<legend>'.$tr->translate($legend).'</legend>
					<table>';
						
		foreach ($elements as $lbl => $element){
			$form .= '<tr>
						<td class="field">'. $tr->translate($lbl) .'</td>
						<td class="add-edit">'. $element .'</td>
					</tr>';
		}
		$form .= '</table>';
		if($tableadd!=null){
			$form .= $tableadd;
		}
		$form .= '</fieldset>';
		if(!empty($hidenvalues)){
			foreach ($hidenvalues as $i =>$h){
				$form .= $h;
			}
		}
    	
    	$form .= "</form>";
    	return $form ;
    }
    
    //sopharat create getForm1
    public function getForm1($action, $method, $url_cancel, $elements, $legend = null, $hidenvalues = null, $type=null, $page=null, $tableadd=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	if($type!=null && $page!=null){
    		$arr_type = $this->getArrCsType();
    	}
    	 
    	$script= '<script type="text/javascript">
				    	jQuery(document).ready(function(){
					    	//binds form submission and fields to the validation engine
					    	jQuery("#frm").validationEngine();
				   		 });
				    </script>';
    	$form = "<form  id=\"frm\" method='". $method ."' action='". $action ."' accept-charset=\"utf-8\" enctype=\"multipart/form-data\" style=\"position:relative;\"> ";
    	 
    	$form .= '<div class="btn" align="right">
				    	<button type="submit" class="positive">
					    	<img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>
					    	Save
				    	</button>
				    	<a href="'. $url_cancel .'" class="negative">
					    	<img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
					    	Cancel
				    	</a>
			    	</div>
			    	<div class="de-font">
				       	<div class="view-table">
				       		<div class="head_form">
				        		'.$tr->translate($legend).'
				        	</div>
				        	<div class="contain_form">
		    					<table style="width:50%">';
    
    	foreach ($elements as $lbl => $element){
    		$form .= '<tr>
    		<td class="field">'. $tr->translate($lbl) .'</td>
    		<td>'. $element .'</td>
    		</tr>';
    	}
    	$form .= '</table>';
    	if($tableadd!=null){
    		$form .= $tableadd;
    	}
    	$form .= '</div><!-- end .contain_form -->
			       	</div><!-- end of .view-table -->
				</div><!-- end of .de-font -->';
    	if(!empty($hidenvalues)){
    		foreach ($hidenvalues as $i =>$h){
    			$form .= $h;
    		}
    	}
    	 
    	$form .= "</form>";
    	return $form ;
    }
    
    /**
     * 
     * @param string $frm_name
     * @param string $action
     * @param string $method
     * @param string $url_cancel
     * @param array $multi_legend
     * @param string $hidenvalues=null
     * @param string $type=null
     * @param string $page=null
     * @param string $tableadd=null
     * @return string
     */
    public function getMultiLegendForm($frm_name,$action, $method, $url_cancel, $multi_legend, $hidenvalues = null, $type=null, $page=null, $tableadd=null, $url_edit=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$legend = '';
    	if($type!=null && $page!=null){
    		$arr_type = $this->getArrCsType();
    		$legend = '<legend>'.$tr->translate($page).' '.$tr->translate($arr_type[$type]).'</legend>';
    	}
    	$script = '';
    	$form = '';
    	
    	if($page=="VIEW"){
    		
    		$form .= '<div class="btn" align="right">
    					<a class="negative" href="'.$url_edit.'">
					        <img alt="" src="'. BASE_URL .'/images/icon/edit.png">
					        Edit
					    </a>
				    	<a href="'. $url_cancel .'" class="negative">
					    	<img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
					    	Cancel
				    	</a>
				    	<a href="" onclick="printDiv(&#39;printreport&#39;)"><img alt="" src="'.BASE_URL.'/images/icon/print.png">'.$tr->translate("PRINT").'</a>
				    </div>
	    	<fieldset>
	    	'.$legend;
    	}else{
	    	$script= '<script type="text/javascript">
				    	jQuery(document).ready(function(){
					    	//binds form submission and fields to the validation engine
					    	jQuery("#'.$frm_name.'").validationEngine();
					    });
				    </script>';
	    	$form = "<form  id=\"".$frm_name."\" method='". $method ."' action='". $action ."' accept-charset=\"utf-8\" enctype=\"multipart/form-data\" style=\"position:relative;\"> ";
	    	 
	    	$form .= '<div class="btn" align="right">
				    	<button type="submit" class="positive">
					    	<img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>
					    	Save
				    	</button>
				    	<a href="'. $url_cancel .'" class="negative">
					    	<img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
					    	Cancel
				    	</a>
				    </div>
	    	<fieldset>
	    	'.$legend;
    	}
    
    	$arr_int = array("1","2","3","4","5","6","7","8","9","10");
    	
    	//For to keep print div
    	if($page=="VIEW"){
    		$form .='<div id="printreport">';
    	}
    	
    	foreach($multi_legend as $key=>$elements){
    		if(is_array($elements)){
	    		$key_legend = (in_array($tr->translate($key), $arr_int))? '':'<legend>'.$tr->translate($key).'</legend>';
	    		$form .= '<fieldset>'.$key_legend;
	    		$form .= '<table>';
		    	foreach ($elements as $lbl => $element){
		    		$form .= '<tr>
					    		<td class="field-member">'. $tr->translate($lbl) .'</td>
					    		<td class="add-edit">'. $element .'</td>
					    	</tr>';
		    	}
		    	$form .=	'</table></fieldset>';
	    	}else{
	    		$form .='<br/>'.$tr->translate($key);
    		}
    	}
    	if($tableadd!=null){
    		$form .= $tableadd;
    	}
   		 //For to keep print div
    	if($page=="VIEW"){
    		$form .="</div>";
    	}
    	
    	$form .= '</fieldset>';
    	
    	
    	
    	if(!empty($hidenvalues)){
    		foreach ($hidenvalues as $i =>$h){
    			$form .= $h;
    		}
    	}

    	//just update
    	if($page == 'editform1'){
    	}else $form .= "</form>";
    	
    	return $form. $script;
    }    
    
    /**
     * get dynamic form for page view
     * @param string $url_edit
     * @param string $url_cancel
     * @param array $elements
     * @param string $type
     * @return zendForm $form
     */
    public function getFormView($url_edit,$url_cancel, $elements,$title,$tableadd=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();   	
    	
    	$form = '<div class="btn" align="right">
			    	<a class="negative" href="'.$url_edit.'">
				        <img alt="" src="'. BASE_URL .'/images/icon/edit.png">
				        Edit
				    </a>
			    	<a href="'. $url_cancel .'" class="negative">
				    	<img src="'. BASE_URL .'/images/icon/previous.gif" alt=""/>
				    	Back
			    	</a>
    			</div>
    			<fieldset>
    				<legend>View '.$tr->translate($title).'</legend>
    				<table>';
    
    	foreach ($elements as $lbl => $element){
    		$form .= '<tr>
			    		<td class="field">'. $tr->translate($lbl) .'</td>
			    		<td class="value">'. $element .'</td>
    				</tr>';
    	}
    
    	$form .=	'</table>';
    	if($tableadd!=null){
    		$form .= $tableadd;
    	}
    	$form .='</fieldset>';
        	 
    	return $form;
    }

    /**
     * return table Formtableadd
     * @param array $columns
     * @param string $action_add_id
     * @param string $hidden_id
     * @param int $hidden_value
     * @return string
     */
    public function getFormtableadd($columns, $action_add_id, $num_row_id, $rows=null, $tbl_id = "tbl_answer", $page=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$num_row_value = ($rows!=null)? count($rows): 0;
    
    	$tbl = '<table class="collape tablesorter" id="'.$tbl_id.'" style="width: 100%">
    	<thead>
    	<tr>
    	';
    	foreach($columns as $col){
    		$tbl .= '		<th class="tdheader">'. $tr->translate($col).'</th>';
    	}
    	
    	$tbl .= '</tr>
    	</thead>
    	<tbody>';
    	if($num_row_value > 0){
    		$i=0;
    		foreach($rows as $ele){
				$i++;    			
    			$tbl .= "<tr>
    						<td align='center'>".$i."</td>
			    			<td class='input-add-1col' align='center'>
				    			<input type='text' class='validate[required] text-input'  id='member_".$i."' value='".$ele['member_name']."'/>
				    			<input type='hidden' name='member_id_".$i."' id='member_id_".$i."'  value='".$ele['member_id']."'>
				    			<input type='hidden' name='id_".$i."'  value='".$ele['id']."'>
		    				</td>
    					</tr>"; 
    		}	
    	}
    	$tbl .= '</tbody>
    	</table>';
    	if($page == null){
	    	$tbl .= '<div class="btn" align="left">
				    	<button type="button" class="positive" id="'.$action_add_id.'">
					    	<img src="'. BASE_URL .'/images/icon/add.png" alt=""/>
					    	Add
				    	</button>
				    	<input type="hidden" id="'.$num_row_id.'" name="'.$num_row_id.'" value="'.$num_row_value.'" />
			    	</div>';
    	}
    	return $tbl;
    }   

    /**
     * return table Viewtableadd
     * @param array $columns
     * @param array $data
     * @return string $tbl
     */
    public function getViewtableadd($columns, $data){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$no = 0;
    	
    	$tbl = '<table class="collape tablesorter" id="tbl_answer" style="width: 100%">
					<thead>
						<tr>';
    	foreach($columns as $col){
    		$tbl .= '		<th class="tdheader">'. $tr->translate($col).'</th>';
    	}
		$tbl .= '		</tr>
					</thead>
					<tbody>';
		if($data != ""){
			foreach($data as $d){ 
				$no++;
				$tbl .='	<tr>
								<td align="center">'.$no.'</td>';
				foreach($d as $ele){
					$tbl .='	<td>'.$ele.'</td>';
				}
				$tbl .='	</tr>';
			}
		}
		$tbl .='	</tbody>
				</table>';
		return $tbl;
    }
    
	 public function getOtherViewtableadd($columns, $data){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$no = 0;
    	
    	$tbl = '<table class="collape tablesorter" id="tbl_answer" style="width: 100%">
					<thead>
						<tr>';
    	foreach($columns as $col){
    		$tbl .= '		<th class="tdheader">'. $tr->translate($col).'</th>';
    	}
		$tbl .= '		</tr>
					</thead>
					<tbody>';
		if($data != ""){
			foreach($data as $d){ 
				$no++;
				$tbl .='	<tr>
								<td align="center">'.$no.'</td>';
				foreach($d as $ele){
					$tbl .='	<td>'.$ele.'</td>';
				}
				$tbl .='	</tr>';
			}
		}
		$tbl .='	</tbody>
				</table>';
		return $tbl;
    }
    
    /**
     * set element form for display in group
     * @param string $title
     * @param array $elements
     * @return string
     */
    public function setGroupDisplay($title, $elements){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$str= "<fieldset>
    				<legend>" . $title . "</legend>
    				<table>";
    	
    	foreach ($elements as $lbl => $element){
    		$str .= '<tr>
				    		<td class="field">'. $tr->translate($lbl) .'</td>
				    		<td class="add-edit">'. $element .'</td>
    				 </tr>';
    	}
    	$str .= "</table></fieldset>";
    	return $str;
    }
    
    /**
     * For Display form that have group diplay
     * @param string $title
     * @param array $elements
     * @param string $submit_url
     * @param string $url_cancel
     * @param array $hidenvalues
     * @return string
     */
    public function getFormGroupDisplay($title, $elements, $submit_url, $url_cancel, $hidenvalues=null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	
    	$form = "<form  id=\"frm\" method='POST' action='". $submit_url ."' accept-charset=\"utf-8\" enctype=\"multipart/form-data\" style=\"position:relative;\"> ";
    	 
    	$form .= '<div class="btn" align="right">
				    	<button type="submit" class="positive">
					    	<img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>'. $tr->translate('SAVE') .'
				    	</button>
				    	<a href="'. $url_cancel .'" class="negative">
					    	<img src="'.BASE_URL.'/images/icon/cross.png" alt=""/> '. $tr->translate('CANCEL') .'					    	
				    	</a>
			    	</div>    	
    	<table> <tr><td align="center"><h3>'.$title.'</h3></td></tr>';
    	
    	foreach ($elements as $lbl => $element){
    		$form .= '<tr>
		    			<td>'. $element .'</td>
		    		  </tr>';
    	}
    	
    	$form .=	'</table>';
    	
    	
    	
    	if(!empty($hidenvalues)){
    		foreach ($hidenvalues as $i =>$h){
    			$form .= $h;
    		}
    	}
    	 
    	$form .= "</form>";
    	return $form ;
    }
    
    public function getFormSaveCancel($url_cancel){
    	$form = '<div class="btn" align="right">
				    <button type="submit" class="positive">
				        <img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>
				        Save
				    </button>
				    <a href="'. $url_cancel .'" class="negative">
				        <img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
				        Cancel
				    </a>
			   </div>';
    	return $form;
    }
    
	public function getFormEditCancel($url_edit,$url_cancel){
    	$form = '<div class="btn" align="right">
				    <a href="'. $url_edit .'" class="positive">
				        <img src="'.BASE_URL.'/images/icon/edit.png" alt=""/>
				        Edit
				    </a>
				    <a href="'. $url_cancel .'" class="negative">
				        <img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
				        Cancel
				    </a>
			    </div>';
    	return $form;
    }
    
    /* @Desc: 
     * 
     * */
	public function showOrderDetail() {
	
	}
}

