<?php
prado::using ('Application.pages.m.belanja.MainPageBelanja');
class Uraian extends MainPageBelanja {	
    public $invalidIDProyekMessage=false;
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showUraian=true;                
		if (!$this->IsCallback&&!$this->IsPostback) {				            
            if ($_SESSION['currentPageUraian']['RencanaTargetFisikPage']) {
                $this->idProcess='view';                
                $this->processRencanaTargetFisik();                
            }else {
                if (!isset($_SESSION['currentPageUraian'])||$_SESSION['currentPageUraian']['page_name']!='m.belanja.Uraian') {
                    $_SESSION['currentPageUraian']=array('page_name'=>'m.belanja.Uraian','page_num'=>0,'dataKegiatan'=>array(),'RencanaTargetFisikPage'=>false);												
                }  
                $idproyek=addslashes($this->request['id']);
                $this->kegiatan->setIdProyek($idproyek,true);                        
                if (isset($this->kegiatan->dataKegiatan['idproyek'])) {                
                    $_SESSION['currentPageUraian']['dataKegiatan']=$this->kegiatan->dataKegiatan;                
                    $this->uraianAnchor->NavigateUrl=$this->Service->constructUrl('m.belanja.Uraian',array('id'=>$idproyek));
                    $this->populateData();
                    $this->invalidIDProyekMessage=true;
                }else {
                    unset($_SESSION['currentPageUraian']['dataKegiatan']);                                    
                }                           
            }
		}			
	}
    protected function populateData() {		
        $idproyek=$this->session['currentPageUraian']['dataKegiatan']['idproyek'];
		$str = "SELECT iduraian,rekening,nama_uraian,volume,satuan,nilai,jp FROM uraian WHERE idproyek=$idproyek ORDER BY rekening ASC";	
		$this->DB->setFieldTable(array('iduraian','rekening','nama_uraian','volume','satuan','nilai','jp'));
		$r=$this->DB->getRecord($str);		
        $result=array();
        while (list($k,$v)=each($r)) {
            $iduraian=$v['iduraian'];
            $terealisasi=$this->DB->checkRecordIsExist('iduraian','penggunaan',$iduraian)==true?1:0;
            $v['terrealisasi']=$terealisasi;
            $v['totalrealisasi']=0;
            $v['sisapagu']=0;
            $v['totalfisik']=0;
            if ($terealisasi==1) {
                $str = "SELECT SUM(realisasi) AS totalrealisasi,SUM(fisik) AS totalfisik FROM penggunaan WHERE iduraian=$iduraian";
                $this->DB->setFieldTable(array('totalrealisasi','totalfisik'));
                $realisasi=$this->DB->getRecord($str);
                $v['totalrealisasi']=$realisasi[1]['totalrealisasi'];
                $v['totalfisik']=$realisasi[1]['totalfisik'];
                $v['sisapagu']=$v['nilai']-$realisasi[1]['totalrealisasi'];
            }          
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();		
	}    
}
?>