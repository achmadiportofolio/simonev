<?php
prado::using ('Application.pages.s.report.MainPageReports');
class formADetails extends MainPageReports {	
    public $dataUraian;
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showFormA=true;
        $this->createObjFinance();
        $this->createObjKegiatan();
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (isset($this->session['currentPageFormA']['dataKegiatan']['idproyek'])) {                
                $this->idProcess='view';
                $this->initialization ();
            }else {
                $this->kegiatan->redirect('s.report.formA');
            }
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth ();
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();
		}		
	}   
    private function initialization () {        
        $idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];
        $iduraian=addslashes($this->request['id']);    
        $str = "SELECT iduraian,rekening,nama_uraian,idlok,ket_lok,nilai,no_kontrak,nama_perusahaan,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,status_lelang FROM uraian WHERE iduraian=$iduraian AND idproyek=$idproyek";
        $this->DB->setFieldTable (array('iduraian','rekening','nama_uraian','idlok','ket_lok','nilai','no_kontrak','nama_perusahaan','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','status_lelang'));	        
		$r=$this->DB->getRecord($str);           
        if (isset($r[1])) {
            $this->dataUraian=$r[1];
            $this->dataUraian['lokasi']=$this->kegiatan->getLokasiProyek (null,'lokasi',$this->dataUraian['idlok'],$this->dataUraian['ket_lok']);
            $_SESSION['currentPageFormADetails']=$this->dataUraian;
            $this->populatePermasalahan();
            $this->populatePhoto();
        }else {
            unset($_SESSION['currentPageFormADetails']);
            $this->kegiatan->redirect('s.report.formA');
        }
    }    
    public function populatePermasalahan () {
        $iduraian=$_SESSION['currentPageFormADetails']['iduraian'];
		$str="SELECT idproblem,tanggal,judul FROM masalah_realisasi WHERE iduraian='$iduraian' ORDER BY tanggal DESC";
		$this->DB->setFieldTable(array('idproblem','tanggal','judul'));		
		$r=$this->DB->getRecord($str);
        
        $this->RepeaterPermasalahan->DataSource=$r;
        $this->RepeaterPermasalahan->dataBind();
    }
    public function populatePhoto() {
        $iduraian=$_SESSION['currentPageFormADetails']['iduraian'];
        $str = "SELECT idphoto,bulan,nama_photo,keterangan FROM realisasi_photo WHERE iduraian=$iduraian";
        $this->DB->setFieldTable(array('idphoto','bulan','nama_photo','keterangan'));		
		$r=$this->DB->getRecord($str);
        $this->RepeaterPhoto->DataSource=$r;
        $this->RepeaterPhoto->dataBind();
    }
}
?>

