<?php
prado::using ('Application.pages.d.report.MainPageReports');
class formA extends MainPageReports {	
    public $result=array();	
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
                if (!isset($_SESSION['currentPageFormA'])||$_SESSION['currentPageFormA']['page_name']!='d.report.formA') {
                    $_SESSION['currentPageFormA']=array('page_name'=>'d.report.formA','page_num'=>0,'dataKegiatan'=>array(),'idprogram'=>'none','search'=>false,'userid'=>'none');												
                }
                $tahun=$this->session['ta'];
                $idunit=$this->idunit;
                $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);		
                $result['none']='Keseluruhan Program';
                $this->cmbProgram->DataSource=$result;
                $this->cmbProgram->Text=$_SESSION['currentPageFormA']['idprogram'];
                $this->cmbProgram->dataBind();

                $daftar_user=$this->kegiatan->getList("user WHERE idunit=$idunit AND page='s' OR page='d'",array('userid','username'),'username',null,1);
                $daftar_user['none']='All';
                $this->cmbStaff->DataSource=$daftar_user;
                $this->cmbStaff->Text=$_SESSION['currentPageFormA']['userid'];
                $this->cmbStaff->dataBind();

                $_SESSION['currentPageFormA']['search']=false;
                $this->populateData ();		
            }
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();
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
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageFormA']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}  
    public function changeProgramFilter ($sender,$param) {
        $_SESSION['currentPageFormA']['idprogram']=$this->cmbProgram->Text;
        $this->populateData($_SESSION['currentPageFormA']['search']);
    }
    public function changeStaffFilter ($sender,$param) {
        $_SESSION['currentPageFormA']['userid']=$this->cmbStaff->Text;
        $this->populateData($_SESSION['currentPageFormA']['search']);
    }
    public function filterRecord ($sender,$param) {
        $_SESSION['currentPageFormA']['search']=true;
        $this->populateData($_SESSION['currentPageFormA']['search']);
    }
    protected function populateData ($search=false) {		
        $idunit=$this->idunit;
        $tahun=$this->session['ta'];        
        $bulan=$this->session['bulanrealisasi'];
        $idprogram=$_SESSION['currentPageFormA']['idprogram'];
        $str_kode_program=$idprogram=='none'?'':"AND idprogram=$idprogram";
        $userid=$_SESSION['currentPageFormA']['userid'];
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";
        $str_jumlah="proyek p,program pr WHERE pr.idprogram=p.idprogram AND pr.idunit=$idunit AND tahun_anggaran=$tahun $str_kode_program $str_kode_user";        
        $str_baris = "SELECT idproyek,kode_proyek,nama_proyek FROM proyek p,program pr WHERE pr.idprogram=p.idprogram AND pr.idunit=$idunit AND tahun_anggaran=$tahun $str_kode_program $str_kode_user";                
        if ($search) {
            $kriteria=$this->txtKriteria->Text;
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah AND kode_proyek LIKE '$kriteria%'";
                $str_baris = "$str_baris AND kode_proyek LIKE '$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah AND nama_proyek LIKE '%$kriteria%'";
                $str_baris = "$str_baris AND nama_proyek LIKE '%$kriteria%'";
            }                
        }        
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageFormA']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'idproyek');	
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageFormA']['page_num']=0;}
        $str="$str_baris ORDER BY p.kode_proyek ASC LIMIT $offset,$limit";        
		$this->DB->setFieldTable (array('idproyek','kode_proyek','nama_proyek'));	        
		$r=$this->DB->getRecord($str,$offset+1);       
        $result=array();        
        while (list($k,$v)=each($r)) {
            $idproyek=$v['idproyek'];
            $str = "SELECT SUM(nilai) AS pagu FROM uraian WHERE idproyek='$idproyek'";
            $this->DB->setFieldTable (array('pagu'));
            $total=$this->DB->getRecord($str);
            $v['totalPagu']=$this->finance->toRupiah($total[1]['pagu']);

            $str = "SELECT SUM(target) AS target FROM v_laporan_a WHERE idproyek='$idproyek' AND bulan_penggunaan <= '$bulan'";
            $this->DB->setFieldTable (array('target'));
            $total=$this->DB->getRecord($str);
            $v['totalTarget']=$this->finance->toRupiah($total[1]['target']);

            $str = "SELECT SUM(realisasi) AS realisasi FROM v_laporan_a WHERE idproyek='$idproyek' AND bulan_penggunaan <= '$bulan'";			
            $this->DB->setFieldTable (array('realisasi'));          
            $total=$this->DB->getRecord($str);
            $v['totalRealisasi']=$this->finance->toRupiah($total[1]['realisasi']);

            $result[$k]=$v;
        }
        
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	
	}
    public function viewRecord ($sender,$param) {
        $idproyek=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->kegiatan->setIdProyek($idproyek,false,2);
        $dataproyek=$this->kegiatan->getDataProyek();                
        $_SESSION['currentPageFormA']['dataKegiatan']=$dataproyek;
        $this->redirect('d.report.formA');                
    }
	private function initialization () {	
		$idproyek=$_SESSION['currentPageFormA']['dataKegiatan']['idproyek'];	                
		$str = "SELECT pa.nama_pengguna_anggaran,pa.nip_pengguna_anggaran,pp.nip_pptk,pp.nama_pptk FROM proyek p LEFT JOIN pengguna_anggaran pa ON (pa.nip_pengguna_anggaran=p.nip_pengguna_anggaran) LEFT JOIN pptk pp ON (p.nip_pptk=pp.nip_pptk) WHERE p.idproyek='$idproyek'";
		$this->DB->setFieldTable (array('nama_pengguna_anggaran','nip_pengguna_anggaran','nip_pptk','nama_pptk'));
		$r=$this->DB->getRecord ($str);		
		$_SESSION['currentPageFormA']['dataKegiatan']['nama_pengguna_anggaran']=$r[1]['nama_pengguna_anggaran'];
		$_SESSION['currentPageFormA']['dataKegiatan']['nip_pengguna_anggaran']=$r[1]['nip_pengguna_anggaran'];
		$_SESSION['currentPageFormA']['dataKegiatan']['nama_pptk']=$r[1]['nama_pptk'];
		$_SESSION['currentPageFormA']['dataKegiatan']['nip_pptk']=$r[1]['nip_pptk']; 		        
	}
	public function closeView ($sender,$param) {
		unset($_SESSION['currentPageFormA']['dataKegiatan']);
		$this->redirect('d.report.formA');
	}		
	/**
	* digunakan untuk mendapatkan data proyek
	*	
	*/	
	private function getDataProyek () {
		$no_bulan=$this->session['bulanrealisasi'];		
		$idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];        
        $str = "SELECT rek1.no_rek1,rek1.nama_rek1,rek2.no_rek2,rek2.nama_rek2,rek3.no_rek3,rek3.nama_rek3,rek4.no_rek4,rek4.nama_rek4,rek5.no_rek5,rek5.nama_rek5,u.iduraian,u.nama_uraian,u.nilai,CONCAT (volume,' ',satuan) AS volume FROM uraian u,rek1,rek2,rek3,rek4,rek5 WHERE u.rekening=rek5.no_rek5 AND rek4.no_rek4=rek5.no_rek4 AND rek3.no_rek3=rek4.no_rek3 AND rek2.no_rek2=rek3.no_rek2 AND rek1.no_rek1=rek2.no_rek1 AND u.idproyek='$idproyek' ORDER BY rek5.no_rek5 ASC";
        $this->DB->setFieldTable (array('no_rek1','nama_rek1','no_rek2','nama_rek2','no_rek3','nama_rek3','no_rek4','nama_rek4','no_rek5','nama_rek5','iduraian','nama_uraian','nilai','volume'));
        $r1=$this->DB->getRecord($str);
        $dataAkhir=array();		
        if (isset($r1[1])) {
            while (list($k,$datauraianproyek)=each($r1)) {                
                $iduraian=$datauraianproyek['iduraian'];                      
                $nama_uraian=$datauraianproyek['nama_uraian'];                
                $str = "SELECT realisasi,target,fisik FROM penggunaan WHERE bulan <= '$no_bulan' AND iduraian=$iduraian";                                
                $this->DB->setFieldTable (array('realisasi','target','fisik'));
                $r2=$this->DB->getRecord($str);
                $target=0;
                $realisasi=0;
                $fisik=0;
                foreach ($r2 as $n) {
                    $target+=$n['target'];
                    $realisasi+=$n['realisasi'];
                    $fisik+=$n['fisik'];
                }
                $no_rek5=$datauraianproyek['no_rek5'];
                if (array_key_exists ($no_rek5,$dataAkhir)) {                
                    $dataAkhir[$no_rek5]['child'][]=array('no_rek1'=>$datauraianproyek['no_rek1'],
                                 'nama_rek1'=>$datauraianproyek['nama_rek1'],
                                 'no_rek2'=>$datauraianproyek['no_rek2'],
                                 'nama_rek2'=>$datauraianproyek['nama_rek2'],
                                 'no_rek3'=>$datauraianproyek['no_rek3'],
                                 'nama_rek3'=>$datauraianproyek['nama_rek3'],
                                 'no_rek4'=>$datauraianproyek['no_rek4'],
                                 'nama_rek4'=>$datauraianproyek['nama_rek4'],
                                 'no_rek5'=>$datauraianproyek['no_rek5'],
                                 'nama_rek5'=>$datauraianproyek['nama_rek5'],
                                 'iduraian'=>$iduraian,
                                 'nama_uraian'=>$nama_uraian,
                                 'nilai'=>$datauraianproyek['nilai'],
                                 'target'=>$target,
                                 'realisasi'=>$realisasi,
                                 'fisik'=>$fisik,
                                 'volume'=>$datauraianproyek['volume']);                                  
                }else {
                    $dataAkhir[$no_rek5]=array('no_rek1'=>$datauraianproyek['no_rek1'],
                                               'nama_rek1'=>$datauraianproyek['nama_rek1'],
                                               'no_rek2'=>$datauraianproyek['no_rek2'],
                                               'nama_rek2'=>$datauraianproyek['nama_rek2'],
                                               'no_rek3'=>$datauraianproyek['no_rek3'],
                                               'nama_rek3'=>$datauraianproyek['nama_rek3'],
                                               'no_rek4'=>$datauraianproyek['no_rek4'],
                                               'nama_rek4'=>$datauraianproyek['nama_rek4'],
                                               'no_rek5'=>$datauraianproyek['no_rek5'],
                                               'nama_rek5'=>$datauraianproyek['nama_rek5'],
                                               'nama_uraian'=>$nama_uraian,
                                               'iduraian'=>$iduraian,
                                               'nilai'=>$datauraianproyek['nilai'],
                                               'target'=>$target,
                                               'realisasi'=>$realisasi,
                                               'fisik'=>$fisik,
                                               'volume'=>$datauraianproyek['volume']);
                }
                
            }
        }
        $this->result = $dataAkhir;			        
        return $dataAkhir;
	}
	/**
	* digunakan untuk mendapatkan tingkat rekening
	*	
	*/
	private function getRekeningProyek () {		 
		$a=$this->result;
        $tingkat=array();
		foreach ($a as $v) {					
			$tingkat[1][$v['no_rek1']]=$v['nama_rek1'];
			$tingkat[2][$v['no_rek2']]=$v['nama_rek2'];
			$tingkat[3][$v['no_rek3']]=$v['nama_rek3'];
			$tingkat[4][$v['no_rek4']]=$v['nama_rek4'];
			$tingkat[5][$v['no_rek5']]=$v['nama_rek5'];				
		}
		return $tingkat;
	}
	public function calculateEachLevel ($dataproyek,$k,$no_rek) {
        $totalpagu=0;
        $totaltarget=0;
        $totalrealisasi=0;        
        $totalfisik=0;
        foreach ($dataproyek as $de) {            
            if ($k==$de[$no_rek]) {                                
                $totalpagu+=$de['nilai'];
                $totaltarget+=$de['target'];
                $totalrealisasi+=$de['realisasi'];
                $totalfisik+=$de['fisik'];
                if (isset($dataproyek[$de['no_rek5']]['child'][0])) {                    
                    $child=$dataproyek[$de['no_rek5']]['child'];                    
                    foreach ($child as $n) {                       
                        $totalpagu+=$n['nilai'];
                        $totaltarget+=$n['target'];
                        $totalrealisasi+=$n['realisasi'];
                        $totalfisik+=$n['fisik'];
                    }
                }
            }
        }        
        $result=array('totalpagu'=>$totalpagu,'totaltarget'=>$totaltarget,'totalrealisasi'=>$totalrealisasi,'totalfisik'=>$totalfisik);        
        return $result;
    }	
	public function printContent() {		
		$no_bulan=$this->session['bulanrealisasi'];
		$tahun=$this->session['ta'];
		
		$content = '<table class="list" style="font-size:9px">';
        $content.= '<thead>';
		$content.= '<tr class="center">';
		$content.= '<th rowspan="3" colspan="5">KODE <BR>REKENING</th>';				
		$content.= '<th rowspan="3" width="300" class="center">URAIAN</th>';
		$content.= '<th rowspan="2" width="100" class="center">PAGU DANA</th>';				
        $content.= '<th rowspan="2" width="70" class="center">VOLUME</th>';				
        $content.= '<th rowspan="2" width="40" class="center">BOBOT</th>';				
		$content.= '<th colspan="5" class="center">KEUANGAN</th>';		
		$content.= '<th colspan="2" class="center">REALISASI <br />FISIK<br />PEKERJAAN (%)</th>';				
        $content.= '<th colspan="2" class="center">SISA ANGGARAN</th>';				
		$content.= '</tr>';	
		
		$content.= '<tr class="center">';
        $content.= '<td colspan="2" class="center">TARGET (SPM)</td>';		
		$content.= '<td colspan="3">REALISASI (SPJ)</td>';						        
        $content.= '<td rowspan="2">RATA-<br />RATA(%)</td>';	        
        $content.= '<td rowspan="2">TERTIM-<br />BANG(%)</td>';			
       	$content.= '<td rowspan="2">DPA</td>';						        
        $content.= '<td rowspan="2">KAS</td>';					        
		$content.= '</tr>';
        $content.= '<tr>';		
		$content.= '<td class="center">Rp.</td>';		
        $content.= '<td class="center">Unit,Paket</td>';		
		$content.= '<td class="center">%</td>';				
        $content.= '<td class="center">Rp.</td>';	
        $content.= '<td class="center">%</td>';	
        $content.= '<td class="center">Rp.</td>';
        $content.= '<td class="center">% Rata-rata</td>';	
        $content.= '<td class="center">% Tertimbang</td>';	        					;		
		$content.= '</tr>';
		$content.= '<tr>';
		$content.= '<td colspan="5" class="center">1</td>';				
		$content.= '<td class="center">2</td>';
		$content.= '<td class="center">3</td>';				
		$content.= '<td class="center">4</td>';		
		$content.= '<td class="center">5</td>';	
		$content.= '<td class="center">6a</td>';						
        $content.= '<td class="center">6b</td>';			
        $content.= '<td class="center">6c</td>';			
        $content.= '<td class="center">6d</td>';			
        $content.= '<td class="center">6e</td>';			
        $content.= '<td class="center">7</td>';			
        $content.= '<td class="center">8</td>';			
        $content.= '<td class="center">8</td>';			
        $content.= '<td class="center">9</td>';			
		$content.= '</tr>';
		$content.= '</thead>';
        
		$dataproyek=$this->getDataProyek();           
        $tingkat = $this->getRekeningProyek();        
        if (isset($tingkat[1])) {            
            $tingkat_1=$tingkat[1];            
            $tingkat_2=$tingkat[2];
            $tingkat_3=$tingkat[3];
            $tingkat_4=$tingkat[4];
            $tingkat_5=$tingkat[5];            
            while (list($k1,$v1)=each($tingkat_1)) {
                foreach ($tingkat_5 as $k5=>$v5) {
                    $rek1=substr($k5,0,1);
                    if ($rek1 == $k1) {
                        //tingkat i
                        $totalPaguDana_Rek1=$this->calculateEachLevel($dataproyek,$k1,'no_rek1');                        
                        $totalPaguDana=$totalPaguDana_Rek1['totalpagu'];
                        $rp_total_pagu_dana_rek1=$this->finance->toRupiah($totalPaguDana);
                        $content.= '<tr>';
                        $content.= '<td width="10" class="center">'.$k1.'.</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td class="left">'.$v1.'</td>';
                        $content.= '<td class="right">'.$rp_total_pagu_dana_rek1.'</td>';										
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '</tr>';

                        //tingkat ii
                        foreach ($tingkat_2 as $k2=>$v2) {
                            $rek2_tampil=array();
                            foreach ($tingkat_5 as $k5_level2=>$v5_level2) {
                                $rek2=substr($k5_level2,0,3);								
                                if ($rek2 == $k2) {
                                    if (!array_key_exists($k2,$rek2_tampil)){															
                                        $rek2_tampil[$rek2]=$v2;
                                    }							
                                }
                            }
                            foreach ($rek2_tampil as $a=>$b) {
                                $totalPaguDana_Rek2=$this->calculateEachLevel($dataproyek,$a,'no_rek2');                                
                                $no_=explode ('.',$a);
                                $rp_total_pagu_dana_rek2=$this->finance->toRupiah($totalPaguDana_Rek2['totalpagu']);
                                $content.= '<tr>';
                                $content.= '<td class="center">'.$no_[0].'.</td>';
                                $content.= '<td class="center">'.$no_[1].'.</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="left">'.$b.'</td>';
                                $content.= '<td class="right">'.$rp_total_pagu_dana_rek2.'</td>';														
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '</tr>';

                                //tingkat iii
                                foreach ($tingkat_3 as $k3=>$v3) {	
                                    $rek3=substr($k3,0,3);
                                    if ($a==$rek3) {
                                        $jumlahrek3+=1;
                                        $totalPaguDana_Rek3=$this->calculateEachLevel($dataproyek,$k3,'no_rek3');                                        
                                        $no_=explode (".",$k3);
                                        $rp_total_pagu_dana_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalpagu']);
                                        $persen_bobot_rek3=number_format(($totalPaguDana_Rek3['totalpagu']/$totalPaguDana)*100,2);
                                        $total_persen_bobot_rek3+=$persen_bobot_rek3;
                                        $rp_total_target_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totaltarget']);
                                        $totalTarget_rek3+=$totalPaguDana_Rek3['totaltarget'];
                                        $persen_target_rek3=number_format(($totalPaguDana_Rek3['totaltarget']/$totalPaguDana_Rek3['totalpagu'])*100,2);
                                        $rp_total_realisasi_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalrealisasi']);
                                        $totalRealisasi_rek3+=$totalPaguDana_Rek3['totalrealisasi'];
                                        $persen_realisasi_rek3=number_format(($totalPaguDana_Rek3['totaltarget']/$totalPaguDana_Rek3['totalpagu'])*100,2);
                                        $persen_tertimbang_rek3=number_format(($persen_realisasi_rek3*$persen_bobot_rek3)/100,2);
                                        $total_persen_tertimbang_rek3+=$persen_tertimbang_rek3;                                                                                
                                        $persen_rata2_fisik_rek3=$totalPaguDana_Rek3['totalfisik'];                                                
                                        $total_persen_rata2_fisik_rek3+=$persen_rata2_fisik_rek3;
                                        $persen_tertimbang_fisik_rek3=number_format(($persen_rata2_fisik_rek3*$persen_bobot_rek3)/100,2);
                                        $total_persen_fisik_tertimbang_rek3+=$persen_tertimbang_fisik_rek3;
                                        $dalamDpa_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalpagu']-$totalPaguDana_Rek3['totaltarget']);                                                
                                        $dalamKas_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totaltarget']-$totalPaguDana_Rek3['totalrealisasi']);
                                        $content.= '<tr>';
                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';											
                                        $content.= '<td class="left">'.$v3.'</td>';									
                                        $content.= '<td class="right">'.$rp_total_pagu_dana_rek3.'</td>';																		
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">'.$persen_bobot_rek3.'</td>';
                                        $content.= '<td class="right">'.$rp_total_target_rek3.'</td>';
                                        $content.= '<td class="center">'.$persen_target_rek3.'</td>';
                                        $content.= '<td class="right">'.$rp_total_realisasi_rek3.'</td>';
                                        $content.= '<td class="center">'.$persen_realisasi_rek3.'</td>';
                                        $content.= '<td class="center">'.$persen_tertimbang_rek3.'</td>';
                                        $content.= '<td class="center">'.$persen_rata2_fisik_rek3.'</td>';
                                        $content.= '<td class="center">'.$persen_tertimbang_fisik_rek3.'</td>';
                                        $content.= '<td class="right">'.$dalamDpa_rek3.'</td>';
                                        $content.= '<td class="center">'.$dalamKas_rek3.'</td>'; 
                                        $content.= '</tr>';

                                        foreach ($tingkat_4 as $k4=>$v4) {
                                            if (ereg ($k3,$k4)) {                                           				
                                                $totalPaguDana_Rek4=$this->calculateEachLevel($dataproyek,$k4,'no_rek4');
                                                $rp_total_pagu_dana_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalpagu']);
                                                $no_=explode (".",$k4);
                                                $persen_bobot_rek4=number_format(($totalPaguDana_Rek4['totalpagu']/$totalPaguDana)*100,2);
                                                $rp_total_target_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totaltarget']);
                                                $persen_target_rek4=number_format(($totalPaguDana_Rek4['totaltarget']/$totalPaguDana_Rek4['totalpagu'])*100,2);
                                                $rp_total_realisasi_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalrealisasi']);
                                                $persen_realisasi_rek4=number_format(($totalPaguDana_Rek4['totaltarget']/$totalPaguDana_Rek4['totalpagu'])*100,2);
                                                $persen_tertimbang_rek4=number_format(($persen_realisasi_rek4*$persen_bobot_rek4)/100,2);
                                                $persen_rata2_fisik_rek4=$totalPaguDana_Rek4['totalfisik'];                                                
                                                $persen_tertimbang_fisik_rek4=number_format(($persen_rata2_fisik_rek4*$persen_bobot_rek4)/100,2);
                                                $dalamDpa_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalpagu']-$totalPaguDana_Rek4['totaltarget']);                                                
                                                $dalamKas_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totaltarget']-$totalPaguDana_Rek4['totalrealisasi']);
                                                
                                                $content.= '<tr>';
                                                $content.= '<td class="center">'.$no_[0].'.</td>';
                                                $content.= '<td class="center">'.$no_[1].'.</td>';
                                                $content.= '<td class="center">'.$no_[2].'.</td>';
                                                $content.= '<td class="center">'.$no_[3].'.</td>';											
                                                $content.= '<td class="center">&nbsp;</td>';		
                                                $content.= '<td class="left">'.$v4.'</td>';																					
                                                $content.= '<td class="right">'.$rp_total_pagu_dana_rek4.'</td>';														
                                                $content.= '<td class="center">&nbsp;</td>';
                                                $content.= '<td class="center">'.$persen_bobot_rek4.'</td>';
                                                $content.= '<td class="right">'.$rp_total_target_rek4.'</td>';
                                                $content.= '<td class="center">'.$persen_target_rek4.'</td>';
                                                $content.= '<td class="right">'.$rp_total_realisasi_rek4.'</td>';
                                                $content.= '<td class="center">'.$persen_realisasi_rek4.'</td>';
                                                $content.= '<td class="center">'.$persen_tertimbang_rek4.'</td>';
                                                $content.= '<td class="center">'.$persen_rata2_fisik_rek4.'</td>';
                                                $content.= '<td class="center">'.$persen_tertimbang_fisik_rek4.'</td>';
                                                $content.= '<td class="right">'.$dalamDpa_rek4.'</td>';
                                                $content.= '<td class="center">'.$dalamKas_rek4.'</td>';                                                
                                                $content.= '</tr>';

                                                foreach ($tingkat_5 as $k5=>$v5) {
                                                    if (ereg ($k4,$k5)) {      
                                                        $totalUraian+=1;
                                                        $totalPaguDana_Rek5=$this->calculateEachLevel($dataproyek,$k5,'no_rek5');													
                                                        $rp_total_pagu_dana_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalpagu']);       
                                                        $iduraian=$dataproyek[$k5]['iduraian'];
                                                        $nama_uraian=$dataproyek[$k5]['nama_uraian']; 
                                                        $no_=explode (".",$k5);    
                                                        $persen_bobot_rek5=number_format(($totalPaguDana_Rek5['totalpagu']/$totalPaguDana)*100,2);
                                                        $rp_total_target_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totaltarget']);
                                                        $persen_target_rek5=number_format(($totalPaguDana_Rek5['totaltarget']/$totalPaguDana_Rek5['totalpagu'])*100,2);
                                                        $rp_total_realisasi_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalrealisasi']);
                                                        $persen_realisasi_rek5=number_format(($totalPaguDana_Rek5['totaltarget']/$totalPaguDana_Rek5['totalpagu'])*100,2);
                                                        $persen_tertimbang_rek5=number_format(($persen_realisasi_rek5*$persen_bobot_rek5)/100,2);
                                                        $persen_rata2_fisik_rek5=$totalPaguDana_Rek5['totalfisik'];                                                
                                                        $persen_tertimbang_fisik_rek5=number_format(($persen_rata2_fisik_rek5*$persen_bobot_rek5)/100,2);
                                                        $dalamDpa_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalpagu']-$totalPaguDana_Rek5['totaltarget']);                                                
                                                        $dalamKas_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totaltarget']-$totalPaguDana_Rek5['totalrealisasi']);
                                                        $content.= '<tr>';
                                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                                        $content.= '<td class="center">'.$no_[3].'.</td>';															
                                                        $content.= '<td class="center">'.$no_[4].'.</td>';
                                                        $content.= '<td class="left">'.$v5.'</td>';
                                                        $content.= '<td class="right">'.$rp_total_pagu_dana_rek5.'</td>';
                                                        $content.= '<td class="center">&nbsp;</td>';
                                                        $content.= '<td class="center">'.$persen_bobot_rek5.'</td>';
                                                        $content.= '<td class="right">'.$rp_total_target_rek5.'</td>';
                                                        $content.= '<td class="right">'.$persen_target_rek5.'</td>';
                                                        $content.= '<td class="right">'.$rp_total_realisasi_rek5.'</td>';
                                                        $content.= '<td class="center">'.$persen_realisasi_rek5.'</td>';
                                                        $content.= '<td class="center">'.$persen_tertimbang_rek5.'</td>';
                                                        $content.= '<td class="center">'.$persen_rata2_fisik_rek5.'</td>';
                                                        $content.= '<td class="center">'.$persen_tertimbang_fisik_rek5.'</td>';
                                                        $content.= '<td class="right">'.$dalamDpa_rek5.'</td>';
                                                        $content.= '<td class="center">'.$dalamKas_rek5.'</td>';
                                                        $content.= '</tr>';	
                                                        
                                                        $nilaiuraian=$dataproyek[$k5]['nilai']; 
                                                        $target=$dataproyek[$k5]['target'];                                                         
                                                        $realisasi=$dataproyek[$k5]['realisasi'];                                                        
                                                        $fisik=$dataproyek[$k5]['fisik'];                                                        
                                                        $volume=$dataproyek[$k5]['volume'];                                                        
                                                        $persen_bobot=number_format(($nilaiuraian/$totalPaguDana)*100,2);                                                        
                                                        $persen_target=number_format(($target/$nilaiuraian)*100,2);   
                                                        $persen_rata2_realisasi=number_format(($realisasi/$nilaiuraian)*100,2);
                                                        $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);                                                        
                                                        $persen_rata2_fisik=$fisik;                                                        
                                                        $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);
                                                        $dalamDpa=$nilaiuraian-$target;                                                        
                                                        $dalamKas=$target-$realisasi;                                                       
                                                        
                                                        $rp_nilai_uraian=$this->finance->toRupiah($nilaiuraian); 
                                                        $rp_target=$this->finance->toRupiah($target);
                                                        $rp_realisasi=$this->finance->toRupiah($realisasi);
                                                        $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa);
                                                        $rp_dalam_kas=$this->finance->toRupiah($dalamKas);
                                                        
                                                        $content.= '<tr>';
                                                        $content.= '<td colspan="5"></td>';                    
                                                        $url=$this->Service->constructUrl('m.report.formADetails',array('id'=>$iduraian));
                                                        $tetaut = "<a href=\"$url\">$nama_uraian</a>";
                                                        $content.= '<td class="left">--- '.$tetaut.'</td>';
                                                        $content.= '<td class="right">'.$rp_nilai_uraian.'</td>';
                                                        $content.= "<td class=\"center\">$volume</td>";
                                                        $content.= "<td class=\"center\">$persen_bobot</td>";
                                                        $content.= "<td class=\"right\">$rp_target</td>";
                                                        $content.= "<td class=\"right\">$persen_target</td>";
                                                        $content.= "<td class=\"right\">$rp_realisasi</td>";
                                                        $content.= "<td class=\"center\">$persen_rata2_realisasi</td>";
                                                        $content.= "<td class=\"center\">$persen_tertimbang_realisasi</td>";
                                                        $content.= "<td class=\"center\">$persen_rata2_fisik</td>";
                                                        $content.= "<td class=\"center\">$persen_tertimbang_fisik</td>";
                                                        $content.= "<td class=\"right\">$rp_dalam_dpa</td>";
                                                        $content.= "<td class=\"center\">$rp_dalam_kas</td>";                                                        
                                                        $content.= '</tr>';	                                                        
                                                        if (isset($dataproyek[$k5]['child'][0])) {                    
                                                            $child=$dataproyek[$k5]['child'];                                                                                                                                            
                                                            foreach ($child as $n) {
                                                                $totalUraian+=1;
                                                                $iduraian=$n['iduraian'];                                                                
                                                                $nama_uraian=$n['nama_uraian'];
                                                                $nilaiuraian=$n['nilai']; 
                                                                $target=$n['target'];                                                                 
                                                                $realisasi=$n['realisasi'];    
                                                                $fisik=$n['fisik'];                                                                
                                                                $volume=$n['volume'];
                                                                $persen_bobot=number_format(($nilaiuraian/$totalPaguDana)*100,2);                                                                
                                                                $persen_target=number_format(($target/$nilaiuraian)*100,2);   
                                                                $persen_rata2_realisasi=number_format(($realisasi/$nilaiuraian)*100,2);
                                                                $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);                                                                
                                                                $persen_rata2_fisik=$fisik;                                                           
                                                                $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);
                                                                $dalamDpa=$nilaiuraian-$target;                                                                
                                                                $dalamKas=$target-$realisasi;                                                               
                                                                        
                                                                $rp_nilai_uraian=$this->finance->toRupiah($nilaiuraian); 
                                                                $rp_target=$this->finance->toRupiah($target);
                                                                $rp_realisasi=$this->finance->toRupiah($realisasi);
                                                                $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa);
                                                                $rp_dalam_kas=$this->finance->toRupiah($dalamKas); 
                                                                
                                                                $content.= '<tr>';
                                                                $content.= '<td colspan="5"></td>';            
                                                                $url=$this->Service->constructUrl('m.report.formADetails',array('id'=>$iduraian));
                                                                $tetaut = "<a href=\"$url\">$nama_uraian</a>";
                                                                $content.= '<td class="left">--- '.$tetaut.'</td>';
                                                                $content.= '<td class="right">'.$rp_nilai_uraian.'</td>';
                                                                $content.= "<td class=\"center\">$volume</td>";
                                                                $content.= "<td class=\"center\">$persen_bobot</td>";
                                                                $content.= "<td class=\"right\">$rp_target</td>";
                                                                $content.= "<td class=\"right\">$persen_target</td>";
                                                                $content.= "<td class=\"right\">$rp_realisasi</td>";
                                                                $content.= "<td class=\"center\">$persen_rata2_realisasi</td>";
                                                                $content.= "<td class=\"center\">$persen_tertimbang_realisasi</td>";
                                                                $content.= "<td class=\"center\">$persen_rata2_fisik</td>";
                                                                $content.= "<td class=\"center\">$persen_tertimbang_fisik</td>";
                                                                $content.= "<td class=\"right\">$rp_dalam_dpa</td>";
                                                                $content.= "<td class=\"center\">$rp_dalam_kas</td>";                                                        
                                                                $content.= '</tr>';	                                                        
                                                            }
                                                        }															
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }						
                        }
                        break;
                    }
                    continue;
                }
            }                    
            $rp_total_pagu_dana=$this->finance->toRupiah($totalPaguDana);	
            $rp_total_target=$this->finance->toRupiah ($totalTarget_rek3);			
            $rp_total_realisasi=$this->finance->toRupiah ($totalRealisasi_rek3);            
            $rp_total_dalam_dpa=$this->finance->toRupiah($totalPaguDana-$totalTarget_rek3);
            $rp_total_dalam_kas=$this->finance->toRupiah($totalTarget_rek3-$totalRealisasi_rek3);                                    
            $content.= '<tr>';
            $content.= '<td colspan="6"><strong>Jumlah</strong></td>';
            $content.= '<td class="right">'.$rp_total_pagu_dana.'</td>';			
            $content.= '<td class="center">&nbsp;</td>';
            $content.= '<td class="center">'.$total_persen_bobot_rek3.'&nbsp;</td>';
            $content.= '<td class="right">'.$rp_total_target.'</td>';	
            $total_persen_target=number_format(($totalTarget_rek3/$totalPaguDana)*100,2);
            $content.= '<td class="right">'.$total_persen_target.'</td>';
            $content.= '<td class="right">'.$rp_total_realisasi.'</td>';		
            $total_persen_rata2_realisasi=number_format(($totalRealisasi_rek3/$totalPaguDana)*100,2);
            $content.= '<td class="center">'.$total_persen_rata2_realisasi.'</td>';								
            $content.= '<td class="center">'.$total_persen_tertimbang_rek3.'</td>';
            $content.= '<td class="center">'.number_format($total_persen_rata2_fisik_rek3/$totalUraian,2).'</td>';                        
            $content.= '<td class="center">'.$total_persen_fisik_tertimbang_rek3.'</td>';            
            $content.= '<td class="right">'.$rp_total_dalam_dpa.'</td>';	
            $content.= '<td class="center">'.$rp_total_dalam_kas.'</td>';	
            $content.= '</tr></table>';		

            return $content;
        }else {
            $this->btnPrint->Enabled=false;
            return "<p class=\"msg info\">
                        Belum ada Realisasi Fisik dan Keuangan sampai dengan bulan dan tahun anggaran $no_bulan/$tahun.</p>";
        }
	}
	public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $this->createObjReport();        
        $this->report->dataKegiatan=$_SESSION['currentPageFormA']['dataKegiatan'];
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];
        $this->report->dataKegiatan['bulanrealisasi']=$_SESSION['bulanrealisasi'];
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printFormA();
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printFormA();                
            break;
        }        
    }
   public function printFormA() {
        $datakegiatan=$_SESSION['currentPageFormA']['dataKegiatan'];        
        $nama_unit=$_SESSION['currentPageFormA']['dataKegiatan']['nama_unit'];
		$no_bulan=$_SESSION['bulanrealisasi'];
		$tahun=$_SESSION['ta'];
        switch ($this->report->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->report->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->report->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                $row=1;
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->getStyle("T$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",'FORMULIR A');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:U$row");				                
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'LAPORAN BULANAN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:U$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'REALISASI PELAKSANAAN KEGIATAN PEMBANGUNAN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:U$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'ANGGARAN PENDAPATAN DAN BELANJA DAERAH (APBD)');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:U$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row","KABUPATEN BINTAN TAHUN ANGGARAN $tahun");
                $row+=2;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:U$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",$nama_unit);
                $styleArray=array( 
								'font' => array('bold' => true,'size'=>'11'),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),								
							);
                $this->report->rpt->getActiveSheet()->getStyle("A1:A$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(35);
                $this->report->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(12);
                $this->report->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(12);
                $this->report->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(17);
                $this->report->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(7);
                $this->report->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(17);
                $this->report->rpt->getActiveSheet()->getColumnDimension('N')->setWidth(12);
                $this->report->rpt->getActiveSheet()->getColumnDimension('O')->setWidth(12);
                $this->report->rpt->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
                $this->report->rpt->getActiveSheet()->getColumnDimension('R')->setWidth(17);
                $this->report->rpt->getActiveSheet()->getColumnDimension('S')->setWidth(17);
                $this->report->rpt->getActiveSheet()->getColumnDimension('T')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('U')->setWidth(20);
                
                $row+=1;
                $nama_bulan=$this->TGL->getMonth(4,$no_bulan);
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->getStyle("T$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $this->report->rpt->getActiveSheet()->setCellValue("T$row","POSISI S.D $nama_bulan $tahun");
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'I');
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'DATA UMUM');
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'a.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Program');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['nama_program']);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'b.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Kegiatan');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['nama_proyek']);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'c.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Tolak Ukur');
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'1)');
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'Keluaran');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['keluaran']);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'2)');
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'Hasil');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['hasil']);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'d.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Sifat Kegiatan');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['sifat_kegiatan']);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'e.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Jumlah Biaya');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$this->finance->toRupiah($datakegiatan['nilai_pagu']));
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'f.');
                $this->report->rpt->getActiveSheet()->setCellValue("C$row",'Waktu Pelaksanaan');
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",':');
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",$datakegiatan['waktu_pelaksanaan']);
                
                $row+=2;
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'II.');
                $this->report->rpt->getActiveSheet()->setCellValue("B$row",'REALISASI FISIK DAN KEUANGAN');
                
                $row+=1;
                $row_akhir=$row+2;                            
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:E$row_akhir");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'KODE REKENING');
                
                $row_akhir=$row+2;                                     
                $this->report->rpt->getActiveSheet()->mergeCells("F$row:F$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'URAIAN');                
                
                $row_akhir=$row+1;                                     
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",'PAGU DANA');
                $row_akhir=$row+2;                                            
                $this->report->rpt->getActiveSheet()->mergeCells("G$row_akhir:H$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("G$row_akhir",'Rp.');
                
                $row_akhir=$row+1;                     
                $this->report->rpt->getActiveSheet()->mergeCells("I$row:I$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("I$row",'VOLUME');
                $row_akhir=$row+2;                                                            
                $this->report->rpt->getActiveSheet()->setCellValue("I$row_akhir",'Unit, Paket');
                
                $row_akhir=$row+1;                               
                $this->report->rpt->getActiveSheet()->mergeCells("J$row:J$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("J$row",'BOBOT');
                $row_akhir=$row+2;                                            
                $this->report->rpt->getActiveSheet()->setCellValue("J$row_akhir",'%');
                
                $row_akhir=$row+1;                               
                $this->report->rpt->getActiveSheet()->mergeCells("K$row:O$row");
                $this->report->rpt->getActiveSheet()->setCellValue("K$row",'KEUANGAN');
                $this->report->rpt->getActiveSheet()->mergeCells("K$row_akhir:L$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("K$row_akhir",'TARGET (SPM)');
                $this->report->rpt->getActiveSheet()->mergeCells("M$row_akhir:O$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("M$row_akhir",'REALISASI (SPJ)');
                $row_akhir=$row+2;                                            
                $this->report->rpt->getActiveSheet()->setCellValue("K$row_akhir",'Rp.');
                $this->report->rpt->getActiveSheet()->setCellValue("L$row_akhir",'%');
                $this->report->rpt->getActiveSheet()->setCellValue("M$row_akhir",'Rp.');
                $this->report->rpt->getActiveSheet()->setCellValue("N$row_akhir",'% Rata-rata');
                $this->report->rpt->getActiveSheet()->setCellValue("O$row_akhir",'% Tertimbang');
                
                $row_akhir=$row+1;                               
                $this->report->rpt->getActiveSheet()->mergeCells("P$row:Q$row_akhir");                
                $this->report->rpt->getActiveSheet()->setCellValue("P$row",'REALISASI FISIK PEKERJAAN');
                $row_akhir=$row+2;                                                            
                $this->report->rpt->getActiveSheet()->setCellValue("P$row_akhir",'% Rata-rata');
                $this->report->rpt->getActiveSheet()->setCellValue("Q$row_akhir",'% Tertimbang');
                
                $row_akhir=$row+1;               
                $this->report->rpt->getActiveSheet()->mergeCells("R$row:S$row_akhir");                
                $this->report->rpt->getActiveSheet()->setCellValue("R$row",'SISA ANGGARAN');
                $row_akhir=$row+2;                                                                            
                $this->report->rpt->getActiveSheet()->setCellValue("R$row_akhir",'DPA');
                $this->report->rpt->getActiveSheet()->setCellValue("S$row_akhir",'KAS');
                
                $row_akhir=$row+2;                               
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:T$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",'PERMASALAHAN');
                
                $row_akhir=$row+2;                               
                $this->report->rpt->getActiveSheet()->mergeCells("U$row:U$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("U$row",'PEMECAHAN MASALAH');
                
                
                $row_akhir=$row+3;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row_akhir:E$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');
                $this->report->rpt->getActiveSheet()->setCellValue("F$row_akhir",'2');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row_akhir:H$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("G$row_akhir",'3');                
                $this->report->rpt->getActiveSheet()->setCellValue("I$row_akhir",'4');
                $this->report->rpt->getActiveSheet()->setCellValue("J$row_akhir",'5');
                $this->report->rpt->getActiveSheet()->setCellValue("K$row_akhir",'6a');
                $this->report->rpt->getActiveSheet()->setCellValue("L$row_akhir",'6b');
                $this->report->rpt->getActiveSheet()->setCellValue("M$row_akhir",'6c');
                $this->report->rpt->getActiveSheet()->setCellValue("N$row_akhir",'6d');
                $this->report->rpt->getActiveSheet()->setCellValue("O$row_akhir",'6e');
                $this->report->rpt->getActiveSheet()->setCellValue("P$row_akhir",'7');
                $this->report->rpt->getActiveSheet()->setCellValue("Q$row_akhir",'8');
                $this->report->rpt->getActiveSheet()->setCellValue("R$row_akhir",'9');
                $this->report->rpt->getActiveSheet()->setCellValue("S$row_akhir",'10');
                $this->report->rpt->getActiveSheet()->setCellValue("T$row_akhir",'11');
                $this->report->rpt->getActiveSheet()->setCellValue("U$row_akhir",'12');                               
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:U$row_akhir")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:U$row_akhir")->getAlignment()->setWrapText(true);
                $this->report->rpt->getActiveSheet()->setTitle ('Laporan A');
                
                $dataproyek=$this->getDataProyek();           
                $tingkat = $this->getRekeningProyek();         
                if (isset($tingkat[1])) {                                        
                    $tingkat_1=$tingkat[1];            
                    $tingkat_2=$tingkat[2];
                    $tingkat_3=$tingkat[3];
                    $tingkat_4=$tingkat[4];
                    $tingkat_5=$tingkat[5];
                    $row+=4;               
                    $row_awal=$row;
                    while (list($k1,$v1)=each($tingkat_1)) {
                        foreach ($tingkat_5 as $k5=>$v5) {
                            $rek1=substr($k5,0,1);
                            if ($rek1 == $k1) {
                                //tingkat i
                                $totalPaguDana_Rek1=$this->calculateEachLevel($dataproyek,$k1,'no_rek1'); 
                                $totalPaguDana=$totalPaguDana_Rek1['totalpagu'];
                                $rp_total_pagu_dana_rek1=$this->finance->toRupiah($totalPaguDana);
                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$k1,PHPExcel_Cell_DataType::TYPE_STRING);
                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v1);	
                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                $this->report->rpt->getActiveSheet()->setCellValue("G$row",$rp_total_pagu_dana_rek1);
                                $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                $row+=1;
                                //tingkat ii
                                foreach ($tingkat_2 as $k2=>$v2) {
                                    $rek2_tampil=array();
                                    foreach ($tingkat_5 as $k5_level2=>$v5_level2) {
                                        $rek2=substr($k5_level2,0,3);								
                                        if ($rek2 == $k2) {
                                            if (!array_key_exists($k2,$rek2_tampil)){															
                                                $rek2_tampil[$rek2]=$v2;
                                            }							
                                        }
                                    }
                                    foreach ($rek2_tampil as $a=>$b) {
                                        $totalPaguDana_Rek2=$this->calculateEachLevel($dataproyek,$a,'no_rek2'); 
                                        $no_=explode ('.',$a);
                                        $rp_total_pagu_dana_rek2=$this->finance->toRupiah($totalPaguDana_Rek2['totalpagu']);                                                                            
                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);
                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);
                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$b);	
                                        $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                        $this->report->rpt->getActiveSheet()->setCellValue("G$row",$rp_total_pagu_dana_rek2);
                                        $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                        $row+=1;
                                        //tingkat iii
                                        foreach ($tingkat_3 as $k3=>$v3) {	
                                            $rek3=substr($k3,0,3);
                                            if ($a==$rek3) {
                                                $jumlahrek3+=1;
                                                $totalPaguDana_Rek3=$this->calculateEachLevel($dataproyek,$k3,'no_rek3');
                                                $no_=explode (".",$k3);
                                                $rp_total_pagu_dana_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalpagu']);
                                                $persen_bobot_rek3=number_format(($totalPaguDana_Rek3['totalpagu']/$totalPaguDana)*100,2);
                                                $total_persen_bobot_rek3+=$persen_bobot_rek3;
                                                $rp_total_target_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totaltarget']);
                                                $totalTarget_rek3+=$totalPaguDana_Rek3['totaltarget'];
                                                $persen_target_rek3=number_format(($totalPaguDana_Rek3['totaltarget']/$totalPaguDana_Rek3['totalpagu'])*100,2);
                                                $rp_total_realisasi_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalrealisasi']);
                                                $totalRealisasi_rek3+=$totalPaguDana_Rek3['totalrealisasi'];
                                                $persen_realisasi_rek3=number_format(($totalPaguDana_Rek3['totaltarget']/$totalPaguDana_Rek3['totalpagu'])*100,2);
                                                $persen_tertimbang_rek3=number_format(($persen_realisasi_rek3*$persen_bobot_rek3)/100,2);
                                                $total_persen_tertimbang_rek3+=$persen_tertimbang_rek3;                                                                                
                                                $persen_rata2_fisik_rek3=$totalPaguDana_Rek3['totalfisik'];                                                
                                                $total_persen_rata2_fisik_rek3+=$persen_rata2_fisik_rek3;
                                                $persen_tertimbang_fisik_rek3=number_format(($persen_rata2_fisik_rek3*$persen_bobot_rek3)/100,2);
                                                $total_persen_fisik_tertimbang_rek3+=$persen_tertimbang_fisik_rek3;
                                                $dalamDpa_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totalpagu']-$totalPaguDana_Rek3['totaltarget']);                                                
                                                $dalamKas_rek3=$this->finance->toRupiah($totalPaguDana_Rek3['totaltarget']-$totalPaguDana_Rek3['totalrealisasi']);
                                                
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v3);
                                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("J$row",$persen_bobot_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_total_target_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("L$row",$persen_target_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_total_realisasi_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("N$row",$persen_realisasi_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("O$row",$persen_tertimbang_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("P$row",$persen_rata2_fisik_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("Q$row",$persen_tertimbang_fisik_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$dalamDpa_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$dalamKas_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                
                                                $this->report->rpt->getActiveSheet()->getStyle("A$row:S$row")->getFont()->setBold(true);
                                                $row+=1;                                              
                                                foreach ($tingkat_4 as $k4=>$v4) {
                                                    if (ereg ($k3,$k4)) {
                                                        $totalPaguDana_Rek4=$this->calculateEachLevel($dataproyek,$k4,'no_rek4');																																			
                                                        $rp_total_pagu_dana_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalpagu']);
                                                        $no_=explode (".",$k4);
                                                        $persen_bobot_rek4=number_format(($totalPaguDana_Rek4['totalpagu']/$totalPaguDana)*100,2);
                                                        $rp_total_target_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totaltarget']);
                                                        $persen_target_rek4=number_format(($totalPaguDana_Rek4['totaltarget']/$totalPaguDana_Rek4['totalpagu'])*100,2);
                                                        $rp_total_realisasi_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalrealisasi']);
                                                        $persen_realisasi_rek4=number_format(($totalPaguDana_Rek4['totaltarget']/$totalPaguDana_Rek4['totalpagu'])*100,2);
                                                        $persen_tertimbang_rek4=number_format(($persen_realisasi_rek4*$persen_bobot_rek4)/100,2);
                                                        $persen_rata2_fisik_rek4=$totalPaguDana_Rek4['totalfisik'];                                                
                                                        $persen_tertimbang_fisik_rek4=number_format(($persen_rata2_fisik_rek4*$persen_bobot_rek4)/100,2);
                                                        $dalamDpa_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totalpagu']-$totalPaguDana_Rek4['totaltarget']);                                                
                                                        $dalamKas_rek4=$this->finance->toRupiah($totalPaguDana_Rek4['totaltarget']-$totalPaguDana_Rek4['totalrealisasi']);
                                                        
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v4);
                                                        $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("J$row",$persen_bobot_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_total_target_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("L$row",$persen_target_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_total_realisasi_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("N$row",$persen_realisasi_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("O$row",$persen_tertimbang_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("P$row",$persen_rata2_fisik_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("Q$row",$persen_tertimbang_fisik_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$dalamDpa_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$dalamKas_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->getStyle("A$row:S$row")->getFont()->setBold(true);
                                                        $row+=1;
                                                        foreach ($tingkat_5 as $k5=>$v5) {
                                                            if (ereg ($k4,$k5)) {
                                                                $totalUraian+=1;                                                                
                                                                $totalPaguDana_Rek5=$this->calculateEachLevel($dataproyek,$k5,'no_rek5');													
                                                                $rp_total_pagu_dana_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalpagu']);       
                                                                $iduraian=$dataproyek[$k5]['iduraian'];
                                                                $nama_uraian=$dataproyek[$k5]['nama_uraian']; 
                                                                $no_=explode (".",$k5);    
                                                                $persen_bobot_rek5=number_format(($totalPaguDana_Rek5['totalpagu']/$totalPaguDana)*100,2);
                                                                $rp_total_target_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totaltarget']);
                                                                $persen_target_rek5=number_format(($totalPaguDana_Rek5['totaltarget']/$totalPaguDana_Rek5['totalpagu'])*100,2);
                                                                $rp_total_realisasi_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalrealisasi']);
                                                                $persen_realisasi_rek5=number_format(($totalPaguDana_Rek5['totaltarget']/$totalPaguDana_Rek5['totalpagu'])*100,2);
                                                                $persen_tertimbang_rek5=number_format(($persen_realisasi_rek5*$persen_bobot_rek5)/100,2);
                                                                $persen_rata2_fisik_rek5=$totalPaguDana_Rek5['totalfisik'];                                                
                                                                $persen_tertimbang_fisik_rek5=number_format(($persen_rata2_fisik_rek5*$persen_bobot_rek5)/100,2);
                                                                $dalamDpa_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totalpagu']-$totalPaguDana_Rek5['totaltarget']);                                                
                                                                $dalamKas_rek5=$this->finance->toRupiah($totalPaguDana_Rek5['totaltarget']-$totalPaguDana_Rek5['totalrealisasi']);
                                                                
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("E$row",$no_[4],PHPExcel_Cell_DataType::TYPE_STRING);                                                                
                                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v5);
                                                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("J$row",$persen_bobot_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_total_target_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("L$row",$persen_target_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_total_realisasi_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("N$row",$persen_realisasi_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("O$row",$persen_tertimbang_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("P$row",$persen_rata2_fisik_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("Q$row",$persen_tertimbang_fisik_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$dalamDpa_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$dalamKas_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                
                                                                $nilaiuraian=$dataproyek[$k5]['nilai']; 
                                                                $target=$dataproyek[$k5]['target'];                                                                 
                                                                $realisasi=$dataproyek[$k5]['realisasi'];                                                                                                                        
                                                                $fisik=$dataproyek[$k5]['fisik'];
                                                                $volume=$dataproyek[$k5]['volume'];
                                                                $persen_bobot=number_format(($nilaiuraian/$totalPaguDana)*100,2);                                                                
                                                                $persen_target=number_format(($target/$nilaiuraian)*100,2);   
                                                                $persen_rata2_realisasi=number_format(($realisasi/$nilaiuraian)*100,2);
                                                                $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);                                                                
                                                                $persen_rata2_fisik=$fisik;                                                                
                                                                $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);
                                                                $dalamDpa=$nilaiuraian-$target;                                                                
                                                                $dalamKas=$target-$realisasi;                                                                

                                                                $rp_nilai_uraian=$this->finance->toRupiah($nilaiuraian); 
                                                                $rp_target=$this->finance->toRupiah($target);
                                                                $rp_realisasi=$this->finance->toRupiah($realisasi);
                                                                $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa);
                                                                $rp_dalam_kas=$this->finance->toRupiah($dalamKas);
                                                                
                                                                $row+=1;
                                                                $this->report->rpt->getActiveSheet()->mergeCells("A$row:E$row");
                                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$nama_uraian);                                                                        
                                                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_uraian,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("I$row",$volume);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("J$row",$persen_bobot);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_target,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("L$row",$persen_target);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("N$row",$persen_rata2_realisasi);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("O$row",$persen_tertimbang_realisasi);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("P$row",$persen_rata2_fisik);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("Q$row",$persen_tertimbang_fisik);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$rp_dalam_dpa,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$rp_dalam_kas,PHPExcel_Cell_DataType::TYPE_STRING);                                                                                                                                                                                               
                                                                if (isset($dataproyek[$k5]['child'][0])) {                    
                                                                    $row+=1;                                                                             
                                                                    $child=$dataproyek[$k5]['child'];                    
                                                                    foreach ($child as $n) {
                                                                        $iduraian=$n['iduraian'];
                                                                        $totalUraian+=1;
                                                                        $nama_uraian=$n['nama_uraian'];
                                                                        $nilaiuraian=$n['nilai']; 
                                                                        $target=$n['target'];                 
                                                                        $fisik=$n['fisik'];                 
                                                                        $realisasi=$n['realisasi'];                                                                            
                                                                        $volume=$n['volume'];
                                                                        $persen_bobot=number_format(($nilaiuraian/$totalPaguDana)*100,2);                                                                        
                                                                        $persen_target=number_format(($target/$nilaiuraian)*100,2);   
                                                                        $persen_rata2_realisasi=number_format(($realisasi/$nilaiuraian)*100,2);
                                                                        $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);                                                                        
                                                                        $persen_rata2_fisik=$fisik;                                                                        
                                                                        $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);
                                                                        $dalamDpa=$nilaiuraian-$target;                                                                        
                                                                        $dalamKas=$target-$realisasi;                                                                        

                                                                        $rp_nilai_uraian=$this->finance->toRupiah($nilaiuraian); 
                                                                        $rp_target=$this->finance->toRupiah($target);
                                                                        $rp_realisasi=$this->finance->toRupiah($realisasi);
                                                                        $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa);
                                                                        $rp_dalam_kas=$this->finance->toRupiah($dalamKas);                                                                         
                                                                        
                                                                        $this->report->rpt->getActiveSheet()->mergeCells("A$row:E$row");
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$nama_uraian);                                                                        
                                                                        $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_uraian,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("I$row",$volume);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("J$row",$persen_bobot);
                                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_target,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("L$row",$persen_target);
                                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("N$row",$persen_rata2_realisasi);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("O$row",$persen_tertimbang_realisasi);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("P$row",$persen_rata2_fisik);
                                                                        $this->report->rpt->getActiveSheet()->setCellValue("Q$row",$persen_tertimbang_fisik);
                                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$rp_dalam_dpa,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$rp_dalam_kas,PHPExcel_Cell_DataType::TYPE_STRING);                                                               
                                                                        $row+=1;                                                                        
                                                                    }
                                                                    $row-=1;
                                                                }
                                                                $row+=1;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }						
                                }
                                break;
                            }
                            continue;
                        }
                    }
                }
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:U$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:U$row")->getAlignment()->setWrapText(true);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("F$row_awal:F$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("G$row_awal:G$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("K$row_awal:K$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("M$row_awal:M$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("R$row_awal:S$row")->applyFromArray($styleArray);                
                
                $rp_total_pagu_dana=$this->finance->toRupiah($totalPaguDana);	
                $rp_total_target=$this->finance->toRupiah ($totalTarget_rek3);			
                $rp_total_realisasi=$this->finance->toRupiah ($totalRealisasi_rek3);
                $total_dalam_dpa=$totalPaguDana-$totalTarget_rek3;
                $rp_total_dalam_dpa=$this->finance->toRupiah($total_dalam_dpa);
                $total_dalam_kas=$totalTarget_rek3-$totalRealisasi_rek3;
                $rp_total_dalam_kas=$this->finance->toRupiah($total_dalam_kas);
               
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:F$row");
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValue("J$row",$total_persen_bobot_rek3);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_total_target,PHPExcel_Cell_DataType::TYPE_STRING);
                $total_persen_target=number_format(($totalTarget_rek3/$totalPaguDana)*100,2);
                $this->report->rpt->getActiveSheet()->setCellValue("L$row",$total_persen_target);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_total_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                $total_persen_rata2_realisasi=number_format(($totalRealisasi_rek3/$totalPaguDana)*100,2);
                $this->report->rpt->getActiveSheet()->setCellValue("N$row",$total_persen_rata2_realisasi);
                $this->report->rpt->getActiveSheet()->setCellValue("O$row",$total_persen_tertimbang_rek3);
                $total_persen_rata2_fisik_rek3=number_format($total_persen_rata2_fisik_rek3/$totalUraian,2);
                $this->report->rpt->getActiveSheet()->setCellValue("P$row",$total_persen_rata2_fisik_rek3);                
                $this->report->rpt->getActiveSheet()->setCellValue("Q$row",$total_persen_fisik_tertimbang_rek3);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$rp_total_dalam_dpa,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$rp_total_dalam_kas,PHPExcel_Cell_DataType::TYPE_STRING);                                                                
                $this->report->rpt->getActiveSheet()->getStyle("A$row:S$row")->getFont()->setBold(true);
                
                $row+=3;
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'Kinerja :');
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",'Tanjungpinang, '.$this->TGL->tanggal('d F Y'));
                
                $row+=1;
                $row_awal=$row;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'No.');
                                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Uraian');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:I$row");
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",'Jumlah');
                            
                
                $this->report->rpt->getActiveSheet()->mergeCells("N$row:P$row");
                $this->report->rpt->getActiveSheet()->setCellValue("N$row",'Pengguna Anggaran');
                
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",'Pejabat Pelaksana Teknis Kegiatan');                
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'1');                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Posisi Bulan/Tahun');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row","$nama_bulan $tahun");
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'2');                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Pagu Dana Belanja Langsung');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'3');                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Realisasi Keuangan (Akumulatif) Rp. (%)');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("I$row",$total_persen_rata2_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'4');                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Realisasi Fisik (Akumulatif) (%)');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$total_persen_rata2_fisik_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("D$row:E$row");
                $this->report->rpt->getActiveSheet()->setCellValue("D$row",'5');                
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'Sisa Dana (Rp)');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$this->finance->toRupiah($total_dalam_dpa+$total_dalam_kas),PHPExcel_Cell_DataType::TYPE_STRING);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("D$row_awal:I$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("D$row_awal:I$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );											
                $row_awal+=1;
                $this->report->rpt->getActiveSheet()->getStyle("F$row_awal:F$row")->applyFromArray($styleArray); 
                
                $this->report->rpt->getActiveSheet()->mergeCells("N$row:P$row");
                $this->report->rpt->getActiveSheet()->setCellValue("N$row",$_SESSION['currentPageFormA']['dataKegiatan']['nama_pengguna_anggaran']);           
                
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",$_SESSION['currentPageFormA']['dataKegiatan']['nama_pptk']);
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("N$row:P$row");
                $this->report->rpt->getActiveSheet()->setCellValue("N$row",'Nip.'.$this->nipFormat($_SESSION['currentPageFormA']['dataKegiatan']['nip_pengguna_anggaran']));
                
                $this->report->rpt->getActiveSheet()->mergeCells("T$row:U$row");
                $this->report->rpt->getActiveSheet()->setCellValue("T$row",'Nip.'.$this->nipFormat($_SESSION['currentPageFormA']['dataKegiatan']['nip_pptk']));
                
                
                $this->report->printOut('FormA');                
                $this->report->setLink($this->linkOutput,'FormA');
            break;
        }
    }
}
?>

