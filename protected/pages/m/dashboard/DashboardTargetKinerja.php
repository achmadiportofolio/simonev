<?php
prado::using ('Application.pages.m.dashboard.MainPageDashboard');
class DashboardTargetKinerja extends MainPageDashboard {
    public static $totalPersenTargetFisik=0;
    public static $totalPersenRealisasiFisik=0;
    public static $totalPersenTargetKeuangan=0;
    public static $totalPersenRealisasiKeuangan=0;
    public static $totalRpTargetKeuangan=0;
    public static $totalRpRealisasiKeuangan=0;
	public function onLoad($param) {		
		parent::onLoad($param);	        
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            $this->showDashboardTargetKinerja=true;
            if (!isset($_SESSION['currentPageDashboardTargetKinerja'])||$_SESSION['currentPageDashboardTargetKinerja']['page_name']!='m.dashboard.DashboardTargetKinerja') {
                $_SESSION['currentPageDashboardTargetKinerja']=array('page_name'=>'m.dashboard.DashboardTargetKinerja','page_num'=>0);												
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
        $this->redirect('m.dashboard.DashboardTargetKinerja');
	}
    public function changeBulanRealisasi ($sender,$param) {	
        $_SESSION['bulanrealisasi']=$this->toolbarOptionsBulanRealisasi->Text;
        $this->redirect('m.dashboard.DashboardTargetKinerja');
	}
    protected function populateData () { 
        $ta=$this->session['ta'];
        $bulanrealisasi=$_SESSION['bulanrealisasi'];
        $str = "SELECT idunit,nama_unit FROM unit u,bagian b WHERE b.idbagian=u.idbagian ORDER BY kode_unit";
		$this->DB->setFieldTable(array('idunit','nama_unit'));        
        $r=$this->DB->getRecord($str);        
        $result=array();  
        while (list ($k,$v)=each($r)) {
            $idunit=$v['idunit'];                        
            //menghitung persen target fisik
            $str = "SELECT SUM(fisik) AS jumlah_fisik FROM target_uraian tu,uraian u,proyek p,program pr WHERE tu.iduraian=u.iduraian AND u.idproyek=p.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND bulan <= '$bulanrealisasi' AND tu.tahun='$ta'";            
            $this->DB->setFieldTable(array('jumlah_fisik'));
            $data_target_fisik=$this->DB->getRecord($str);
            
            $target_fisik=0;
            if ($data_target_fisik[1]['jumlah_fisik'] >0){                
                $str = "SELECT COUNT(iduraian) AS jumlah_uraian FROM uraian u,proyek p,program pr WHERE u.idproyek=p.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND tahun_anggaran='$ta'";
                $this->DB->setFieldTable(array('jumlah_uraian'));
                $data_target_fisik2=$this->DB->getRecord($str);                                                
                $target_fisik=round(($data_target_fisik[1]['jumlah_fisik']/$data_target_fisik2[1]['jumlah_uraian']));
            }            
            $v['target_fisik']=$target_fisik;
            
            //menghitung persen realisasi fisik
            $str = "SELECT SUM(fisik) AS jumlah_fisik,COUNT(idpenggunaan) AS jumlah_baris_realisasi FROM penggunaan pe,uraian u,proyek p,program pr WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND pe.bulan <= '$bulanrealisasi' AND tahun_anggaran='$ta'";            
            $this->DB->setFieldTable(array('jumlah_fisik','jumlah_baris_realisasi'));
            $data_persen_realisasi_fisik=$this->DB->getRecord($str);            
            $persen_realisasi_fisik=0;
            if ($data_persen_realisasi_fisik[1]['jumlah_fisik'] >0){                   
                $persen_realisasi_fisik=round(($data_persen_realisasi_fisik[1]['jumlah_fisik']/$data_persen_realisasi_fisik[1]['jumlah_baris_realisasi']));
            }                        
            $v['persen_realisasi_fisik']=$persen_realisasi_fisik;
            
            //menghitung persen target keuangan
            $str = "SELECT SUM(target_sp2d) AS jumlah_sp2d FROM target_uraian tu,uraian u,proyek p,program pr WHERE tu.iduraian=u.iduraian AND u.idproyek=p.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND bulan <= '$bulanrealisasi' AND tu.tahun='$ta'";            
            $this->DB->setFieldTable(array('jumlah_sp2d'));
            $data_target_sp2d=$this->DB->getRecord($str);         
            $target_sp2d=0;
            if ($data_target_sp2d[1]['jumlah_sp2d'] >0){                                
                $str = "SELECT SUM(target_sp2d) AS jumlah_sp2d FROM target_uraian tu,uraian u,proyek p,program pr WHERE tu.iduraian=u.iduraian AND u.idproyek=p.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND tu.tahun='$ta'";            
                $this->DB->setFieldTable(array('jumlah_sp2d'));
                $data_target_sp2d2=$this->DB->getRecord($str);                                                
                $target_sp2d=round(($data_target_sp2d[1]['jumlah_sp2d']/$data_target_sp2d2[1]['jumlah_sp2d'])*100);
            }            
            $v['target_sp2d']=$target_sp2d;
            
            //menghitung persen realisasi keuangan
            $str = "SELECT SUM(realisasi) AS jumlah_realisasi FROM penggunaan pe,uraian u,proyek p,program pr WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND pe.bulan <= '$bulanrealisasi' AND tahun_anggaran='$ta'";
            $this->DB->setFieldTable(array('jumlah_realisasi'));
            $data_persen_realisasi_sp2d=$this->DB->getRecord($str);            
            $persen_realisasi_sp2d=0;
            if ($data_persen_realisasi_sp2d[1]['jumlah_realisasi'] >0){                                         
                $persen_realisasi_sp2d=round(($data_persen_realisasi_sp2d[1]['jumlah_realisasi']/$data_target_sp2d2[1]['jumlah_sp2d'])*100);
            }                        
            $v['persen_realisasi_sp2d']=$persen_realisasi_sp2d;
            $v['jumlah_target_sp2d']=$this->DB->getSumRowsOfTable('target_sp2d',"target_uraian tu,uraian u,proyek p,program pr WHERE tu.iduraian=u.iduraian AND u.idproyek=p.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND bulan <= '$bulanrealisasi' AND tahun_anggaran='$ta'");
            $v['jumlah_realisasi_sp2d']=$this->DB->getSumRowsOfTable('realisasi',"penggunaan pe,uraian u,proyek p,program pr WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND p.idprogram=pr.idprogram AND pr.idunit=$idunit AND pe.bulan <= '$bulanrealisasi' AND tahun_anggaran='$ta'");
            $result[$k]=$v;
        }        
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();        
    }    
    public function itemCreated ($sender,$param) {
        $item=$param->Item;		
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
            DashboardTargetKinerja::$totalPersenRealisasiFisik+=$item->DataItem['persen_realisasi_fisik'];
            DashboardTargetKinerja::$totalPersenTargetFisik+=$item->DataItem['target_fisik'];
            DashboardTargetKinerja::$totalPersenTargetKeuangan+=$item->DataItem['target_sp2d'];
            DashboardTargetKinerja::$totalPersenRealisasiKeuangan+=$item->DataItem['persen_realisasi_sp2d'];
            DashboardTargetKinerja::$totalRpTargetKeuangan+=$item->DataItem['jumlah_target_sp2d'];
            DashboardTargetKinerja::$totalRpRealisasiKeuangan+=$item->DataItem['jumlah_realisasi_sp2d'];
        }
    }
}  
		