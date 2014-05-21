<?php
prado::using ('Application.pages.s.pendapatan.MainPagePendapatan');
class Pendapatan extends MainPagePendapatan {
    public $totalAllKegiatan=0;
    public $totalAllPaguAnggaran=0;
    public $totalAllRealisasi=0;    
	public function onLoad($param) {		
		parent::onLoad($param);			                
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePendapatan'])||$_SESSION['currentPagePendapatan']['page_name']!='s.Pendapatan') {
                $_SESSION['currentPagePendapatan']=array('page_name'=>'s.Pendapatan','page_num'=>0,'dataKegiatan'=>array());												
            }
            
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();
            
            $this->populateData ();	
		}
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
        $this->populateData ();
	}
    public function changeBulanRealisasi ($sender,$param) {	
        $_SESSION['bulanrealisasi']=$this->toolbarOptionsBulanRealisasi->Text;
        $this->populateData ();
	}
    protected function populateData () {
        $ta=$this->session['ta'];     
        $idunit=$this->idunit;		
		$no_bulan = $this->session['bulanrealisasi'];       
    }      
}
		