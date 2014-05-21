<?php

class MainPageDMaster extends MainPage {	
    /**
     *
     * object rekening
     */
    public $rekening; 
    /**
     *
     * object dmaster
     */
    public $dmaster;
    /**
	* tab bagian
	*/
	public $showBagian=false;
    /**
	* tab unit kerja
	*/
	public $showUnitKerja=false;
    /**
	* tab program
	*/
	public $showProgram=false;	
    /**
	* tab Rekening
	*/
	public $showRekening=false;	
    /**
	* tab Transaksi
	*/
	public $showTransaksi=false;	
    /**
	* tab Kelompok
	*/
	public $showKelompok=false;	
    /**
	* tab Jenis
	*/
	public $showJenis=false;	
    /**
	* tab Objek
	*/
	public $showObjek=false;	
    /**
	* tab Rincian
	*/
	public $showRincian=false;   
    /**
	* label print out
	*/
	protected $labelPrintout='';
	public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->showDMaster=true;      
        $this->dmaster=$this->getLogic('DMaster');
        $this->createObjKegiatan();        
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
	}
    public function createObjRekening() {
        $this->rekening=$this->getLogic('Rekening');
    }
    public function showPrintOutModal ($sender,$param) {
        $this->lblPrintout->Text=$this->labelPrintout;
        $this->modalPrintOut->show();
    }
    public function closePrintOutModal ($sender,$param) {        
        $this->modalPrintOut->hide();
        $this->redirect($this->Page->getPagePath());
    }
    public function resetModalPrintOut ($sender,$param) {
        $this->modalPrintOut->Attributes->style = "";
        $this->modalPrintOut->render($param->NewWriter);
    }
    public function printRekening ($jenisrekening) {
        switch ($this->report->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->report->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->report->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                $row=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:C$row");				                                
                $this->report->rpt->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'DAFTAR REKENING '.strtoupper($jenisrekening));
                $styleArray=array( 
								'font' => array('bold' => true,'size'=>'11'),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),				                                
							);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:C$row")->applyFromArray($styleArray);
                switch ($jenisrekening) {
                    case 'transaksi' :
                        $str = "SELECT no_rek1 AS no_rek,nama_rek1 AS nama_rek FROM rek1 ORDER BY no_rek1 ASC";                        
                    break;
                    case 'kelompok' :
                        $str = "SELECT no_rek2 AS no_rek,nama_rek2 AS nama_rek FROM rek2 ORDER BY no_rek2 ASC";                        
                    break;
                    case 'jenis' :
                        $str = "SELECT no_rek3 AS no_rek,nama_rek3 AS nama_rek FROM rek3 ORDER BY no_rek3 ASC";                        
                    break;
                    case 'objek' :
                        $str = "SELECT no_rek4 AS no_rek,nama_rek4 AS nama_rek FROM rek4 ORDER BY no_rek4 ASC";                        
                    break;
                    case 'rincian' :
                        $str = "SELECT no_rek5 AS no_rek,nama_rek5 AS nama_rek FROM rek5 ORDER BY no_rek5 ASC";
                        
                    break;
                }
                
                $this->report->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(7);
                $this->report->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(17);
                $this->report->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(60);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'NO');	
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'KODE REKENING');	
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'NAMA REKENING');	
                $styleArray=array( 
								'font' => array('bold' => true,'size'=>'11'),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),				
                                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))   
							);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:C$row")->applyFromArray($styleArray);
                $row+=1;
                $row_awal=$row;                
                $this->DB->setFieldTable(array('no_rek','nama_rek'));                        
                $r=$this->DB->getRecord($str);
                while (list($k,$v)=each($r)) {
                    $this->report->rpt->getActiveSheet()->setCellValue("A$row",$v['no']);	
                    $this->report->rpt->getActiveSheet()->setCellValue("B$row",$v['no_rek']);	
                    $this->report->rpt->getActiveSheet()->setCellValue("C$row",$v['nama_rek']);	
                    $row+=1;
                }
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:C$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:C$row")->getAlignment()->setWrapText(true);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("C$row_awal:C$row")->applyFromArray($styleArray);
                
                $this->report->printOut("rekening_$jenisrekening");                
                $this->report->setLink($this->linkOutput,'Daftar  Rekening '.ucfirst($jenisrekening));
            break;
        }
    }
}
?>