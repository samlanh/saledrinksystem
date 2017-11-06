<?php
/** Error reporting */
error_reporting(E_ALL);

/** PHPExcel */
include_once  PUBLIC_PATH .'/js/analysis/Classes/PHPExcel.php';
/** Include path **/
//ini_set('include_path', ini_get('include_path'). ';' . PUBLIC_PATH .'/js/analysis/Classes/');

class Application_Model_ExportExcelClass  extends Zend_Db_Table_Abstract
{
	protected $row=0;
	protected $col=0;
	protected $active_sheet=0;
	protected $mun_sheet=0;
	protected $objPHPExcel;
	
	public function __construct(){
		
		// Create new PHPExcel object
		$this->objPHPExcel = new PHPExcel();
		
		// Set properties
		$this->objPHPExcel->getProperties()->setCreator("Resolvo")
			->setLastModifiedBy("Maarten Balliauw")
			->setTitle("Office 2007 XLSX Test Document")
			->setSubject("Office 2007 XLSX Test Document")
			->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
			->setKeywords("office 2007 openxml php")
			->setCategory("Test result file");		
		
		parent::__construct();
	}
	
	public function setDataActiveSheet($rs, $space=2,$inx=null){
		// Add some data
		$objCell=$this->objPHPExcel->setActiveSheetIndex($this->active_sheet);

		$col = array_keys($rs[0]);
		if($col){
			for($i=0;$i<count($col);$i++){
				$objCell->setCellValue($this->getCol($this->row + 1, $i+1),$col[$i]);
			}
		}
		if($rs){
			$i= $this->row + 1;
			foreach($rs as $row){
				$i++;$j=0;
				foreach($row as $key=>$value){
					$j++;
					$objCell->setCellValue($this->getCol($i,$j),$value);
				}
			}
		}
		
		$this->row += count($rs) + 1 + $space;
		$this->col += count($rs[0]);
	}
	
	
	private function getCol($row,$col){
		if($col<=24)
			return chr(64+$col).$row;
		elseif($col>24 && $col<=48)
			return 'A'.chr((64-24)+$col).$row;
	}
	
	public function setTitleSheet($title){		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$this->objPHPExcel->setActiveSheetIndex($this->active_sheet);
		
		// Rename sheet
		$this->objPHPExcel->getActiveSheet()->setTitle($title);		
	}
	
	public function addNewSheet($title_sheet){
		$this->active_sheet++;
		$this->objPHPExcel->createSheet();
		$this->setTitleSheet($title_sheet);
		$this->row = 0;
		$this->col = 0;
	}
	
	public  function downloads($file_title){
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$file_title.'.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	
}

