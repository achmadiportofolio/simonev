<?php
prado::using ('Application.pages.m.belanja.MainPageBelanja');
class Realisasi extends MainPageBelanja  {
    public $jumlahBobot;
	public function onLoad ($param) {		
		parent::onLoad ($param);           
        $this->showRealisasi=true;	
		if (!$this->IsPostBack && !$this->IsCallback) {	
            $iduraian=addslashes($this->request['id']);
            if (!isset($_SESSION['currentPageRealisasi'])||$_SESSION['currentPageRealisasi']['page_name']!='m.belanja.Realisasi') {
                $_SESSION['currentPageRealisasi']=array('page_name'=>'m.belanja.Realisasi','page_num'=>0,'dataUraian'=>array(),'viewindex'=>0);												
			}  
			$str = "SELECT p.idproyek,u.iduraian,p.kode_proyek,p.nama_proyek,p.nilai_pagu,p.tahun_anggaran,u.rekening,u.nama_uraian,volume,satuan,harga_satuan,nilai,u.idlok,u.ket_lok,u.nama_perusahaan,u.tgl_kontrak,u.tgl_mulai_pelaksanaan,u.tgl_selesai_pelaksanaan FROM uraian u,proyek p WHERE u.idproyek=p.idproyek AND iduraian='$iduraian'";
			$this->DB->setFieldTable(array('idproyek','iduraian','kode_proyek','nama_proyek','nilai_pagu','tahun_anggaran','rekening','nama_uraian','volume','satuan','harga_satuan','nilai','idlok','ket_lok','nama_perusahaan','tgl_kontrak','nama_perusahaan','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan'));
			$result=$this->DB->getRecord($str);		
            if (isset($result[1])) {                             
                $this->RelasiTabPanel->ActiveViewIndex=$_SESSION['currentPageRealisasi']['viewindex'];
    			$result[1]['tgl_kontrak']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_kontrak']);
                $result[1]['tgl_mulai_pelaksanaan']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_mulai_pelaksanaan']);
    			$result[1]['tgl_selesai_pelaksanaan']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_selesai_pelaksanaan']);
    			$_SESSION['currentPageRealisasi']['dataUraian']=$result[1];	 			
                $this->uraianAnchor->NavigateUrl=$this->Service->constructUrl('m.belanja.Uraian',array('id'=>$result[1]['idproyek']));
                $this->uraianAnchor2->NavigateUrl=$this->Service->constructUrl('m.belanja.Uraian',array('id'=>$result[1]['idproyek']));
                $this->realisasiAnchor->NavigateUrl=$this->Service->constructUrl('m.belanja.Realisasi',array('id'=>$iduraian));                
    			$this->populateData();
                $this->populatePermasalahan();
                $this->populatePhoto();
            }else {
                unset($_SESSION['currentPageRealisasi']['dataUraian']);                
                $this->idProcess='view';                
            }
        }
	}	
    public function panelViewChanged ($sender,$param) {
        $iduraian=$_SESSION['currentPageRealisasi']['dataUraian']['iduraian'];
        switch ($sender->getId()) {
            case 'chkrealisasi' :
                $_SESSION['currentPageRealisasi']['viewindex']=0;
            break;
            case 'chkpermasalahan' :
                $_SESSION['currentPageRealisasi']['viewindex']=1;
            break;
            case 'chkphoto' :
                $_SESSION['currentPageRealisasi']['viewindex']=2;
            break;
        }
        $this->kegiatan->redirect('m.belanja.Realisasi',array('id'=>$iduraian));				
    }
    protected function populateData() {
        $iduraian=$_SESSION['currentPageRealisasi']['dataUraian']['iduraian'];
		$str="SELECT idpenggunaan,nilai,bulan,tahun,target,realisasi,fisik FROM penggunaan p,uraian u WHERE u.iduraian=p.iduraian AND p.iduraian='$iduraian' ORDER BY bulan ASC";
		$this->DB->setFieldTable(array('idpenggunaan','nilai','bulan','tahun','target','realisasi','fisik'));		
		$r=$this->DB->getRecord($str);
		$finance=$this->finance;
		$bulan=$this->TGL->getMonth(3);		
        $result=array();
        while (list($k,$v)=each($r)) {
            $target=$target+$v['target'];
            $realisasi=$realisasi+$v['realisasi'];
            $dpa=$v['nilai']-$target;			
            $kas=$target-$realisasi;
            $v['bulan']=$bulan[$v['bulan']];
            $v['dpa']=$finance->toRupiah($dpa);
            $v['kas']=$finance->toRupiah($kas);
            $v['realisasi']=$finance->toRupiah($v['realisasi']);
            $v['target']=$finance->toRupiah($v['target']);
            $v['nilai']=$finance->toRupiah($v['nilai']);
            $result[$k]=$v;	
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();
		
	}  
    public function populatePermasalahan () {
        $iduraian=$_SESSION['currentPageRealisasi']['dataUraian']['iduraian'];
		$str="SELECT idproblem,tanggal,judul FROM masalah_realisasi WHERE iduraian='$iduraian' ORDER BY tanggal DESC";
		$this->DB->setFieldTable(array('idproblem','tanggal','judul'));		
		$r=$this->DB->getRecord($str);
        
        $this->RepeaterPermasalahan->DataSource=$r;
        $this->RepeaterPermasalahan->dataBind();
    }
    public function populatePhoto() {
        $iduraian=$_SESSION['currentPageRealisasi']['dataUraian']['iduraian'];
        $str = "SELECT idphoto,bulan,nama_photo,keterangan FROM realisasi_photo WHERE iduraian=$iduraian";
        $this->DB->setFieldTable(array('idphoto','bulan','nama_photo','keterangan'));		
		$r=$this->DB->getRecord($str);
        $this->RepeaterPhoto->DataSource=$r;
        $this->RepeaterPhoto->dataBind();
    }    
}

?>