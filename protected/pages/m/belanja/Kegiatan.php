<?php
prado::using ('Application.pages.m.belanja.MainPageBelanja');
class Kegiatan extends MainPageBelanja {	    
	public function onLoad ($param) {		
		parent::onLoad ($param);	        
        $this->showKegiatan=true;        
		if (!$this->IsCallback&&!$this->IsPostBack) {            
            if (!isset($_SESSION['currentPageKegiatan'])||$_SESSION['currentPageKegiatan']['page_name']!='m.belanja.Kegiatan') {
                $_SESSION['currentPageKegiatan']=array('page_name'=>'m.belanja.Kegiatan','page_num'=>0,'search'=>false,'dataKegiatan'=>array(),'idunitkerja'=>'none','nip_pptk'=>'none');												
            }            
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();            

            $_SESSION['currentPageKegiatan']['search']=false;
            $idunit=$_SESSION['currentPageKegiatan']['idunitkerja'];
            $this->cmbUnitKerja->DataSource=$this->kegiatan->getListUnitKerja();
            $this->cmbUnitKerja->Text=$idunit;
            $this->cmbUnitKerja->dataBind();
            
            $listpptk=$this->kegiatan->getList("pptk WHERE idunit='$idunit'",array ('nip_pptk','nama_pptk'),'nama_pptk',null,1);                    
            $this->cmbPPTK->DataSource=$listpptk;
            $this->cmbPPTK->Enabled=count($listpptk) > 1 ?true:false;
            $this->cmbPPTK->Text=$_SESSION['currentPageKegiatan']['nip_pptk'];
            $this->cmbPPTK->DataBind();
        
            $this->populateData();		            
		}
		
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
        $tahun=$this->session['ta'];
        $idunit=$this->idunit;
        $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);		
        $this->cmbProgram->DataSource=$result;
        $this->cmbProgram->dataBind();        
        $this->populateData ($_SESSION['currentPageKegiatan']['search']);
	}
    public function changeBulanRealisasi ($sender,$param) {	
        $_SESSION['bulanrealisasi']=$this->toolbarOptionsBulanRealisasi->Text;
        $this->populateData ($_SESSION['currentPageKegiatan']['search']);
	}
    public function filterUnitKerja ($sender,$param) {
        $idunit=$this->cmbUnitKerja->Text;
        $_SESSION['currentPageKegiatan']['page_num']=0;
        $_SESSION['currentPageKegiatan']['idunitkerja']=$idunit;
        
        $listpptk=$this->kegiatan->getList("pptk WHERE idunit='$idunit'",array ('nip_pptk','nama_pptk'),'nama_pptk',null,1);        
        $this->cmbPPTK->DataSource=$listpptk;        
        $this->cmbPPTK->Enabled=count($listpptk) > 1 ?true:false;
        $this->cmbPPTK->Text=$_SESSION['currentPageKegiatan']['nip_pptk'];
        $this->cmbPPTK->DataBind();
        $this->populateData($_SESSION['currentPageKegiatan']['search']);
    }
    
    public function filterPPTK ($sender,$param) {
        $nip_pptk=$this->cmbPPTK->Text;
        $_SESSION['currentPageKegiatan']['page_num']=0;
        $_SESSION['currentPageKegiatan']['nip_pptk']=$nip_pptk;        
        $this->populateData($_SESSION['currentPageKegiatan']['search']);
    }
    public function filterRecord ($sender,$param) {
        $_SESSION['currentPageKegiatan']['page_num']=0;
        $_SESSION['currentPageKegiatan']['search']=true;
        $this->populateData($_SESSION['currentPageKegiatan']['search']);
    }    
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKegiatan']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}  
	protected function populateData ($search=false) {		
        $tahun=$this->session['ta'];        
        $bulan=$this->session['bulanrealisasi'];        
        $idunitkerja=$_SESSION['currentPageKegiatan']['idunitkerja'];
        $nip_pptk=$_SESSION['currentPageKegiatan']['nip_pptk'];        
        $str_unitkerja=$idunitkerja=='none'?'':" AND pr.idunit=$idunitkerja";        
        $str_pptk=$nip_pptk=='none'?'':" AND p.nip_pptk='$nip_pptk'";        
        if ($search) {
            $str_jumlah="proyek p JOIN program pr ON (pr.idprogram=p.idprogram) WHERE tahun_anggaran=$tahun  $str_unitkerja $str_pptk";
            $str_baris = "SELECT idproyek,kode_proyek,nama_proyek,nama_pptk FROM proyek p JOIN program pr ON (pr.idprogram=p.idprogram) LEFT JOIN pptk ON (pptk.nip_pptk=p.nip_pptk) WHERE tahun_anggaran=$tahun $str_unitkerja $str_pptk";        
            $kriteria=$this->txtKriteria->Text;
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah AND kode_proyek LIKE '$kriteria%'";
                $str_baris = "$str_baris AND kode_proyek LIKE '$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah AND nama_proyek LIKE '%$kriteria%'";
                $str_baris = "$str_baris AND nama_proyek LIKE '%$kriteria%'";
            }                
        }else {
            $str_jumlah="proyek p JOIN program pr ON (pr.idprogram=p.idprogram) WHERE tahun_anggaran=$tahun $str_unitkerja $str_pptk";
            $str_baris = "SELECT idproyek,kode_proyek,nama_proyek,nama_pptk FROM proyek p JOIN program pr ON (pr.idprogram=p.idprogram) LEFT JOIN pptk ON (pptk.nip_pptk=p.nip_pptk) WHERE tahun_anggaran=$tahun  $str_unitkerja $str_pptk";        
        }
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKegiatan']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'idproyek');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageKegiatan']['page_num']=0;}
        $str="$str_baris ORDER BY p.kode_proyek ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable (array('idproyek','kode_proyek','nama_proyek','nama_pptk'));	        
		$r=$this->DB->getRecord($str,$offset+1);       
        $result=array();        
        while (list($k,$v)=each($r)) {
            $idproyek=$v['idproyek'];//                      
            
            $str = "SELECT SUM(nilai) AS pagu,COUNT(iduraian) AS totaluraian FROM uraian WHERE idproyek='$idproyek'";
            $this->DB->setFieldTable (array('pagu','totaluraian'));
            $pagu=$this->DB->getRecord($str);
            $v['totalPagu']=$pagu[1]['pagu']==''?0:$this->finance->toRupiah($pagu[1]['pagu']);

            $str = "SELECT SUM(target) AS target FROM v_laporan_a WHERE idproyek='$idproyek' AND bulan_penggunaan<='$bulan'";
            $this->DB->setFieldTable (array('target'));
            $total=$this->DB->getRecord($str);
            $v['totalTarget']=$total[1]['target']==''?0:$this->finance->toRupiah($total[1]['target']);

            $str = "SELECT SUM(realisasi) AS realisasi FROM v_laporan_a WHERE idproyek='$idproyek' AND bulan_penggunaan<='$bulan'";			
            $this->DB->setFieldTable (array('realisasi'));
            $total=$this->DB->getRecord($str);
            $v['totalRealisasi']=$total[1]['realisasi']==''?0:$this->finance->toRupiah($total[1]['realisasi']);            
            
            $str = "SELECT SUM(fisik) AS fisik FROM v_laporan_a WHERE idproyek='$idproyek' AND bulan_penggunaan<='$bulan'";			
            $this->DB->setFieldTable (array('fisik'));
            $total=$this->DB->getRecord($str);                                  
            
            $capaian=$total[1]['fisik']==''?0:number_format(($total[1]['fisik']/$pagu[1]['totaluraian']),2);
            $v['capaian']=$capaian;
            
            $targetkegiatan=$this->DB->getSumRowsOfTable ('fisik',"target_uraian tu,uraian u WHERE tu.iduraian=u.iduraian AND u.idproyek=$idproyek AND tu.bulan<='$bulan' AND tu.tahun=$tahun");            
            $style=$capaian<$targetkegiatan?'background-color:red;color:#fff':'background-color:green;color:#fff';            
            $v['style']=$style;
            $result[$k]=$v;
        }        
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	
	}   
}

?>