<?php

class MainPageReports extends MainPage {
    /**
	* tab form target & realisasi penerimaan pendapatan
	*/
	public $showFormRealisasi=false;	
    /**
	* tab form a
	*/
	public $showFormA=false;	
    /**
	* tab form b
	*/
	public $showFormB=false;
    /**
	* tab pelaksanaan anggaran
	*/
	public $showPelaksanaanAnggaran=false;
    /**
	* tab jenis kegiatan
	*/
	public $showJenisPelaksanaan=false;
    /**
	* label print out
	*/
	protected $labelPrintout='';
    /**
     * 
     * @param type $param
     */
	public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->showReports=true;              
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
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
    public function showPrintOutRekapitulasi ($sender,$param) {
        $this->lblPrintoutRekapitulasi->Text=$this->labelPrintout;
        $this->modalPrintOutRekapitulasi->show();
    }
    public function closePrintOutRekapitulasi ($sender,$param) {                                
        $this->modalPrintOutRekapitulasi->hide();        
        $this->redirect($this->Page->getPagePath());
    }
    public function resetModalPrintOutRekapitulasi ($sender,$param) {
        $this->modalPrintOutRekapitulasi->Attributes->style = "";
        $this->modalPrintOutRekapitulasi->render($param->NewWriter);
    }
}
?>