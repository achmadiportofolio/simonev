<?php
prado::using ('Application.pages.s.dashboard.MainPageDashboard');
class Home extends MainPageDashboard {
	public function onLoad($param) {		
		parent::onLoad($param);	        
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageHome'])||$_SESSION['currentPageHome']['page_name']!='s.Home') {
                $_SESSION['currentPageHome']=array('page_name'=>'s.Home','page_num'=>0);												
			} 
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();            
            $this->toolbarOptionsBulanRealisasi->Enabled=false;                    
            $this->populateData ();
		}
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
        $this->redirect('s.Home');
	}
    protected function populateData () {        
        $ta=$this->session['ta'];
        $userid=$this->userid;        
        $str = "SELECT COUNT(DISTINCT(p.idproyek)) AS belumterealisasi FROM proyek p LEFT JOIN uraian u ON(p.idproyek=u.idproyek) LEFT JOIN penggunaan pe ON (pe.iduraian=u.iduraian) WHERE p.tahun_anggaran=$ta AND pe.realisasi IS NULL AND p.userid=$userid";
        $this->DB->setFieldTable(array('belumterealisasi'));
        $r=$this->DB->getRecord($str);
        $this->dataKegiatan['belumterealisasi']=$r[1]['belumterealisasi'];
        $this->dataKegiatan['totalkegiatan']=$this->DB->getCountRowsOfTable ("proyek WHERE tahun_anggaran=$ta AND userid=$userid",'idproyek');
        $this->dataKegiatan['terrealisasi']=$this->dataKegiatan['totalkegiatan']-$this->dataKegiatan['belumterealisasi'];
        $totalnilaipagu=$this->DB->getSumRowsOfTable('nilai_pagu',"proyek WHERE tahun_anggaran=$ta AND userid=$userid");
        $this->dataKegiatan['totalpaguanggaran']=$this->finance->toRupiah($totalnilaipagu);
        $this->dataKegiatan['totalpaguanggaran']=$this->dataKegiatan['totalpaguanggaran'].' ('.$this->finance->baca($totalnilaipagu).')';
        $totalrealisasi=$this->DB->getSumRowsOfTable('realisasi',"v_laporan_a WHERE tahun_anggaran=$ta AND userid=$userid");
        $this->dataKegiatan['totalrealisasi']=$this->finance->toRupiah($totalrealisasi);
        $this->dataKegiatan['totalrealisasi']=$this->dataKegiatan['totalrealisasi'].' ('.$this->finance->baca($totalrealisasi).')';
        $persenrealisasi=$totalrealisasi>0?round(($totalrealisasi/$totalnilaipagu)*100,2):0;
        $this->dataKegiatan['persenrealisasi']=$persenrealisasi;        
    }    
    
}  
		