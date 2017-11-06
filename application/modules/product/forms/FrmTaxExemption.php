<?php

class product_Form_FrmTaxExemption extends Zend_Form
{		
	private $fund_no='';
	private $division_id='';			
	private $counter=0;
	private $sql_currency;
	private $user_id='';
	public function getFundNo()
	{
		return $this->fund_no;
	}
	
	public function setFundNo($fund_no='',$user_id='',$division_id='') 
	{	
	    $this->fund_no=$fund_no;
	    $this->division_id=$division_id;
	    $this->user_id=$user_id;
	    $this->sql_currency="SELECT currency_type_id,abbreviation AS currency FROM pdbs_currency_type
							 WHERE currency_type_id=77 OR currency_type_id=159";
	}
	//get number of category
	public function getCounter(){
		return $this->counter;
	}	
	public function getForm($values=null)
    {
    	
        $pdbs=new Application_Model_Pdbs();
        /* Form Elements & Other Definitions Here ... */
        //set division
        $this->addElement($pdbs->getDivisionSelect($this->fund_no));
        //fund no
        $this->addElement($pdbs->getFundSelect('pdbs_tax_exemption',$this->division_id,$this->fund_no,$this->user_id));
        //ea no
        $this->addElement($pdbs->getEaSelect($this->fund_no));
        //ia no
        $this->addElement($pdbs->getIaSelect($this->fund_no));
        //sector
        $this->addElement($pdbs->getSubSectorSelect());       
        
   	    //company or firm name
	    	$firm=new Zend_Form_Element_Text('company_name');
	    	$firm->setAttribs(array('id'=>'company_name',	    			
	    				'class'=>'validate[required]', 							    		
	    		)
	    	);  
	    	$this->addElement($firm);
	    //type of import
	    	$type_of_import=new Zend_Form_Element_Select('type_of_import');
	    	$type_of_import->setMultiOptions(array('Permanent Import'=>'Permanent Import','Import Re-Export'=>'Import Re-Export'));
	    	$type_of_import->setAttribs(array('id'=>'type_of_import',    	 
	    						      'class'=>'validate[required]',   								   											    								
	    	));
	    	$this->addElement($type_of_import);
	    //taskforce
	    	$taskfoce=new Zend_Form_Element_Text('taskforce');
	    	$taskfoce->setAttribs(array('id'=>'taskforce',	    									    		
	    		)
	    	);  
	    	$this->addElement($taskfoce);	   	             
       //description
	    	$description=new Zend_Form_Element_Text('description');
	    	$description->setAttribs(array('id'=>'description',	    												    	
	    	));  
	    	$this->addElement($description);     
	    if($values!=null)$this->setForm($values); 	    	     			   		   		
	   //return value
	   		Application_Model_Decorator::removeAllDecorator($this);
	    	return $this;
	           			    	
    }    
    //set value or assign all element to form element
    public function setForm($arr_value)
    {    	
    	if($arr_value){    			
    		foreach($arr_value as $read)    		
    			foreach($read as $key=>$value)
		    		foreach($this->getElements() as $element){
		    			if($key==$element->getName()){		    						    				
		    				$element->setValue($value);
		    			}
		    		}    		
    	}
    }
    //set form detail for editing
    public function setFormDetail($arr)
    {
        $pdbs=new Application_Model_Pdbs();
    	$i=0;
    	if($arr){    		
    		$currency=$pdbs->getOption('currency', 'currency_type_id', $this->sql_currency);
    		unset($currency['']);
    		foreach($arr as $read){
    			$i++;
    			$id=new Zend_Form_Element_Hidden('id_'.$i);
    				$id->setValue($read['tax_exemption_detail_id']);
    			$date=new Zend_Form_Element_Text('date_'.$i);
    				$date->setValue(date_format(date_create($read['tax_date']),'d-m-Y'));
    				$date->setAttrib('class', 'validate[funcCall[dateDMY]]');
    			$import=new Zend_Form_Element_Text('des_import_goods_'.$i);
    				$import->setValue($read['des_import_goods']);
    				$import->setAttribs(array('class', 'validate[require]]','style'=>'text-align:left')); 
    			$quantity=new Zend_Form_Element_Text('quantity_'.$i);
    				//$quantity->setValue(number_format($read[ 'quantity']));
    				$quantity->setValue($read['quantity']);
    				$quantity->setAttrib('class', 'validate[require]]');
    			$value=new Zend_Form_Element_Text('value_'.$i);
    				$value->setValue(Application_Model_PdbsReport::formatCurrency($read['VALUE'],$read['currency']));
    				$value->setAttrib('class', 'validate[require]]');
    			$currency_type=new Zend_Form_Element_Select('currency_type_id_'.$i);
    				$currency_type->setMultiOptions($currency);
    				$currency_type->setAttrib('class', 'validate[require]]');
    				$currency_type->setValue($read['currency']);
    			$port_of_charge=new Zend_Form_Element_Text('port_of_discharge_'.$i);
    				//$port_of_charge->setValue(Application_Model_PdbsReport::formatCurrency($read['port_of_discharge'],$read['currency']));
    				$port_of_charge->setValue($read['port_of_discharge']);
    				$port_of_charge->setAttrib('class', 'validate[require]]');
    			$this->addElements(array($id,$date,$import,$quantity,$value,$currency_type,$port_of_charge));
    		}
    	}
    	$this->counter=$i;
    }
}

