<?php
prado::using ('Application.pages.m.report.MainPageReports');
class Reports extends MainPageReports {
    public $dataRekapKegiatan=array();
    public $dataKegiatanSudahDilelang=array();
    public $dataKegiatanProsesLelang=array();
    public $dataKegiatanBelumDilelang=array();
    public function onLoad($param) {		
		parent::onLoad($param);				
		if (!$this->IsPostBack&&!$this->IsCallBack) {            
            if (!$this->IsPostBack&&!$this->IsCallBack) {
                if (!isset($_SESSION['currentPageReports'])||$_SESSION['currentPageReports']['page_name']!='m.Reports') {
                    $_SESSION['currentPageReports']=array('page_name'=>'m.Reports','page_num'=>0,'dataReport'=>array());												
                }
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
        $this->redirect('t.Reports');
        //$this->populateData();               
	}   
    protected function populateData () {
        $ta=$this->session['ta'];
        $str = "SELECT  jp, COUNT(iduraian) AS totaluraian FROM proyek p,uraian u WHERE p.idproyek=u.idproyek AND p.tahun_anggaran=$ta AND jp!='none' GROUP BY jp";
        $this->DB->setFieldTable(array('jp','totaluraian'));
        $r=$this->DB->getRecord($str);                      
        $this->dataRekapKegiatan=$this->getReport($r);
        $_SESSION['currentPageReports']['dataReport']['rekapkegiatan']=$this->dataRekapKegiatan;
        $str = "SELECT  jp, COUNT(iduraian) AS totaluraian FROM proyek p,uraian u WHERE p.idproyek=u.idproyek AND p.tahun_anggaran=$ta AND jp!='none' AND u.status_lelang=1 GROUP BY jp";
        $this->DB->setFieldTable(array('jp','totaluraian'));
        $r=$this->DB->getRecord($str);               
        $this->dataKegiatanSudahDilelang=$this->getReport($r);
        $_SESSION['currentPageReports']['dataReport']['sudahdilelang']=$this->dataKegiatanSudahDilelang;
        
        $str = "SELECT  jp, COUNT(iduraian) AS totaluraian FROM proyek p,uraian u WHERE p.idproyek=u.idproyek AND p.tahun_anggaran=$ta AND jp!='none' AND u.status_lelang=2 GROUP BY jp";
        $this->DB->setFieldTable(array('jp','totaluraian'));
        $r=$this->DB->getRecord($str);               
        $this->dataKegiatanProsesLelang=$this->getReport($r);
        $_SESSION['currentPageReports']['dataReport']['proseslelang']=$this->dataKegiatanProsesLelang;
        
        $str = "SELECT  jp, COUNT(iduraian) AS totaluraian FROM proyek p,uraian u WHERE p.idproyek=u.idproyek AND p.tahun_anggaran=$ta AND jp!='none' AND u.status_lelang=0 GROUP BY jp";
        $this->DB->setFieldTable(array('jp','totaluraian'));
        $r=$this->DB->getRecord($str);               
        $this->dataKegiatanBelumDilelang=$this->getReport($r);
        $_SESSION['currentPageReports']['dataReport']['belumdilelang']=$this->dataKegiatanBelumDilelang;   
        
        $this->reportChart->ImageUrl=$this->Service->constructUrl ('t.ReportChart'); 
    }
    private function getReport ($r) {
        $data=array('plfisik'=>0,'plperencanaan'=>0,'plpengawasan'=>0,'plpengadaan'=>0,'lelangfisik'=>0,'lelangperencanaan'=>0,'lelangpengawasan'=>0,'lelangpengadaan'=>0);
        foreach ($r as $v) {            
            switch ($v['jp']) {
                case 'plfisik' :                    
                    $data['plfisik']=$v['totaluraian'];
                break;
                case 'plperencanaan' :
                    $data['plperencanaan']=$v['totaluraian'];
                break;
                case 'plpengawasan' :
                    $data['plpengawasan']=$v['totaluraian'];
                break;
                case 'plpengadaan' :
                    $data['plpengadaan']=$v['totaluraian'];
                break;
                case 'lelangfisik' :
                    $data['lelangfisik']=$v['totaluraian'];
                break;
                case 'lelangperencanaan' :
                    $data['lelangperencanaan']=$v['totaluraian'];
                break;
                case 'lelangpengawasan' :
                    $data['lelangpengawasan']=$v['totaluraian'];
                break;
                case 'lelangpengadaan' :
                    $data['lelangpengadaan']=$v['totaluraian'];
                break;
            }
        }
        return $data;
    }
}
		