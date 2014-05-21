<?php
prado::using ('Application.pages.s.report.MainPageReports');
class formA extends MainPageReports {	
    public $result=array();
	public $dataReport;	
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
                if (!isset($_SESSION['currentPageFormA'])||$_SESSION['currentPageFormA']['page_name']!='s.report.formA') {
                    $_SESSION['currentPageFormA']=array('page_name'=>'s.report.formA','page_num'=>0,'dataKegiatan'=>array(),'idprogram'=>'none','search'=>false,'userid'=>$this->userid);												
                }
                $tahun=$this->session['ta'];
                $idunit=$this->idunit;
                $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);		
                $result['none']='Keseluruhan Program';
                $this->cmbProgram->DataSource=$result;
                $this->cmbProgram->Text=$_SESSION['currentPageFormA']['idprogram'];
                $this->cmbProgram->dataBind();
                
                $daftar_user=$this->kegiatan->getList("user WHERE page='s' OR page='m'",array('userid','username'),'username',null,1);
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
        $userid=$_SESSION['currentPageFormA']['userid'];
        $tahun=$this->session['ta'];        
        $bulan=$this->session['bulanrealisasi'];
        $idprogram=$_SESSION['currentPageFormA']['idprogram'];
        $str_kode_program=$idprogram=='none'?'':"AND idprogram=$idprogram";        
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";
        $str_jumlah="proyek WHERE tahun_anggaran=$tahun $str_kode_program $str_kode_user";        
        $str_baris = "SELECT idproyek,kode_proyek,nama_proyek FROM proyek p WHERE tahun_anggaran=$tahun $str_kode_program $str_kode_user";                
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
        $this->redirect('s.report.formA');
                
    }
	private function initialization () {	
		$idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];	
        $userid=$_SESSION['currentPageFormA']['userid'];	
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";
		$str = "SELECT kp.nama_kuasa_pengguna,kp.nip_kuasa_pengguna,pp.nip_pptk,pp.nama_pptk FROM proyek p, kuasa_pengguna kp,pptk pp WHERE p.nip_kuasa_pengguna=kp.nip_kuasa_pengguna AND p.nip_pptk=pp.nip_pptk AND p.idproyek='$idproyek' $str_kode_user";
		$this->DB->setFieldTable (array('nama_kuasa_pengguna','nip_kuasa_pengguna','nip_pptk','nama_pptk'));
		$r=$this->DB->getRecord ($str);		
		$this->dataReport['nama_kuasa_pengguna']=$r[1]['nama_kuasa_pengguna'];
		$this->dataReport['nip_kuasa_pengguna']=$r[1]['nip_kuasa_pengguna'];
		$this->dataReport['nama_pptk']=$r[1]['nama_pptk'];
		$this->dataReport['nip_pptk']=$r[1]['nip_pptk']; 		        
	}
	public function closeView ($sender,$param) {
		unset($_SESSION['currentPageFormA']['dataKegiatan']);
		$this->redirect('s.report.formA');
	}	
	/**
	* digunakan untuk mendapatkan data proyek
	*	
	*/	
	private function getDataProyek () {
		$no_bulan=$this->session['bulanrealisasi'];
		$tahun=$this->session['ta'];
		$idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];	
        $userid=$_SESSION['currentPageFormA']['userid'];	
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";
		$this->DB->setFieldTable (array('no_rek1','nama_rek1','no_rek2','nama_rek2','no_rek3','nama_rek3','no_rek4','nama_rek4','no_rek5','nama_rek5','idproyek','kode_proyek','nama_proyek','nilai_pagu','iduraian','nama_uraian','satuan','nilai','realisasi','target','bulan_penggunaan','tahun_penggunaan','tahun_anggaran'));
		$str = "SELECT no_rek1,nama_rek1,no_rek2,nama_rek2,no_rek3,nama_rek3,no_rek4,nama_rek4,no_rek5,nama_rek5,idproyek,kode_proyek,nama_proyek,nilai_pagu,iduraian,nama_uraian,satuan,nilai,realisasi,target,bulan_penggunaan,tahun_penggunaan,tahun_anggaran FROM v_laporan_a WHERE bulan_penggunaan <= '$no_bulan' AND tahun_penggunaan='$tahun' AND idproyek='$idproyek' $str_kode_user ORDER BY no_rek5 ASC";
		$result = $this->DB->getRecord ($str); 			
		$dataAkhir=array();		
		if (isset($result[1])) {														
			foreach ($result as $de) {
				$no_rek5=trim($de['no_rek5']);											
				if (array_key_exists ($no_rek5,$dataAkhir) ) {											
					$dataAkhir[$no_rek5]['nilai']=$de['nilai'];					
					$dataAkhir[$no_rek5]['target']+=$de['target'];
					$dataAkhir[$no_rek5]['realisasi']+=$de['realisasi'];										
				}else {											
					$dataAkhir[$no_rek5]=array ("no_rek1"=>$de['no_rek1'],
	 											"nama_rek1"=>$de['nama_rek1'],
				 								"no_rek2"=>$de['no_rek2'],
												"nama_rek2"=>$de['nama_rek2'],
				 								"no_rek3"=>$de['no_rek3'],
												"nama_rek3"=>$de['nama_rek3'],
			 									"no_rek4"=>$de['no_rek4'],
												"nama_rek4"=>$de['nama_rek4'],
			 									"no_rek5"=>$de['no_rek5'],
												"nama_rek5"=>$de['nama_rek5'],
												"nilai"=>$de['nilai'],												
												"realisasi"=>$de['realisasi'],
												"target"=>$de['target']);
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
	private function getTotalPaguDana() {
		$r=$this->result;
        while (list($k,$v)=each($r)) {		
			$total+=$v['nilai'];
		}
		return $total;
	}	
	
	private function getTotalTarget() {
		$r=$this->result;
		foreach ($r as $k=>$v) {
			$total+=$v['target'];
		}
		return $total;
	}
	
	private function getTotalRealisasi() {
		$r=$this->result;
		foreach ($r as $k=>$v) {
			$total+=$v['realisasi'];
		}
		return $total;
	}
	private function getDpaKas ($idproyek,$bulan,$tahun) {
		$db=$this->DB;
		$dpa_kas=false;
        $userid=$this->userid;
		if (isset($this->result[1])) {
			$r=$this->result;
			if ($bulan > '01') {
				$db->setFieldTable (array('nilai','target','realisasi'));
				$str = "SELECT nilai,target,realisasi FROM v_laporan_a WHERE tahun_penggunaan='$tahun' AND idproyek='$idproyek' AND userid=$userid";				
				foreach ($r as $m=>$n) {
					$dpa=0;
					$kas=0;
					$str = $str . " AND no_rek5='$m'";
					$result=$db->getRecord($str);					
					foreach ($result as $k=>$v) {
						$paguDana+=$v['nilai'];
						$target=$target+$v['target'];
						$realisasi=$realisasi+$v['realisasi'];	 			
						$dpa=$paguDana-$target;				
						$kas=$target-$realisasi;						
					}					
					$dpa_kas[$k]=array("dpa"=>$dpa,"kas"=>$kas);
				}
				return $dpa_kas;
			}else {
				foreach ($r as $k=>$v) {
					$dpa=$v['nilai']-$v['target'];
					$kas=$v['target']-$v['realisasi'];
					$dpa_kas[$k]=array("dpa"=>$dpa,"kas"=>$kas);
				}
			}			
			return $dpa_kas;
		}
		return $dpa_kas;	
	}
	public function printContent() {
		$idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];
		$no_bulan=$this->session['bulanrealisasi'];
		$tahun=$this->session['ta'];
		$userid=$_SESSION['currentPageFormA']['userid'];	
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";
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
        $content.= '<td class="center">% Tertimbang</td>';		
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
//            $dpa_kas=$this->getDpaKas ($idproyek,$no_bulan,$tahun);
            $totalPaguDana=$this->getTotalPaguDana();
            $totalTarget=$this->getTotalTarget();
            $totalRealisasi=$this->getTotalRealisasi();        
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
                        $totalPaguDana_Rek1=0;
                        foreach ($dataproyek as $de) {
                            if ($k1==$de['no_rek1']) {
                                $totalPaguDana_Rek1+=$de['nilai'];
                            }
                        }
                        $rp_total_pagu_dana_rek1=$this->finance->toRupiah($totalPaguDana_Rek1,'tanparp');
                        $content.= '<tr>';
                        $content.= '<td width="10" class="center">'.$k1.'</td>';
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
                        $content.= '<td class="right"></td>';	
                        $content.= '<td class="right"></td>';	
                        $content.= '<td class="right"></td>';	
                        $content.= '<td class="right"></td>';	
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
                                $totalPaguDana_Rek2=0;
                                foreach ($dataproyek as $de) {
                                    if ($a==$de['no_rek2']) {
                                        $totalPaguDana_Rek2+=$de['nilai'];
                                    }
                                }
                                $no_=explode ('.',$a);
                                $rp_total_pagu_dana_rek2=$this->finance->toRupiah($totalPaguDana_Rek2,'tanparp');
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
                                $content.= '<td class="right"></td>';	
                                $content.= '<td class="right"></td>';	
                                $content.= '<td class="right"></td>';	
                                $content.= '<td class="right"></td>';	
                                $content.= '</tr>';

                                //tingkat iii
                                foreach ($tingkat_3 as $k3=>$v3) {	
                                    $rek3=substr($k3,0,3);
                                    if ($a==$rek3) {
                                        $totalPaguDana_Rek3=0;
                                        foreach ($dataproyek as $de) {
                                            if ($k3==$de['no_rek3']) {
                                                $totalPaguDana_Rek3+=$de['nilai'];
                                            }
                                        }
                                        $no_=explode (".",$k3);
                                        $rp_total_pagu_dana_rek3=$this->finance->toRupiah($totalPaguDana_Rek3,'tanparp');
                                        $content.= '<tr>';
                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';											
                                        $content.= '<td class="left">'.$v3.'</td>';									
                                        $content.= '<td class="right">'.$rp_total_pagu_dana_rek3.'</td>';																		
                                        $content.= '<td class="center">&nbsp;</td>';									
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';										
                                        $content.= '<td class="center">&nbsp;</td>';																		
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '</tr>';

                                        foreach ($tingkat_4 as $k4=>$v4) {
                                            if (ereg ($k3,$k4)) {
                                                $totalPaguDana_Rek4=0;
                                                $totalTarget_Rek4=0;
                                                $totalRealisasi_Rek4 = 0;
                                                foreach ($dataproyek as $de) {
                                                    if ($k4==$de['no_rek4']) {
                                                        $totalPaguDana_Rek4+=$de['nilai'];
                                                        $totalTarget_Rek4 += $de['target'];
                                                        $totalRealisasi_Rek4 += $de['realisasi'];
                                                    }
                                                }																																				
                                                $rp_total_pagu_dana_rek4=$this->finance->toRupiah($totalPaguDana_Rek4,"tanparp");
//                                                $rp_total_target_rek4=$this->finance->toRupiah($totalTarget_Rek4,"tanparp");
//                                                $rp_total_realisasi_rek4=$this->finance->toRupiah($totalRealisasi_Rek4,"tanparp");
                                                $no_=explode (".",$k4);

                                                $content.= '<tr>';
                                                $content.= '<td class="center">'.$no_[0].'.</td>';
                                                $content.= '<td class="center">'.$no_[1].'.</td>';
                                                $content.= '<td class="center">'.$no_[2].'.</td>';
                                                $content.= '<td class="center">'.$no_[3].'.</td>';											
                                                $content.= '<td class="center">&nbsp;</td>';		
                                                $content.= '<td class="left">'.$v4.'</td>';																					
                                                $content.= '<td class="right">'.$rp_total_pagu_dana_rek4.'</td>';														
                                                $content.= '<td class="right"></td>';							
                                                $content.= '<td class="center">&nbsp;</td>';
                                                $content.= '<td class="right"></td>';																						
                                                $content.= '<td class="right"></td>';																																												
                                                $content.= '<td class="right"></td>';																					
                                                $content.= '<td class="right"></td>';	
                                                $content.= '<td class="center">&nbsp;</td>';
                                                $content.= '<td class="right"></td>';	
                                                $content.= '<td class="right"></td>';	
                                                $content.= '<td class="right"></td>';	
                                                $content.= '<td class="right"></td>';	
                                                $content.= '</tr>';

                                                foreach ($tingkat_5 as $k5=>$v5) {
                                                    if (ereg ($k4,$k5)) {
                                                        $paguDana=$dataproyek[$k5]['nilai'];													
                                                        $target = $dataproyek[$k5]['target'];
                                                        $realisasi=$dataproyek[$k5]['realisasi'];                                                     													

                                                        $this->DB->setFieldTable (array('iduraian','target','realisasi','volume'));
                                                        $str = "SELECT iduraian,target,realisasi,CONCAT (volume,' ',satuan) AS volume FROM v_laporan_a WHERE bulan_penggunaan<='$no_bulan' AND tahun_penggunaan='$tahun' AND idproyek='$idproyek' AND no_rek5='$k5' $str_kode_user ORDER BY bulan_penggunaan ASC";			
                                                        $result=$this->DB->getRecord($str);
                                                        $totalTarget2=0;
                                                        $totalRealisasi2=0;
                                                        while (list($k,$v)=each($result)) {
                                                            $iduraian=$v['iduraian'];
                                                            $totalTarget2+=$v['target'];
                                                            $totalRealisasi2+=$v['realisasi'];	
                                                            $volume=$v['volume'];
                                                        }
                                                        $dalamDpa=$paguDana-$totalTarget2;
                                                        $dalamKas=$totalTarget2-$totalRealisasi2;
                                                        $totalDalamDpa+=$dalamDpa;
                                                        $totalDalamKas+=$dalamKas;
                                                        $no_=explode (".",$k5);															
                                                        $rp_total_pagu_dana_rek5=$this->finance->toRupiah($paguDana,'tanparp');
                                                        $rp_target=$this->finance->toRupiah($target,'tanparp');
                                                        $rp_realisasi=$this->finance->toRupiah($realisasi,'tanparp');
                                                        $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa,'tanparp');
                                                        $rp_dalam_kas=$this->finance->toRupiah($dalamKas,'tanparp');													                                                        
                                                        $persen_bobot=number_format(($paguDana/$totalPaguDana)*100,2);
                                                        $total_persen_bobot+=$persen_bobot;
                                                        
                                                        $persen_target=number_format(($target/$paguDana)*100,2);                                                        
                                                        $persen_rata2_realisasi=number_format(($realisasi/$paguDana)*100,2);
                                                        
                                                        $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);
                                                        $total_persen_tertimbang_realisasi+=$persen_tertimbang_realisasi;
                                                        
                                                        
                                                        $persen_rata2_fisik=number_format(($persen_tertimbang_realisasi/$persen_bobot)*100,2);
                                                        $total_persen_rata2_fisik+=$persen_rata2_fisik;
                                                        $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);                                                        

                                                        $content.= '<tr>';
                                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                                        $content.= '<td class="center">'.$no_[3].'.</td>';															
                                                        $content.= '<td class="center">'.$no_[4].'.</td>';	
                                                        $url=$this->Service->constructUrl('s.report.formADetails',array('id'=>$iduraian));
                                                        $surel = "<a href=\"$url\">$v5</a>";
                                                        $content.= '<td class="left">'.$surel.'</td>';												
                                                        $content.= '<td class="right">'.$rp_total_pagu_dana_rek5.'</td>';
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
                        break;
                    }
                    continue;
                }
            }
            $rp_total_pagu_dana=$this->finance->toRupiah($totalPaguDana,'tanparp');	
            $rp_total_target=$this->finance->toRupiah ($totalTarget,'tanparp');			
            $rp_total_realisasi=$this->finance->toRupiah ($totalRealisasi,'tanparp');            
            $rp_total_dalam_dpa=$this->finance->toRupiah($totalDalamDpa,'tanparp');
            $rp_total_dalam_kas=$this->finance->toRupiah($totalDalamKas,'tanparp');
            $total_persen_rata2_fisik=number_format($total_persen_rata2_fisik/count($tingkat_5),2);
            $content.= '<tr>';
            $content.= '<td colspan="6"><strong>Jumlah</strong></td>';
            $content.= '<td class="right">'.$rp_total_pagu_dana.'</td>';			
            $content.= '<td class="center">&nbsp;</td>';
            $content.= '<td class="center">'.$total_persen_bobot.'&nbsp;</td>';
            $content.= '<td class="right">'.$rp_total_target.'</td>';	
            $total_persen_target=number_format(($totalTarget/$totalPaguDana)*100,2);
            $content.= '<td class="right">'.$total_persen_target.'</td>';
            $content.= '<td class="right">'.$rp_total_realisasi.'</td>';		
            $total_persen_rata2_realisasi=number_format(($totalRealisasi/$totalPaguDana)*100,2);
            $content.= '<td class="center">'.$total_persen_rata2_realisasi.'</td>';								
            $content.= '<td class="center">'.$total_persen_tertimbang_realisasi.'</td>';
            $content.= '<td class="center">'.$total_persen_rata2_fisik.'</td>';
            $total_persen_fisik_tertimbang=number_format(($total_persen_rata2_fisik*$total_persen_bobot)/100,2);
            $content.= '<td class="center">'.$total_persen_fisik_tertimbang.'</td>';            
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
        $this->report->dataKegiatan['userid']=$this->userid;
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
        $datakegiatan=$this->session['currentPageFormA']['dataKegiatan'];
        $idproyek=$datakegiatan['idproyek'];
		$no_bulan=$this->session['bulanrealisasi'];
		$tahun=$this->session['ta'];
        $userid=$this->userid;
        switch ($this->report->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->report->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->report->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                $row=1;
                $this->report->rpt->getActiveSheet()->setCellValue("S$row",'FORMULIR A');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:V$row");				                
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'LAPORAN BULANAN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:V$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'REALISASI PELAKSANAAN KEGIATAN PEMBANGUNAN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:V$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'ANGGARAN PENDAPATAN DAN BELANJA DAERAH (APBD)');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:V$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row","PROVINSI KEPULAUAN RIAU TAHUN ANGGARAN $tahun");
                $row+=2;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:V$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'DINAS PENDIDIKAN PROVINSI KEPULAUAN RIAU');
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
                $this->report->rpt->getActiveSheet()->setCellValue("A$row","POSISI S.D $nama_bulan $tahun");
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
                
                $row_akhir=$row+2;                               
                $this->report->rpt->getActiveSheet()->mergeCells("V$row:V$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("V$row",'KET');
                
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
                $this->report->rpt->getActiveSheet()->setCellValue("V$row_akhir",'13');                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:V$row_akhir")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:V$row_akhir")->getAlignment()->setWrapText(true);
                $this->report->rpt->getActiveSheet()->setTitle ('Laporan A');
                
                $dataproyek=$this->getDataProyek();           
                $tingkat = $this->getRekeningProyek();        
                if (isset($tingkat[1])) {
                    $totalPaguDana=$this->getTotalPaguDana();
                    $totalTarget=$this->getTotalTarget();
                    $totalRealisasi=$this->getTotalRealisasi();        
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
                                $totalPaguDana_Rek1=0;
                                foreach ($dataproyek as $de) {
                                    if ($k1==$de['no_rek1']) {
                                        $totalPaguDana_Rek1+=$de['nilai'];
                                    }
                                }
                                $rp_total_pagu_dana_rek1=$this->finance->toRupiah($totalPaguDana_Rek1,'tanparp');
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
                                        $totalPaguDana_Rek2=0;
                                        foreach ($dataproyek as $de) {
                                            if ($a==$de['no_rek2']) {
                                                $totalPaguDana_Rek2+=$de['nilai'];
                                            }
                                        }
                                        $no_=explode ('.',$a);
                                        $rp_total_pagu_dana_rek2=$this->finance->toRupiah($totalPaguDana_Rek2,'tanparp');                                                                            
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
                                                $totalPaguDana_Rek3=0;
                                                foreach ($dataproyek as $de) {
                                                    if ($k3==$de['no_rek3']) {
                                                        $totalPaguDana_Rek3+=$de['nilai'];
                                                    }
                                                }
                                                $no_=explode (".",$k3);
                                                $rp_total_pagu_dana_rek3=$this->finance->toRupiah($totalPaguDana_Rek3,'tanparp');
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v3);
                                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek3,PHPExcel_Cell_DataType::TYPE_STRING);
                                                $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                                $row+=1;                                              
                                                foreach ($tingkat_4 as $k4=>$v4) {
                                                    if (ereg ($k3,$k4)) {
                                                        $totalPaguDana_Rek4=0;
                                                        $totalTarget_Rek4=0;
                                                        $totalRealisasi_Rek4 = 0;
                                                        foreach ($dataproyek as $de) {
                                                            if ($k4==$de['no_rek4']) {
                                                                $totalPaguDana_Rek4+=$de['nilai'];
                                                                $totalTarget_Rek4 += $de['target'];
                                                                $totalRealisasi_Rek4 += $de['realisasi'];
                                                            }
                                                        }																																				
                                                        $rp_total_pagu_dana_rek4=$this->finance->toRupiah($totalPaguDana_Rek4,"tanparp");
                                                        $no_=explode (".",$k4);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v4);
                                                        $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek4,PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                                        $row+=1;
                                                        foreach ($tingkat_5 as $k5=>$v5) {
                                                            if (ereg ($k4,$k5)) {
                                                                $paguDana=$dataproyek[$k5]['nilai'];													
                                                                $target = $dataproyek[$k5]['target'];
                                                                $realisasi=$dataproyek[$k5]['realisasi'];                                                     													

                                                                $this->DB->setFieldTable (array('iduraian','target','realisasi','volume'));
                                                                $str = "SELECT iduraian,target,realisasi,CONCAT (volume,' ',satuan) AS volume FROM v_laporan_a WHERE bulan_penggunaan<='$no_bulan' AND tahun_penggunaan='$tahun' AND idproyek='$idproyek' AND no_rek5='$k5' AND userid=$userid ORDER BY bulan_penggunaan ASC";			
                                                                $result=$this->DB->getRecord($str);
                                                                $totalTarget2=0;
                                                                $totalRealisasi2=0;
                                                                while (list($k,$v)=each($result)) {
                                                                    $iduraian=$v['iduraian'];
                                                                    $totalTarget2+=$v['target'];
                                                                    $totalRealisasi2+=$v['realisasi'];	
                                                                    $volume=$v['volume'];
                                                                }
                                                                $dalamDpa=$paguDana-$totalTarget2;
                                                                $dalamKas=$totalTarget2-$totalRealisasi2;
                                                                $totalDalamDpa+=$dalamDpa;
                                                                $totalDalamKas+=$dalamKas;
                                                                $no_=explode (".",$k5);															
                                                                $rp_total_pagu_dana_rek5=$this->finance->toRupiah($paguDana,'tanparp');
                                                                $rp_target=$this->finance->toRupiah($target,'tanparp');
                                                                $rp_realisasi=$this->finance->toRupiah($realisasi,'tanparp');
                                                                $rp_dalam_dpa=$this->finance->toRupiah($dalamDpa,'tanparp');
                                                                $rp_dalam_kas=$this->finance->toRupiah($dalamKas,'tanparp');													                                                        
                                                                $persen_bobot=number_format(($paguDana/$totalPaguDana)*100,2);
                                                                $total_persen_bobot+=$persen_bobot;

                                                                $persen_target=number_format(($target/$paguDana)*100,2);                                                        
                                                                $persen_rata2_realisasi=number_format(($realisasi/$paguDana)*100,2);

                                                                $persen_tertimbang_realisasi=number_format(($persen_rata2_realisasi*$persen_bobot)/100,2);
                                                                $total_persen_tertimbang_realisasi+=$persen_tertimbang_realisasi;


                                                                $persen_rata2_fisik=number_format(($persen_tertimbang_realisasi/$persen_bobot)*100,2);
                                                                $total_persen_rata2_fisik+=$persen_rata2_fisik;
                                                                $persen_tertimbang_fisik=number_format(($persen_rata2_fisik*$persen_bobot)/100,2);                                                        

                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("E$row",$no_[4],PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v5);
                                                                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana_rek5,PHPExcel_Cell_DataType::TYPE_STRING);
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
                //$row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:V$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:V$row")->getAlignment()->setWrapText(true);
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
                
                $rp_total_pagu_dana=$this->finance->toRupiah($totalPaguDana,'tanparp');	
                $rp_total_target=$this->finance->toRupiah ($totalTarget,'tanparp');			
                $rp_total_realisasi=$this->finance->toRupiah ($totalRealisasi,'tanparp');            
                $rp_total_dalam_dpa=$this->finance->toRupiah($totalDalamDpa,'tanparp');
                $rp_total_dalam_kas=$this->finance->toRupiah($totalDalamKas,'tanparp');
                $total_persen_rata2_fisik=number_format($total_persen_rata2_fisik/count($tingkat_5),2);
               
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:F$row");
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:H$row");
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_total_pagu_dana,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValue("J$row",$total_persen_bobot);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$rp_total_target,PHPExcel_Cell_DataType::TYPE_STRING);
                $total_persen_target=number_format(($totalTarget/$totalPaguDana)*100,2);
                $this->report->rpt->getActiveSheet()->setCellValue("L$row",$total_persen_target);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("M$row",$rp_total_realisasi,PHPExcel_Cell_DataType::TYPE_STRING);
                $total_persen_rata2_realisasi=number_format(($totalRealisasi/$totalPaguDana)*100,2);
                $this->report->rpt->getActiveSheet()->setCellValue("N$row",$total_persen_rata2_realisasi);
                $this->report->rpt->getActiveSheet()->setCellValue("O$row",$total_persen_tertimbang_realisasi);
                $this->report->rpt->getActiveSheet()->setCellValue("P$row",$total_persen_rata2_fisik);
                $total_persen_fisik_tertimbang=number_format(($total_persen_rata2_fisik*$total_persen_bobot)/100,2);
                $this->report->rpt->getActiveSheet()->setCellValue("Q$row",$total_persen_fisik_tertimbang);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("R$row",$rp_total_dalam_dpa,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("S$row",$rp_total_dalam_kas,PHPExcel_Cell_DataType::TYPE_STRING);
                                                                
                $this->report->rpt->getActiveSheet()->getStyle("A$row:S$row")->getFont()->setBold(true);
                $this->report->printOut('FormA');                
                $this->report->setLink($this->linkOutput,'FormA');
            break;
        }
    }
}
?>

