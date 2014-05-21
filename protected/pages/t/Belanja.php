<?php
prado::using ('Application.pages.m.belanja.MainPageBelanja');
class Belanja extends MainPageBelanja {
    public $totalAllKegiatan=0;
    public $totalAllPaguAnggaran=0;
    public $totalAllRealisasi=0;    
	public function onLoad($param) {		
		parent::onLoad($param);			                
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageBelanja'])||$_SESSION['currentPageBelanja']['page_name']!='m.Belanja') {
                $_SESSION['currentPageBelanja']=array('page_name'=>'m.Belanja','page_num'=>0,'dataKegiatan'=>array());												
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
        
        $this->DB->setFieldTable (array('idprogram','kode_program','nama_program'));
		$str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$ta'";
        //daftar program pada unit
        $daftar_program=$this->DB->getRecord($str);	
        $result=array();
        while (list($k,$v)=each($daftar_program)) {
            $idprogram=$v['idprogram'];
            $total_kegiatan=$this->DB->getCountRowsOfTable("proyek WHERE idprogram=$idprogram",'idproyek','');
            $v['totalKegiatan']=$total_kegiatan;
            $jumlah_pagu_each_program=$this->DB->getSumRowsOfTable('nilai_pagu',"proyek WHERE idprogram='$idprogram'");
            $v['totalPagu']=$this->finance->toRupiah($jumlah_pagu_each_program);
            $total_realisasi=$this->DB->getSumRowsOfTable('realisasi',"v_laporan_a WHERE idprogram='$idprogram' AND bulan_penggunaan<='$no_bulan'");
            $v['totalRealisasi']=$this->finance->toRupiah($total_realisasi);
            $sisa_anggaran=$jumlah_pagu_each_program-$total_realisasi;
            $v['sisa_anggaran']=$this->finance->toRupiah($sisa_anggaran);
            
            $this->totalAllKegiatan+=$total_kegiatan;
            $this->totalAllPaguAnggaran+=$jumlah_pagu_each_program;
            $this->totalAllRealisasi+=$total_realisasi;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();
    }  
    public function detailsProgram ($sender,$param) {
        $id=$this->getDataKeyField($sender, $this->RepeaterS);
        if (!isset($_SESSION['currentPageKegiatan'])||$_SESSION['currentPageKegiatan']['page_name']!='m.belanja.Kegiatan') {
            $_SESSION['currentPageKegiatan']=array('page_name'=>'m.belanja.Kegiatan','page_num'=>0,'idprogram'=>'none','search'=>false,'userid'=>'none');												
        }
        $_SESSION['currentPageKegiatan']['idprogram']=$id;
        $this->kegiatan->redirect('m.belanja.Kegiatan');
    }    
}
		