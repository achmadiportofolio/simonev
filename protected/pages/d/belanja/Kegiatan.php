<?php
prado::using ('Application.pages.d.belanja.MainPageBelanja');
class Kegiatan extends MainPageBelanja {	    
	public function onLoad ($param) {		
		parent::onLoad ($param);	        
        $this->showKegiatan=true;        
		if (!$this->IsCallback&&!$this->IsPostBack) {           
            if (!isset($_SESSION['currentPageKegiatan'])||$_SESSION['currentPageKegiatan']['page_name']!='d.belanja.Kegiatan') {
                $_SESSION['currentPageKegiatan']=array('page_name'=>'d.belanja.Kegiatan','page_num'=>0,'idprogram'=>'none','search'=>false,'userid'=>'none','dataKegiatan'=>array());												
            }            
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();

            $tahun=$this->session['ta'];
            $idunit=$this->idunit;
            $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);		
            $result['none']='Keseluruhan Program';
            $this->cmbProgram->DataSource=$result;
            $this->cmbProgram->Text=$_SESSION['currentPageKegiatan']['idprogram'];
            $this->cmbProgram->dataBind();

            $daftar_user=$this->kegiatan->getList("user WHERE (page='s' OR page='d') AND idunit=$idunit",array('userid','username'),'username',null,1);
            $daftar_user['none']='All';
            $this->cmbStaff->DataSource=$daftar_user;
            $this->cmbStaff->Text=$_SESSION['currentPageKegiatan']['userid'];
            $this->cmbStaff->dataBind();

            $_SESSION['currentPageKegiatan']['search']=false;
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
    public function changeProgramFilter ($sender,$param) {
        $_SESSION['currentPageKegiatan']['idprogram']=$this->cmbProgram->Text;
        $this->populateData($_SESSION['currentPageKegiatan']['search']);
    }
    public function changeStaffFilter ($sender,$param) {
        $_SESSION['currentPageKegiatan']['userid']=$this->cmbStaff->Text;
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
        $idunit=$this->idunit;
        $tahun=$this->session['ta'];        
        $bulan=$this->session['bulanrealisasi'];
        $idprogram=$_SESSION['currentPageKegiatan']['idprogram'];
        $str_kode_program=$idprogram=='none'?'':"AND p.idprogram=$idprogram";
        $userid=$_SESSION['currentPageKegiatan']['userid'];
        $str_kode_user = $userid =='none'?'':" AND userid=$userid";		
        if ($search) {
            $str_jumlah="proyek p JOIN program pr ON (pr.idprogram=p.idprogram) WHERE tahun_anggaran=$tahun AND pr.idunit=$idunit";
            $str_baris = "SELECT idproyek,kode_proyek,nama_proyek,nama_pptk,enabled FROM proyek p JOIN program pr ON (pr.idprogram=p.idprogram) LEFT JOIN pptk ON (pptk.nip_pptk=p.nip_pptk) WHERE tahun_anggaran=$tahun AND pr.idunit=$idunit";        
            $kriteria=$this->txtKriteria->Text;
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah AND kode_proyek LIKE '$kriteria%'";
                $str_baris = "$str_baris AND kode_proyek LIKE '$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah AND nama_proyek LIKE '%$kriteria%'";
                $str_baris = "$str_baris AND nama_proyek LIKE '%$kriteria%'";
            }                
        }else {
            $str_jumlah="proyek p JOIN program pr ON (pr.idprogram=p.idprogram) WHERE tahun_anggaran=$tahun AND pr.idunit=$idunit $str_kode_program $str_kode_user";
            $str_baris = "SELECT idproyek,kode_proyek,nama_proyek,nama_pptk,enabled FROM proyek p JOIN program pr ON (pr.idprogram=p.idprogram) LEFT JOIN pptk ON (pptk.nip_pptk=p.nip_pptk) WHERE tahun_anggaran=$tahun AND pr.idunit=$idunit $str_kode_program $str_kode_user";        
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
		$this->DB->setFieldTable (array('idproyek','kode_proyek','nama_proyek','nama_pptk','enabled'));	        
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
    public function setDataBound($sender,$param) {
		$item=$param->Item;		
		if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
			$proyek=$item->DataItem['nama_proyek'];
			$item->btnHapus->Attributes->OnClick="if(!confirm('Anda yakin mau menghapus data proyek $proyek?. Perhatian : seluruh data akan terhapus, yang terkait dengan kegiatan ini.')) return false;";
		}
	}	
	public function addProcess ($sender,$param) {
        $this->idProcess = 'add';
        $idunit=$this->idunit;
        $this->txtAddIdProgram->Value=$idunit;
        $tahun=$this->session['ta'];
        $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);		
        $this->cmbAddProgram->DataSource=$result;
        $this->cmbAddProgram->dataBind();
        $this->cmbAddProgram->Enabled=true;	
        
        $this->cmbAddSK->DataSource=$this->kegiatan->getSifatKegiatan();
		$this->cmbAddSK->dataBind();        
        
        $this->cmbAddPA->DataSource=$this->kegiatan->getList("pengguna_anggaran WHERE idunit=$idunit",array ('nip_pengguna_anggaran','nama_pengguna_anggaran'),'nama_pengguna_anggaran',null,1);
		$this->cmbAddPA->dataBind();		
		
		$this->cmbAddKP->DataSource=$this->kegiatan->getList("kuasa_pengguna WHERE idunit=$idunit",array ('nip_kuasa_pengguna','nama_kuasa_pengguna'),'nama_kuasa_pengguna',null,1);
		$this->cmbAddKP->dataBind();			
			
        $ppk=$this->kegiatan->getList("ppk WHERE idunit=$idunit",array ('nip_ppk','nama_ppk'),'nama_ppk',null,1);
		$this->cmbAddPPK->DataSource=$ppk;
		$this->cmbAddPPK->dataBind();
		
		$this->cmbAddPPTK->DataSource=$this->kegiatan->getList("pptk WHERE idunit=$idunit",array ('nip_pptk','nama_pptk'),'nama_pptk',null,1);
		$this->cmbAddPPTK->dataBind();
    }
    public function checkId ($sender,$param) {
        $tahun=$this->session['ta'];
		switch ($sender->getId ()) {
			case 'addKodeKegiatan' :
                $this->idProcess='add';
				$kode_kegiatan= $this->lblAddKodeProgram->Text.$this->txtAddKodeKegiatan->Text;
				$idprogram=$this->txtAddIdProgram->Value;		                
			break;
			case 'editKodeKegiatan' :    
                $this->idProcess='edit';
				$kode_kegiatan= $this->lblEditKodeProgram->Text.$this->txtEditKodeKegiatan->Text;
				$idprogram=$this->txtEditIdProgram->Value;				
				$kode_kegiatan2=$this->txtEditKodeKegiatan2->Value;
			break;
		}
		if ($kode_kegiatan != $kode_kegiatan2) {            
			if ($this->DB->checkRecordIsExist ('kode_proyek','proyek',$kode_kegiatan," AND tahun_anggaran=$tahun AND idprogram=$idprogram")) {
				$param->IsValid=false;					
			}
		}
	}
	public function changeDataMaster ($sender,$param) {
		$this->idProcess='add';		
        switch ($sender->getId()) {				
            case 'cmbAddProgram' :
                $this->disableAndEnabled();	
                $this->lblAddKodeProgram->Text=$this->kegiatan->getKodeProgramByID($this->cmbAddProgram->Text).'.';					
                $idprogram=$this->cmbAddProgram->Text;			
                $this->txtAddIdProgram->Value=$idprogram;
                if ($idprogram != 'none') {					
//                    $this->cmbAddNegara->Enabled=true;	
//                    $this->cmbAddNegara->DataSource=$this->kegiatan->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,1);			
//                    $this->cmbAddNegara->dataBind();
                    
                    $this->cmbAddNegara->Enabled=true;	
                    $this->cmbAddNegara->DataSource=array(2=>'INDONESIA');			
                    $this->cmbAddNegara->dataBind();
                    
                    $dt1=$this->kegiatan->getList("dt1 WHERE idnegara='2'",array('iddt1','nama_dt1'),'nama_dt1',null,1);
                    $this->cmbAddDT1->DataSource=$dt1;
//                    $this->cmbAddDT1->Enabled=false;
                    $this->cmbAddDT1->Enabled=true;
                    $this->cmbAddDT1->dataBind();

                    $this->cmbAddDT2->DataSource=array();
                    $this->cmbAddDT2->Enabled=false;
                    $this->cmbAddDT2->dataBind();

                    $this->cmbAddKecamatan->DataSource=array();
                    $this->cmbAddKecamatan->Enabled=false;
                    $this->cmbAddKecamatan->dataBind();
                }else {
                    $this->cmbAddNegara->Enabled=false;
                    $this->cmbAddNegara->DataSource=array();
                    $this->cmbAddNegara->dataBind();

                    $this->cmbAddDT1->DataSource=array();
                    $this->cmbAddDT1->Enabled=false;
                    $this->cmbAddDT1->dataBind();
                }
            break;
            case 'cmbAddNegara' :
                $idnegara=$this->cmbAddNegara->Text;					
                $dt1=$this->kegiatan->getList("dt1 WHERE idnegara='$idnegara'",array('iddt1','nama_dt1'),'nama_dt1',null,1);
                if ($idnegara!='' && $idnegara != 'none') {									
                    $this->disableAndEnabled(true);											
                    if (count($dt1)>1) {							
                        $this->cmbAddDT1->DataSource=$dt1;
                        $this->cmbAddDT1->Enabled=true;
                        $this->cmbAddDT1->dataBind();
                    }
                }else {
                    $this->disableAndEnabled(false);
                    $this->cmbAddDT1->DataSource=array();
                    $this->cmbAddDT1->Enabled=false;
                    $this->cmbAddDT1->dataBind();

                    $this->cmbAddDT2->DataSource=array();
                    $this->cmbAddDT2->Enabled=false;
                    $this->cmbAddDT2->dataBind();

                    $this->cmbAddKecamatan->DataSource=array();
                    $this->cmbAddKecamatan->Enabled=false;
                    $this->cmbAddKecamatan->dataBind();
                }					
            break;
            case 'cmbAddDT1' :
                $iddt1=$this->cmbAddDT1->Text;
                if ($iddt1!='' && $iddt1 != 'none') {	
                    $dt2=$this->kegiatan->getList("dt2 WHERE iddt1='$iddt1'",array('iddt2','nama_dt2'),'nama_dt2',null,1);                                        
                    if (count($dt2)>1) {
                        $this->disableAndEnabled(true);
                        $this->cmbAddDT2->DataSource=$dt2;
                        $this->cmbAddDT2->Enabled=true;
                        $this->cmbAddDT2->dataBind();
                    }else {
                        $this->cmbAddDT2->DataSource=array();
                        $this->cmbAddDT2->Enabled=false;
                        $this->cmbAddDT2->dataBind();

                        $this->cmbAddKecamatan->DataSource=array();
                        $this->cmbAddKecamatan->Enabled=false;
                        $this->cmbAddKecamatan->dataBind();
                    }
                }else {
                    $this->disableAndEnabled(false);
                    $this->cmbAddDT2->DataSource=array();
                    $this->cmbAddDT2->Enabled=false;
                    $this->cmbAddDT2->dataBind();
                }                
            break;
            case 'cmbAddDT2' :
                $iddt2=$this->cmbAddDT2->Text;
                $kec=$this->kegiatan->getList("kecamatan WHERE iddt2='$iddt2'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);
                if (count($kec)>1) {
                    $this->cmbAddKecamatan->DataSource=$kec;
                    $this->cmbAddKecamatan->Enabled=true;
                    $this->cmbAddKecamatan->dataBind();
                }else {
                    $this->cmbAddKecamatan->DataSource=array();
                    $this->cmbAddKecamatan->Enabled=false;
                    $this->cmbAddKecamatan->dataBind();
                }
            break;
        }			
	}
    private function setLocation2 ($lokasi,$idlok) {            
//        $this->cmbEditNegara->DataSource=$this->kegiatan->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,5);			
//        $this->cmbEditNegara->dataBind();       
        $this->cmbEditNegara->Enabled=true;	
        $this->cmbEditNegara->DataSource=array(2=>'INDONESIA');			
        $this->cmbEditNegara->dataBind();
		while (list($ketlok,$idlok)=each($lokasi)) {			
			switch ($ketlok) {
				case 'idnegara' :
					$this->cmbEditNegara->Text=$idlok;		
					if (isset($lokasi['iddt1'])) {
						$this->cmbEditDT1->DataSource=$this->kegiatan->getList("dt1 WHERE idnegara='$idlok'",array('iddt1','nama_dt1'),'nama_dt1',null,5);								
					}else {
						$this->cmbEditDT1->DataSource=$this->kegiatan->getList("dt1 WHERE idnegara='$idlok'",array('iddt1','nama_dt1'),'nama_dt1',null,1);
                        $this->cmbEditDT1->Text=$idlok;
						$this->cmbEditDT1->Enabled=true;								
					}
					$this->cmbEditDT1->dataBind();
				break;
				case 'iddt1' :
					$this->cmbEditDT1->Text=$idlok;
					if (isset($lokasi['iddt2'])) {
						$this->cmbEditDT2->DataSource=$this->kegiatan->getList("dt2 WHERE iddt1='$idlok'",array('iddt2','nama_dt2'),'nama_dt2',null,5);								
					}else {
						$this->cmbEditDT2->DataSource=$this->kegiatan->getList("dt2 WHERE iddt1='$idlok'",array('iddt2','nama_dt2'),'nama_dt2',null,1);
                        $this->cmbEditDT2->Text=$idlok;
						$this->cmbEditDT2->Enabled=true;								
					}
					$this->cmbEditDT2->dataBind();
				break;
				case 'iddt2' :					
					$this->cmbEditDT2->Text=$idlok;
					if (isset($lokasi['iddt2'])) {
						$this->cmbEditKecamatan->DataSource=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);
                        $this->cmbEditKecamatan->Text=$idlok;
						$this->cmbEditKecamatan->Enabled=true;
					}else {
						$this->cmbEditKecamatan->DataSource=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,5);												
                        $this->cmbEditKecamatan->Text=$idlok;
                        $this->cmbEditKecamatan->Enabled=true;
					}	
					$this->cmbEditKecamatan->dataBind();
				break;
				case 'idkecamatan' :
					$this->cmbEditKecamatan->Text=$idlok;					
				break;
			}
		}
	}
	public function changeDataMaster2 ($sender,$param) {
		$this->idProcess='edit';	       
        switch ($sender->getId()) {				                        
            case 'cmbEditProgram' :
                $this->lblEditKodeProgram->Text=$this->kegiatan->getKodeProgramByID($this->cmbEditProgram->Text).'.';	
                $idprogram=$this->cmbEditProgram->Text;			
                $this->txtEditIdProgram->Value=$idprogram;
            break;            
            case 'cmbEditDT1' :
                $iddt1=$this->cmbEditDT1->Text;
                $dt2=$this->kegiatan->getList("dt2 WHERE iddt1='$iddt1'",array('iddt2','nama_dt2'),'nama_dt2',null,1);                                        
                if (count($dt2)>1) {
                    $this->cmbEditDT2->DataSource=$dt2;
                    $this->cmbEditDT2->Enabled=true;
                    $this->cmbEditDT2->dataBind();                    
                    
                    $this->cmbEditKecamatan->DataSource=array();
                    $this->cmbEditKecamatan->Enabled=false;
                    $this->cmbEditKecamatan->dataBind();
                    
                }else {
                    $this->cmbEditDT2->DataSource=array();
                    $this->cmbEditDT2->Enabled=false;
                    $this->cmbEditDT2->dataBind();

                    $this->cmbEditKecamatan->DataSource=array();
                    $this->cmbEditKecamatan->Enabled=false;
                    $this->cmbEditKecamatan->dataBind();
                    
                }
            break;
            case 'cmbEditDT2' :
                $iddt2=$this->cmbEditDT2->Text;
                $kec=$this->kegiatan->getList("kecamatan WHERE iddt2='$iddt2'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);
                if (count($kec)>1) {
                    $this->cmbEditKecamatan->DataSource=$kec;
                    $this->cmbEditKecamatan->Enabled=true;
                    $this->cmbEditKecamatan->dataBind();
                }else {
                    $this->cmbEditKecamatan->DataSource=array();
                    $this->cmbEditKecamatan->Enabled=false;
                    $this->cmbEditKecamatan->dataBind();
                }
            break;
        }
    }	
	public function saveData ($sender,$param) {
        $this->idProcess='add';
		if ($this->Page->IsValid) {				
            $idprogram=$this->cmbAddProgram->Text;
			$kode_program=$this->lblAddKodeProgram->Text;
			$kode_kegiatan=$kode_program.trim($this->txtAddKodeKegiatan->Text);			
			$nama_kegiatan=  addslashes(strtoupper($this->txtAddNamaKegiatan->Text));
			$nilai_pagu=$this->finance->toInteger($this->txtAddNilaiPagu->Text);
			$keluaran=addslashes($this->txtAddKeluaran->Text);
            $tkk=addslashes($this->txtAddTKK->Text);
			$hasil=addslashes($this->txtAddHasil->Text);
            $tkh=addslashes($this->txtAddTKH->Text);
			$capaian_program=addslashes($this->txtAddCapaianProgram->Text);
            $ksk=addslashes($this->txtAddKSK->Text);
            $sifatkegiatan=$this->cmbAddSK->Text;            
            $waktupelaksanaan=strtoupper($this->txtAddWaktuPelaksanaan->Text);
            $ta=$this->session['ta'];
            
            $negara=$this->cmbAddNegara->Text;
			$dt1=$this->cmbAddDT1->Text;
			$dt2=$this->cmbAddDT2->Text;
			$kec=$this->cmbAddKecamatan->Text;
			if (($negara != 'none'||$negara && '')&&($dt1 && 'none'||$dt1 && '')&&($dt2 != 'none'&&$dt2 != '')&&($kec != 'none'&&$kec != '')){
				$idlok=$kec;
				$ket_lok='kec';
			}elseif (($negara != 'none'&&$negara != '')&&($dt1 && 'none'||$dt1 != '')&&($dt2 != 'none'&&$dt2 != '')){
				$idlok=$dt2;
				$ket_lok='dt2';
			}elseif (($negara != 'none'&&$negara != '')&&($dt1 != 'none'&&$dt1 != '')){
				$idlok=$dt1;
				$ket_lok='dt1';
			}else {
				$idlok=$negara;
				$ket_lok='negara';
			}
            $pa=$this->cmbAddPA->Text;
            $kp=$this->cmbAddKP->Text;
            $ppk=$this->cmbAddPPK->Text;
            $pptk=$this->cmbAddPPTK->Text;
            $str = 'INSERT INTO proyek (idproyek,idprogram,kode_proyek,nama_proyek,nilai_pagu,keluaran,tk_keluaran,hasil,tk_hasil,capaian_program,ksk,sifat_kegiatan,waktu_pelaksanaan,tahun_anggaran,idlok,ket_lok,nip_pengguna_anggaran,nip_kuasa_pengguna,nip_ppk,nip_pptk,userid)';
			$str .= "VALUES (NULL,$idprogram,'$kode_kegiatan','$nama_kegiatan','$nilai_pagu','$keluaran','$tkk','$hasil','$tkh','$capaian_program','$ksk','$sifatkegiatan','$waktupelaksanaan',$ta,'$idlok','$ket_lok','$pa','$kp','$ppk','$pptk',{$this->userid})";
			$this->DB->insertRecord($str);
            $this->kegiatan->redirect('d.belanja.Kegiatan');
		}
	}
	public function editRecord($sender,$param) {
        $idproyek=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->idProcess='edit';
        
		$str = "SELECT pr.idunit,pr.kode_program,p.idproyek,p.idprogram,p.kode_proyek,p.nama_proyek,p.nilai_pagu,p.keluaran,p.tk_keluaran,p.capaian_program,p.hasil,p.tk_hasil,p.ksk,p.sifat_kegiatan,p.waktu_pelaksanaan,p.tahun_anggaran,sumber_anggaran,p.idlok,p.ket_lok,p.nip_pengguna_anggaran,p.nip_kuasa_pengguna,p.nip_ppk,p.nip_pptk,userid FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND idproyek='$idproyek'";
		$this->DB->setFieldTable(array('idunit','kode_program','idproyek','idprogram','kode_proyek','nama_proyek','nilai_pagu','keluaran','tk_keluaran','capaian_program','hasil','tk_hasil','ksk','sifat_kegiatan','waktu_pelaksanaan','tahun_anggaran','sumber_anggaran','idlok','ket_lok','nip_pengguna_anggaran','nip_kuasa_pengguna','nip_ppk','nip_pptk','userid'));
		$r=$this->DB->getRecord($str);	
		$data=$r[1];
        $this->kegiatan->dataKegiatan=$data;
		$lokasi=$this->kegiatan->getAllLokasiOnlyId ();		        
        $this->setLocation2 ($lokasi,$data['idlok']);
        
        $idunit=$data['idunit'];
        $tahun=$data['tahun_anggaran'];
        $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,6);		
        $this->cmbEditProgram->DataSource=$result;
        $this->cmbEditProgram->Text=$data['idprogram'];
        $this->cmbEditProgram->dataBind();
        $this->cmbEditProgram->Enabled=true;	                   
        
        
		$this->txtIdKegiatan->Value=$idproyek;					
        $this->txtEditKodeKegiatan2->Value=$data['kode_proyek'];
		$this->txtEditIdProgram->Value=$data['idprogram'];
        
		$len_proyek=strlen($data['kode_proyek']);
		$len_program=strlen($data['kode_program'])+1;
		$data['kode_proyek']=substr($data['kode_proyek'],$len_program,$len_proyek-$len_program);				
        
        
		$this->lblEditKodeProgram->Text=$data['kode_program'].'.'; 
        $this->txtEditKodeKegiatan->Text=$data['kode_proyek'];			           
        
        $this->txtEditNamaKegiatan->Text=$data['nama_proyek'];
        $this->txtEditNilaiPagu->Text=$this->finance->toRupiah($data['nilai_pagu']);;
        $this->txtEditKeluaran->Text=$data['keluaran'];
        $this->txtEditTKK->Text=$data['tk_keluaran'];
        $this->txtEditHasil->Text=$data['hasil'];
        $this->txtEditTKH->Text=$data['tk_hasil'];
        $this->txtEditCapaianProgram->Text=$data['capaian_program'];
        $this->txtEditKSK->Text=$data['ksk'];
                
        $this->cmbEditSK->DataSource=$this->kegiatan->getSifatKegiatan();
        $this->cmbEditSK->Text=$data['sifat_kegiatan'];
		$this->cmbEditSK->dataBind();           
        
        $this->txtEditWaktuPelaksanaan->Text=$data['waktu_pelaksanaan'];
        $this->cmbEditSumberAnggaran->Text=$data['sumber_anggaran'];
        $this->cmbEditPA->DataSource=$this->kegiatan->getList("pengguna_anggaran WHERE idunit=$idunit",array ('nip_pengguna_anggaran','nama_pengguna_anggaran'),'nama_pengguna_anggaran',null,1);
        $this->cmbEditPA->Text=$data['nip_pengguna_anggaran'];
		$this->cmbEditPA->dataBind();		
		
		$this->cmbEditKP->DataSource=$this->kegiatan->getList("kuasa_pengguna WHERE idunit=$idunit",array ('nip_kuasa_pengguna','nama_kuasa_pengguna'),'nama_kuasa_pengguna',null,1);
        $this->cmbEditKP->Text=$data['nip_kuasa_pengguna'];
		$this->cmbEditKP->dataBind();			
			
        $ppk=$this->kegiatan->getList("ppk WHERE idunit=$idunit",array ('nip_ppk','nama_ppk'),'nama_ppk',null,1);
		$this->cmbEditPPK->DataSource=$ppk;
        $this->cmbEditPPK->Text=$data['nip_ppk'];
		$this->cmbEditPPK->dataBind();
		
		$this->cmbEditPPTK->DataSource=$this->kegiatan->getList("pptk WHERE idunit=$idunit",array ('nip_pptk','nama_pptk'),'nama_pptk',null,1);
        $this->cmbEditPPTK->Text=$data['nip_pptk'];
		$this->cmbEditPPTK->dataBind();        
        $daftar_user=$data['userid']==0?$this->kegiatan->getList("user WHERE page='s' OR page='m'",array('userid','username'),'username',null,1):$this->kegiatan->getList("user WHERE page='s' OR page='m'",array('userid','username'),'username',null,5);        
        
        $this->cmbEditUsers->DataSource=$daftar_user;
        $this->cmbEditUsers->Text=$data['userid'];
		$this->cmbEditUsers->dataBind();
        
	}
	public function updateData ($sender,$param) {
		if ($this->Page->IsValid) {
            $idprogram=$this->txtEditIdProgram->Value;
            $idproyek=$this->txtIdKegiatan->Value;
			$kode_kegiatan=$this->lblEditKodeProgram->Text.trim($this->txtEditKodeKegiatan->Text);			           
            $nama_kegiatan=addslashes(strtoupper($this->txtEditNamaKegiatan->Text));
			$nilai_pagu=$this->finance->toInteger($this->txtEditNilaiPagu->Text);
			$keluaran=addslashes($this->txtEditKeluaran->Text);
            $tkk=addslashes($this->txtEditTKK->Text);
			$hasil=addslashes($this->txtEditHasil->Text);
            $tkh=addslashes($this->txtEditTKH->Text);
			$capaian_program=addslashes($this->txtEditCapaianProgram->Text);
            $ksk=addslashes($this->txtEditKSK->Text);
            $sifatkegiatan=$this->cmbEditSK->Text;            
            $waktupelaksanaan=strtoupper($this->txtEditWaktuPelaksanaan->Text); 
            $sumber_anggaran=$this->cmbEditSumberAnggaran->Text;
            $negara=$this->cmbEditNegara->Text;
			$dt1=$this->cmbEditDT1->Text;
			$dt2=$this->cmbEditDT2->Text;
			$kec=$this->cmbEditKecamatan->Text;
			if (($negara != 'none'||$negara && '')&&($dt1 && 'none'||$dt1 && '')&&($dt2 != 'none'&&$dt2 != '')&&($kec != 'none'&&$kec != '')){
				$idlok=$kec;
				$ket_lok='kec';
			}elseif (($negara != 'none'&&$negara != '')&&($dt1 && 'none'||$dt1 != '')&&($dt2 != 'none'&&$dt2 != '')){
				$idlok=$dt2;
				$ket_lok='dt2';
			}elseif (($negara != 'none'&&$negara != '')&&($dt1 != 'none'&&$dt1 != '')){
				$idlok=$dt1;
				$ket_lok='dt1';
			}else {
				$idlok=$negara;
				$ket_lok='negara';
			}
            $pa=$this->cmbEditPA->Text;
            $kp=$this->cmbEditKP->Text;
            $ppk=$this->cmbEditPPK->Text;
            $pptk=$this->cmbEditPPTK->Text;
            $userid=$this->cmbEditUsers->Text;
			$str = "UPDATE proyek SET idprogram=$idprogram,kode_proyek='$kode_kegiatan',nama_proyek='$nama_kegiatan',nilai_pagu='$nilai_pagu',keluaran='$keluaran',tk_keluaran='$tkk',capaian_program='$capaian_program',hasil='$hasil',tk_hasil='$tkh',ksk='$ksk',sifat_kegiatan='$sifatkegiatan',waktu_pelaksanaan='$waktupelaksanaan',sumber_anggaran='$sumber_anggaran',idlok='$idlok',ket_lok='$ket_lok',nip_pengguna_anggaran='$pa',nip_kuasa_pengguna='$kp',nip_ppk='$ppk',nip_pptk='$pptk',userid=$userid WHERE idproyek=$idproyek";
			$this->DB->updateRecord($str);
            $this->kegiatan->redirect('d.belanja.Kegiatan');			
		}
	}    	
	public function deleteRecord ($sender,$param) {
        $idproyek=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->DB->deleteRecord("proyek WHERE idproyek='$idproyek'");
        $this->kegiatan->redirect('d.belanja.Kegiatan');					
    }	    
	private function disableAndEnabled ($flag=false) {
		if ($flag) {						
			$this->txtAddKodeKegiatan->Enabled=true;
			$this->txtAddNamaKegiatan->Enabled=true;
			$this->txtAddNilaiPagu->Enabled=true;
			$this->txtAddKeluaran->Enabled=true;
			$this->txtAddTKK->Enabled=true;
			$this->txtAddHasil->Enabled=true;
			$this->txtAddTKH->Enabled=true;
			$this->txtAddCapaianProgram->Enabled=true;
			$this->txtAddKSK->Enabled=true;
			$this->cmbAddSK->Enabled=true;			
            $this->cmbAddSumberAnggaran->Enabled=true;
			$this->txtAddWaktuPelaksanaan->Enabled=true;						            
			$this->cmbAddPA->Enabled=true;
			$this->cmbAddKP->Enabled=true;
			$this->cmbAddPPK->Enabled=true;
			$this->cmbAddPPTK->Enabled=true;
			$this->btnSaveData->Enabled=true;			
		}else {					
			$this->txtAddKodeKegiatan->Enabled=false;
			$this->txtAddNamaKegiatan->Enabled=false;
			$this->txtAddNilaiPagu->Enabled=false;
			$this->txtAddKeluaran->Enabled=false;
			$this->txtAddTKK->Enabled=false;
			$this->txtAddHasil->Enabled=false;
			$this->txtAddTKH->Enabled=false;
			$this->txtAddCapaianProgram->Enabled=false;
			$this->txtAddKSK->Enabled=false;
			$this->cmbAddSK->Enabled=false;			
            $this->cmbAddSumberAnggaran->Enabled=false;
			$this->txtAddWaktuPelaksanaan->Enabled=false;						
			$this->cmbAddPA->Enabled=false;
			$this->cmbAddKP->Enabled=false;
			$this->cmbAddPPK->Enabled=false;
			$this->cmbAddPPTK->Enabled=false;
			$this->btnSaveData->Enabled=false;			
		}
	}	
}

?>