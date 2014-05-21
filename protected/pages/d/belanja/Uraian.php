<?php
prado::using ('Application.pages.d.belanja.MainPageBelanja');
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
                if (!isset($_SESSION['currentPageUraian'])||$_SESSION['currentPageUraian']['page_name']!='d.belanja.Uraian') {
                    $_SESSION['currentPageUraian']=array('page_name'=>'d.belanja.Uraian','page_num'=>0,'dataKegiatan'=>array(),'RencanaTargetFisikPage'=>false);												
                }  
                $idproyek=addslashes($this->request['id']);
                $this->kegiatan->setIdProyek($idproyek,true);                        
                if (isset($this->kegiatan->dataKegiatan['idproyek'])) {                
                    $_SESSION['currentPageUraian']['dataKegiatan']=$this->kegiatan->dataKegiatan;                
                    $this->uraianAnchor->NavigateUrl=$this->Service->constructUrl('d.belanja.Uraian',array('id'=>$idproyek));
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
    public function showModalCopy ($sender,$param) {        
        $this->modalCopyUraian->Show();
    }
    public function showRencanaTargetFisikPage($sender,$param) {        
        $idproyek=$_SESSION['currentPageUraian']['dataKegiatan']['idproyek'];
        $_SESSION['currentPageUraian']['RencanaTargetFisikPage']=true;
        $this->kegiatan->redirect('d.belanja.Uraian',array('id'=>$idproyek));
    }
    public function checkKodeKegiatan($sender,$param) {        
        $kode_kegiatan=  addslashes($param->Value);
        if ($kode_kegiatan != '') {
            try {
                if (!$this->DB->checkRecordIsExist('kode_proyek','proyek',$kode_kegiatan)) {
                    throw new Exception ("Kode kegiatan ($kode_kegiatan) tidak tersedia");		
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }		
    }
    public function copyUraian ($sender,$param) {        
        if ($this->IsValid) {
            $kode_kegiatan=$this->txtCopyKodeKegiatan->Text;
            $str = "SELECT idproyek FROM proyek WHERE kode_proyek='$kode_kegiatan'";
            $this->DB->setFieldTable(array('idproyek'));
            $r=$this->DB->getRecord($str);
            if (isset($r[1])) {
                $idproyek=$this->session['currentPageUraian']['dataKegiatan']['idproyek'];
                $idproyek2=$r[1]['idproyek'];
                $this->DB->query('BEGIN');
                if ($this->DB->deleteRecord("uraian WHERE idproyek=$idproyek")) {
                    $str = "INSERT INTO uraian (idproyek,rekening,nama_rekening,nama_uraian,idlok,ket_lok,volume,satuan,jp,idjenis_pembangunan,nama_perusahaan,alamat_perusahaan,no_telepon,nama_direktur,npwp,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,status_lelang) SELECT '$idproyek',rekening,nama_rekening,nama_uraian,idlok,ket_lok,volume,satuan,jp,idjenis_pembangunan,nama_perusahaan,alamat_perusahaan,no_telepon,nama_direktur,npwp,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,status_lelang FROM uraian WHERE idproyek=$idproyek2";
                    $this->DB->insertRecord($str);
                    $this->DB->query('COMMIT');
                }else {
                    $this->DB->query('ROLLBACK');
                }
            }
            $this->modalCopyUraian->Hide();
            $this->populateData();
        }
    }
    public function addProcess ($sender,$param) {
        $this->idProcess='add';        
        $rekening=$this->kegiatan->getList('rek1 WHERE no_rek1=5',array('no_rek1','nama_rek1'),'no_rek1',null,7);        
        $this->cmbAddTransaksi->DataSource=$rekening;
        $this->cmbAddTransaksi->dataBind();
        
        $this->cmbAddJP->DataSource=$this->kegiatan->getJenisPelaksanaan();
        $this->cmbAddJP->dataBind();	
        
        $this->cmbAddJenisPembangunan->DataSource=$this->kegiatan->getJenisPembangunan(null,1);
        $this->cmbAddJenisPembangunan->dataBind();	
    }		
	public function changeRekening ($sender,$param) {
		$this->idProcess='add';		
		$this->kegiatan->dataKegiatan=$this->session['currentPageUraian']['dataKegiatan'];
		$lokasi=$this->kegiatan->getAllLokasiOnlyId ();		
		switch ($sender->getId()) {
			case 'cmbAddTransaksi' :
				$idtransaksi=$this->cmbAddTransaksi->Text;
				$this->disableComponentRekening1 ();
				$this->disableAndEnabled();
				if ($idtransaksi != 'none' || $idtransaksi != '') {
					$result=$this->rekening->getListKelompok($idtransaksi);
					if (count($result)> 1) {
						$this->cmbAddKelompok->DataSource=$result;
						$this->cmbAddKelompok->Enabled=true;
						$this->cmbAddKelompok->dataBind();
					}
				}
			break;			
			case 'cmbAddKelompok' :
				$idkelompok = $this->cmbAddKelompok->Text;
				$this->disableComponentRekening2 ();
				$this->disableAndEnabled();
				if ($idkelompok != 'none' || $idkelompok !='') {
					$result=$this->rekening->getListJenis($idkelompok);
					if (count($result)> 1) {
						$this->cmbAddJenis->DataSource=$result;
						$this->cmbAddJenis->Enabled=true;
						$this->cmbAddJenis->dataBind();
					}
				}
			break;
			case 'cmbAddJenis' :
				$idjenis = $this->cmbAddJenis->Text;
				$this->disableComponentRekening3 ();
				$this->disableAndEnabled();
				if ($idjenis != 'none' || $idjenis != '') {
					$result=$this->rekening->getListObjek($idjenis);
					if (count($result)> 1) {
						$this->cmbAddObjek->DataSource=$result;
						$this->cmbAddObjek->Enabled=true;
						$this->cmbAddObjek->dataBind();
					}
				}
			break;			
			case 'cmbAddObjek' :
				$idobjek = $this->cmbAddObjek->Text;
				$this->disableComponentRekening4 ();
				$this->disableAndEnabled();
				if ($idobjek != 'none' && $idobjek != '') {
					$result=$this->rekening->getListRincian($idobjek);
					if (count($result)> 1) {                        
						$this->cmbAddRincian->DataSource=$result;
						$this->cmbAddRincian->Enabled=true;
						$this->cmbAddRincian->dataBind();
					}
				}
			break;
			case 'cmbAddRincian' :			
				$idrincian=$this->cmbAddRincian->Text;
				if ($idrincian != 'none') {
					$this->session['idrincian']=$idrincian;
					$this->cmbAddNegara->DataSource=$this->kegiatan->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,5);			
					$this->cmbAddNegara->dataBind();
                    $no_rek5=$this->cmbAddRincian->Text;                    
                    $str = "SELECT nama_rek5 FROM rek5 WHERE no_rek5='$no_rek5'";
                    $this->DB->setFieldTable(array('nama_rek5'));
                    $r=$this->DB->getRecord($str);
                    $this->txtAddNamaUraian->Text=$r[1]['nama_rek5'];
                    $this->txtAddNamaRekening->Value=$r[1]['nama_rek5'];
					$this->setLocation ($lokasi);
					$this->disableAndEnabled(true);
				}else {
					$this->disableAndEnabled(false);
				}
			break;
			case 'cmbAddDT1' :
				$this->setLocation ($lokasi);
			break;
			case 'cmbAddDT2' :						
				$this->setLocation ($lokasi);				
			break;
			case 'cmbAddKecamatan' :
				$this->setLocation ($lokasi);				
			break;
		}
	}
	
	private function setLocation ($lokasi) {
		while (list($ketlok,$idlok)=each($lokasi)) {			
			switch ($ketlok) {
				case 'idnegara' :
					$this->cmbAddNegara->Text=$idlok;		
					if (isset($lokasi['iddt1'])) {
						$this->cmbAddDT1->DataSource=$this->kegiatan->getList("dt1 WHERE idnegara='$idlok'",array('iddt1','nama_dt1'),'nama_dt1',null,5);								
					}else {
						$this->cmbAddDT1->DataSource=$this->kegiatan->getList("dt1 WHERE idnegara='$idlok'",array('iddt1','nama_dt1'),'nama_dt1',null,1);
						$this->cmbAddDT1->Enabled=true;								
					}
					$this->cmbAddDT1->dataBind();
				break;
				case 'iddt1' :
					$this->cmbAddDT1->Text=$idlok;
					if (isset($lokasi['iddt2'])) {
						$this->cmbAddDT2->DataSource=$this->kegiatan->getList("dt2 WHERE iddt1='$idlok'",array('iddt2','nama_dt2'),'nama_dt2',null,5);								
					}else {
						$this->cmbAddDT2->DataSource=$this->kegiatan->getList("dt2 WHERE iddt1='$idlok'",array('iddt2','nama_dt2'),'nama_dt2',null,1);
						$this->cmbAddDT2->Enabled=true;								
					}
					$this->cmbAddDT2->dataBind();
				break;
				case 'iddt2' :					
					$this->cmbAddDT2->Text=$idlok;
					if (isset($lokasi['iddt2'])) {
						$this->cmbAddKecamatan->DataSource=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);
						$this->cmbAddKecamatan->Enabled=true;
					}else {
						$this->cmbAddKecamatan->DataSource=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,5);												
                        $this->cmbAddKecamatan->Enabled=true;
					}	
					$this->cmbAddKecamatan->dataBind();
				break;
				case 'idkecamatan' :
					$this->cmbAddKecamatan->Text=$idlok;
					$this->cmbAddKecamatan->Enabled=false;
                    $this->txtAddKelurahan->Enabled=true;
				break;
			}
		}
	}
	private function setLocation2 ($lokasi,$idlok) {            
//        $this->cmbEditNegara->DataSource=$this->kegiatan->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,5);			
//        $this->cmbEditNegara->dataBind();        
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
					$this->cmbEditKecamatan->Enabled=false;                    
                    $this->txtEditKelurahan->Enabled=true;
				break;
			}
		}
	}
	public function changeLocation ($sender,$param) {
		$this->idProcess='add';        
		switch ($sender->getId()) {
			case 'cmbAddNegara' :
				
			break;
			case 'cmbAddDT1' :
				
			break;
			case 'cmbAddDT2' :						
				$idlok=$this->cmbAddDT2->Text;
				if ($idlok == 'none') {
					$listKec=array();
					$bool=false;
                    $this->txtAddKelurahan->Enabled=false;
                    $this->txtAddKelurahan->Text='';
				}else {
					$listKec=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);					
                    $bool=count($listKec)>1?true:false;
				}
				$this->cmbAddKecamatan->DataSource=$listKec;
				$this->cmbAddKecamatan->Enabled=$bool;
				$this->cmbAddKecamatan->dataBind();
			break;
			case 'cmbAddKecamatan' :
                $idkec=$this->cmbAddKecamatan->Text;
                if ($idkec != ''&&$idkec != 'none') {                    
                    $this->txtAddKelurahan->Enabled=true;								
                }else{
                    $this->txtAddKelurahan->Enabled=false;
                    $this->txtAddKelurahan->Text='';								
                }  								
			break;
		}
	}	
    public function changeLocation2 ($sender,$param) {
		$this->idProcess='edit';
		switch ($sender->getId()) {
			case 'cmbEditNegara' :
				
			break;
			case 'cmbEditDT1' :
				
			break;
			case 'cmbEditDT2' :						
				$idlok=$this->cmbEditDT2->Text;
				if ($idlok == 'none' || $idlok == '') {
					$listKec=array();
					$bool=false;                    
                    $this->txtEditKelurahan->Enabled=false;
                    $this->txtEditKelurahan->Text='';
				}else {
					$listKec=$this->kegiatan->getList("kecamatan WHERE iddt2='$idlok'",array('idkecamatan','nama_kecamatan'),'nama_kecamatan',null,1);                                        
					$bool=count($listKec)>1?true:false;
				}
				$this->cmbEditKecamatan->DataSource=$listKec;
				$this->cmbEditKecamatan->Enabled=$bool;
				$this->cmbEditKecamatan->dataBind();          
			break;
			case 'cmbEditKecamatan' :
                                
			break;
		}
	}
	public function checkPaguKegiatan ($sender,$param) {
        $this->idProcess='add';
        $nilai_pagu=$this->finance->toInteger($param->Value);
        if ($nilai_pagu != '') {
            try {                
                $this->kegiatan->dataKegiatan=$this->session['currentPageUraian']['dataKegiatan'];                    
                $sisa_pagu_kegiatan=$this->kegiatan->getSisaNilaiPagu();                    
                if ($nilai_pagu > $sisa_pagu_kegiatan) {
                    throw new Exception ("<p class=\"msg error\">Jumlah Pagu pada Uraian jangan lebih dari nilai pagu kegiatan.</p>");		
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }		
	}	
    public function checkPaguKegiatanEdit ($sender,$param) {
        $this->idProcess='edit';
        $nilai_pagu=$this->finance->toInteger($param->Value);
        if ($nilai_pagu != '') {
            try {
                if ($this->hiddennilaipaguuraian->Value != $nilai_pagu){
                    $dataKegiatan=$this->session['currentPageUraian']['dataKegiatan'];
                    $pagu_kegiatan=$dataKegiatan['nilai_pagu'];
                    $idproyek=$dataKegiatan['idproyek'];
                    $str = "SELECT SUM(nilai) AS total_uraian FROM uraian WHERE idproyek='$idproyek'";
                    $this->DB->setFieldTable(array('total_uraian'));	
                    $r=$this->DB->getRecord($str);                    
                    $total_uraian=isset($r[1])?$r[1]['total_uraian']:0;
                    $total_uraian-=$this->hiddennilaipaguuraian->Value;
                    $total_uraian+=$nilai_pagu;                                        
                    if ($total_uraian > $pagu_kegiatan) {
                        throw new Exception ("<p class=\"msg error\">Jumlah Pagu pada Uraian jangan lebih dari nilai pagu kegiatan.</p>");		
                    }
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }	
    }
	
	public function checkPenawaran ($sender,$param) {		        
		$this->idProcess=$sender->getId()==='checkAddPenawaran'?'add':'edit';
        $nilai=$sender->getId()==='checkAddPenawaran'?$this->txtAddNilaiPagu->Text:$this->txtEditNilaiPagu->Text;
        $nilaipagukegiatan=$this->finance->toInteger($nilai);
        $nilai_penawaran=$this->finance->toInteger($param->Value);
        if ($nilai_penawaran != '') {
            try {
                if ($nilaipagukegiatan != $nilai_penawaran){
                    $this->kegiatan->dataKegiatan=$this->session['currentPageUraian']['dataKegiatan'];                    
                    if ($nilai_penawaran > $nilaipagukegiatan) {
                        throw new Exception ("<p class=\"msg error\">Nilai Penawaran jangan melebihi nilai Pagu Uraian.</p>");		
                    }
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }		
	}	
	public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {
            $idproyek=$this->session['currentPageUraian']['dataKegiatan']['idproyek'];
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
            $nama_rekening=$this->txtAddNamaRekening->Value;
			$nama_uraian=addslashes(strtoupper($this->txtAddNamaUraian->Text));
			$txtKelurahan=addslashes(strtoupper($this->txtAddKelurahan->Text));
			$txtVolume=addslashes(strtoupper($this->txtAddVolume->Text));
			$txtSatuan=addslashes(strtoupper($this->txtAddSatuan->Text));
            $txtHargaSatuan=$this->finance->toInteger($this->txtAddHargaSatuan->Text);
			$txtNilai=$this->finance->toInteger($this->txtAddNilaiPagu->Text);
            $jp=$this->cmbAddJP->Text;
            $jenispembangunan=$this->cmbAddJenisPembangunan->Text;
			$txtNamaPerusahaan=addslashes(strtoupper($this->txtAddNamaPerusahaan->Text));
			$txtAlamatPerusahaan=addslashes(strtoupper($this->txtAddAlamat->Text));
			$txtNoTelepon=$this->txtAddNoTelp->Text;
			$txtNamaDirektur=addslashes(strtoupper($this->txtAddNamaDirektur->Text));
			$txtNpwp=$this->txtAddNPWP->Text;
            $txtHPS=$this->finance->toInteger($this->txtAddHPS->Text);
			$txtPenawaran=$this->finance->toInteger($this->txtAddPenawaran->Text);
            $tanggalkontrak=$this->TGL->tukarTanggal($this->cmbAddTK->Text);
            $mulai_pelaksanaan=$this->TGL->tukarTanggal($this->cmbAddMulaiP->Text);            
            $selesai_pelaksanaan=$this->TGL->tukarTanggal($this->cmbAddSelesaiP->Text); 
            $status_lelang=$this->cmbAddStatusLelang->Text;
			$str = 'INSERT INTO uraian (iduraian,idproyek,rekening,nama_rekening,nama_uraian,idlok,ket_lok,kelurahan,volume,satuan,harga_satuan,nilai,jp,idjenis_pembangunan,nama_perusahaan,alamat_perusahaan,no_telepon,nama_direktur,npwp,hps,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,status_lelang) VALUES ';
			$str .= "(NULL,$idproyek,'".$this->session['idrincian']."','$nama_rekening','$nama_uraian',$idlok,'$ket_lok','$txtKelurahan','$txtVolume','$txtSatuan','$txtHargaSatuan','$txtNilai','$jp','$jenispembangunan','$txtNamaPerusahaan','$txtAlamatPerusahaan','$txtNoTelepon','$txtNamaDirektur','$txtNpwp','$txtHPS','$txtPenawaran','$tanggalkontrak','$mulai_pelaksanaan','$selesai_pelaksanaan',$status_lelang)";
			$this->DB->insertRecord($str);
            $this->kegiatan->redirect('d.belanja.Uraian',array('id'=>$idproyek));
		}else {
			$this->idProcess='add';
		}
	}	
	public function editRecord($sender,$param) {
        $this->idProcess='edit';
        $iduraian=$this->getDataKeyField($sender,$this->RepeaterS);
		$str = "SELECT * FROM uraian WHERE iduraian='$iduraian'";
		$this->DB->setFieldTable(array('iduraian','idproyek','rekening','nama_rekening','nama_uraian','idlok','ket_lok','kelurahan','volume','satuan','harga_satuan','nilai','jp','idjenis_pembangunan','nama_perusahaan','alamat_perusahaan','no_telepon','nama_direktur','npwp','hps','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','status_lelang'));
		$r=$this->DB->getRecord($str); 		        
 		$this->txtIdUraian->Value=$iduraian;
        $data=$r[1];				        
        $this->kegiatan->dataKegiatan=$data;
		$lokasi=$this->kegiatan->getAllLokasiOnlyId ();		                                
        $this->setLocation2 ($lokasi,$data['idlok']);                       
        $this->txtEditKodeRekening->Text=$data['rekening'].' / '.$data['nama_rekening'];
        $this->txtEditNamaUraian->Text=$data['nama_uraian'];
        $this->txtEditKelurahan->Text=$data['ket_lok']=='kec'?true:false;
        $this->txtEditKelurahan->Text=$data['kelurahan'];
        $this->txtEditVolume->Text=$data['volume'];
        $this->txtEditSatuan->Text=$data['satuan'];
        $this->hiddennilaipaguuraian->Value=$data['nilai'];
        $this->txtEditHargaSatuan->Text=$this->finance->toRupiah($data['harga_satuan']);
        $this->txtEditNilaiPagu->Text=$this->finance->toRupiah($data['nilai']);
        $this->txtEditNamaPerusahaan->Text=$data['nama_perusahaan'];
        $this->txtEditAlamat->Text=$data['alamat_perusahaan'];
        $this->txtEditNoTelp->Text=$data['no_telepon'];;
        $this->txtEditNamaDirektur->Text=$data['nama_direktur'];
        $this->txtEditNPWP->Text=$data['npwp'];
        $this->txtEditHPS->Text=$this->finance->toRupiah($data['hps']);
        $this->txtEditPenawaran->Text=$this->finance->toRupiah($data['penawaran']);
        $this->cmbEditTK->Text=$this->TGL->tukarTanggal($data['tgl_kontrak'],'entoid');
        $this->cmbEditMulaiP->Text=$this->TGL->tukarTanggal($data['tgl_mulai_pelaksanaan'],'entoid');                
        $this->cmbEditSelesaiP->Text=$this->TGL->tukarTanggal($data['tgl_selesai_pelaksanaan'],'entoid');                
		$this->cmbEditJP->DataSource=$this->kegiatan->getJenisPelaksanaan();		
        $this->cmbEditJP->Text=$data['jp'];
		$this->cmbEditJP->dataBind();		
        $this->cmbEditJenisPembangunan->DataSource=$this->kegiatan->getJenisPembangunan(null,1);		
        $this->cmbEditJenisPembangunan->Text=$data['idjenis_pembangunan'];
		$this->cmbEditJenisPembangunan->dataBind();		
        $this->cmbEditStatusLelang->Text=$data['status_lelang'];
	}
	public function updateData ($sender,$param) {
		if ($this->Page->IsValid) {
            $idproyek=$this->session['currentPageUraian']['dataKegiatan']['idproyek'];
            $iduraian=$this->txtIdUraian->Value;
			$nama_uraian=addslashes(strtoupper($this->txtEditNamaUraian->Text));
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
			$txtKelurahan=addslashes(strtoupper($this->txtEditKelurahan->Text));
			$txtVolume=addslashes(strtoupper($this->txtEditVolume->Text));
			$txtSatuan=addslashes(strtoupper($this->txtEditSatuan->Text));
            $txtHargaSatuan=$this->finance->toInteger($this->txtEditHargaSatuan->Text);
			$txtNilai=$this->finance->toInteger($this->txtEditNilaiPagu->Text);
            $jp=$this->cmbEditJP->Text;
            $jenispembangunan=$this->cmbEditJenisPembangunan->Text;
			$txtNamaPerusahaan=addslashes(strtoupper($this->txtEditNamaPerusahaan->Text));
			$txtAlamatPerusahaan=addslashes(strtoupper($this->txtEditAlamat->Text));
			$txtNoTelepon=$this->txtEditNoTelp->Text;
			$txtNamaDirektur=addslashes(strtoupper($this->txtEditNamaDirektur->Text));
			$txtNpwp=$this->txtEditNPWP->Text;
            $txtHPS=$this->finance->toInteger($this->txtEditHPS->Text);			
			$txtPenawaran=$this->finance->toInteger($this->txtEditPenawaran->Text);			
            $tanggalkontrak=$this->TGL->tukarTanggal($this->cmbEditTK->Text);
            $mulai_pelaksanaan=$this->TGL->tukarTanggal($this->cmbEditMulaiP->Text);            
            $selesai_pelaksanaan=$this->TGL->tukarTanggal($this->cmbEditSelesaiP->Text);            
            $status_lelang=$this->cmbEditStatusLelang->Text;
			$str = "UPDATE uraian SET nama_uraian='$nama_uraian',idlok='$idlok',ket_lok='$ket_lok',kelurahan='$txtKelurahan',volume='$txtVolume',satuan='$txtSatuan',harga_satuan='$txtHargaSatuan',nilai='$txtNilai',jp='$jp',idjenis_pembangunan='$jenispembangunan',nama_perusahaan='$txtNamaPerusahaan',alamat_perusahaan='$txtAlamatPerusahaan',no_telepon='$txtNoTelepon',nama_direktur='$txtNamaDirektur',npwp='$txtNpwp',hps='$txtHPS',penawaran='$txtPenawaran',tgl_kontrak='$tanggalkontrak',tgl_mulai_pelaksanaan='$mulai_pelaksanaan',tgl_selesai_pelaksanaan='$selesai_pelaksanaan',status_lelang='$status_lelang' WHERE iduraian='$iduraian'";
			$this->DB->updateRecord($str);
            $this->kegiatan->redirect('d.belanja.Uraian',array('id'=>$idproyek));			
		}else {
			$this->idProcess='edit';
		}
	}

	private function disableAndEnabled ($flag=false) {
		if ($flag) {						
//			$this->txtAddKelurahan->Enabled=true;
			$this->txtAddNamaUraian->Enabled=true;
			$this->txtAddVolume->Enabled=true;			
			$this->txtAddSatuan->Enabled=true;
            $this->txtAddHargaSatuan->Enabled=true;
			$this->txtAddNilaiPagu->Enabled=true;
			$this->cmbAddJP->Enabled=true;
            $this->cmbAddJenisPembangunan->Enabled=true;
			$this->txtAddNamaPerusahaan->Enabled=true;
			$this->txtAddAlamat->Enabled=true;
			$this->txtAddNoTelp->Enabled=true;
			$this->txtAddNamaDirektur->Enabled=true;
			$this->txtAddNPWP->Enabled=true;
            $this->txtAddHPS->Enabled=true;
			$this->txtAddPenawaran->Enabled=true;	
            $this->cmbAddStatusLelang->Enabled=true;
			$this->btnSaveData->Enabled=true;			
		}else {					
//			$this->txtAddKelurahan->Enabled=false;
			$this->txtAddNamaUraian->Enabled=false;
			$this->txtAddVolume->Enabled=false;
            $this->txtAddHargaSatuan->Enabled=false;
			$this->txtAddNilaiPagu->Enabled=false;
			$this->cmbAddJP->Enabled=false;
            $this->cmbAddJenisPembangunan->Enabled=false;
			$this->txtAddSatuan->Enabled=false;
			$this->txtAddNamaPerusahaan->Enabled=false;
			$this->txtAddAlamat->Enabled=false;
			$this->txtAddNoTelp->Enabled=false;
			$this->txtAddNamaDirektur->Enabled=false;
			$this->txtAddNPWP->Enabled=false;
            $this->txtAddHPS->Enabled=false;
			$this->txtAddPenawaran->Enabled=false;			
            $this->cmbAddStatusLelang->Enabled=false;
			$this->btnSaveData->Enabled=false;			
		}
	}
	
	private function disableComponentRekening1 () {		
		$this->cmbAddKelompok->DataSource=array();
		$this->cmbAddKelompok->Enabled=false;
		$this->cmbAddKelompok->dataBind();
					
		$this->cmbAddJenis->DataSource=array();
		$this->cmbAddJenis->Enabled=false;
		$this->cmbAddJenis->dataBind();	
					
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();	
					
		$this->cmbAddRincian->DataSource=array();
		$this->cmbAddRincian->Enabled=false;
		$this->cmbAddRincian->dataBind();	
	}
	
	private function disableComponentRekening2 () {	
		$this->cmbAddJenis->DataSource=array();
		$this->cmbAddJenis->Enabled=false;
		$this->cmbAddJenis->dataBind();	
					
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();	
					
		$this->cmbAddRincian->DataSource=array();
		$this->cmbAddRincian->Enabled=false;
		$this->cmbAddRincian->dataBind();	
	}
	private function disableComponentRekening3 () {
								
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();	
					
		$this->cmbAddRincian->DataSource=array();
		$this->cmbAddRincian->Enabled=false;
		$this->cmbAddRincian->dataBind();	
	}
	
	private function disableComponentRekening4 () {					
		$this->cmbAddRincian->DataSource=array();
		$this->cmbAddRincian->Enabled=false;
		$this->cmbAddRincian->dataBind();	
	}	
	
	public function deleteRecord ($sender,$param) {
        $iduraian=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->DB->deleteRecord("uraian WHERE iduraian='$iduraian'");			
        $this->kegiatan->redirect('d.belanja.Uraian',array('id'=>$this->session['currentPageUraian']['dataKegiatan']['idproyek']));
    }  
    public function processRencanaTargetFisik () {        
        $this->populateRencanaTargetFisik();        	
    }
    public function itemCreated($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {        
           $item->ColumnBobot->TextBox->Width='30px'; 
           $item->ColumnBobot->TextBox->Enabled=false; 
           
           $item->Column1->TextBox->Width='30px'; 
           $item->Column1->TextBox->MaxLength='5'; 
           
           $item->Column2->TextBox->Width='30px'; 
           $item->Column2->TextBox->MaxLength='5'; 
           
           $item->Column3->TextBox->Width='30px'; 
           $item->Column3->TextBox->MaxLength='5'; 
           
           $item->Column4->TextBox->Width='30px'; 
           $item->Column4->TextBox->MaxLength='5'; 
           
           $item->Column5->TextBox->Width='30px'; 
           $item->Column5->TextBox->MaxLength='5'; 
           
           $item->Column6->TextBox->Width='30px'; 
           $item->Column6->TextBox->MaxLength='5'; 
           
           $item->Column7->TextBox->Width='30px'; 
           $item->Column7->TextBox->MaxLength='5'; 
           
           $item->Column8->TextBox->Width='30px'; 
           $item->Column8->TextBox->MaxLength='5'; 
           
           $item->Column9->TextBox->Width='30px'; 
           $item->Column9->TextBox->MaxLength='5'; 
           
           $item->Column10->TextBox->Width='30px'; 
           $item->Column10->TextBox->MaxLength='5'; 
           
           $item->Column11->TextBox->Width='30px'; 
           $item->Column11->TextBox->MaxLength='5'; 
           
           $item->Column12->TextBox->Width='30px'; 
           $item->Column12->TextBox->MaxLength='5';      
           
           $item->EditColumn->UpdateButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->UpdateButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->EditColumn->CancelButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->CancelButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->DeleteColumn->Button->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->DeleteColumn->Button->ClientSide->OnComplete="$('loadingbar').hide()";           
           
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')  {            
           $item->EditColumn->EditButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->EditButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->DeleteColumn->Button->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->DeleteColumn->Button->ClientSide->OnComplete="$('loadingbar').hide()";
        }        
    }
    private function populateRencanaTargetFisik () {
        $idproyek=$this->session['currentPageUraian']['dataKegiatan']['idproyek'];
		$str = "SELECT iduraian,rekening,nama_uraian,nilai FROM uraian WHERE idproyek=$idproyek ORDER BY rekening ASC";	
		$this->DB->setFieldTable(array('iduraian','rekening','nama_uraian','nilai'));
		$r=$this->DB->getRecord($str);		        
        $result=array();
        while (list($k,$v)=each($r)) {
            $iduraian=$v['iduraian'];
            $nilai=$v['nilai'];
            $v['nilai']=$this->finance->toRupiah($nilai);
            $nilaipagu=$_SESSION['currentPageUraian']['dataKegiatan']['nilai_pagu'];
            $v['bobot']=($nilai > 0)?number_format(($nilai/$nilaipagu)*100,2):0.00;
            $str = "SELECT CONCAT (bulan,'_',fisik) AS bulanfisik FROM target_uraian WHERE iduraian=$iduraian";
            $this->DB->setFieldTable(array('bulanfisik'));
            $datauraian=$this->DB->getRecord($str);
            $v['01']=0;
            $v['02']=0;
            $v['03']=0;
            $v['04']=0;
            $v['05']=0;
            $v['06']=0;
            $v['07']=0;
            $v['08']=0;
            $v['09']=0;
            $v['10']=0;
            $v['11']=0;
            $v['12']=0;
            while (list($m,$n)=each($datauraian)) {
                $bulanfisik=explode('_',$n['bulanfisik']);
                $v[$bulanfisik[0]]=$bulanfisik[1];
            }
            $result[$k]=$v;
        }
        $this->gridRencanaTargetFisik->DataSource=$result;
        $this->gridRencanaTargetFisik->dataBind();	
    }
    public function editItem($sender,$param) {           
        $this->idProcess='view';
        $this->gridRencanaTargetFisik->EditItemIndex=$param->Item->ItemIndex;
        $this->populateRencanaTargetFisik ();        
    }
    public function cancelItem($sender,$param) {        
        $this->idProcess='view';
        $this->gridRencanaTargetFisik->EditItemIndex=-1;
        $this->populateRencanaTargetFisik ();        
    }
    public function saveItem($sender,$param) {        
        $this->idProcess='view';
        $item=$param->Item;
        $id=$this->gridRencanaTargetFisik->DataKeys[$item->ItemIndex];
        $this->DB->query('BEGIN');
        $tahun=$_SESSION['ta'];
        $bobot=$item->ColumnBobot->TextBox->Text;                         
        $bulan[1]=$item->Column1->TextBox->Text;            
        $bulan[2]=$item->Column2->TextBox->Text;
        $bulan[3]=$item->Column3->TextBox->Text;
        $bulan[4]=$item->Column4->TextBox->Text;
        $bulan[5]=$item->Column5->TextBox->Text;
        $bulan[6]=$item->Column6->TextBox->Text;
        $bulan[7]=$item->Column7->TextBox->Text;
        $bulan[8]=$item->Column8->TextBox->Text;
        $bulan[9]=$item->Column9->TextBox->Text;
        $bulan[10]=$item->Column10->TextBox->Text;
        $bulan[11]=$item->Column11->TextBox->Text;
        $bulan[12]=$item->Column12->TextBox->Text;
        $total=0;
        for ($i=1;$i<=12;$i+=1) {
            $total+=$bulan[$i];
        }        
        if (round($total,2)<=round($bobot,2)) {
            $nilai_pagu=$this->finance->toInteger($item->ColumnPaguUraian->TextBox->Text);                              
            if ($this->DB->deleteRecord("target_uraian WHERE iduraian=$id")) {           
                $i=0;
                for ($i=0;$i<12;$i++) {
                    $no_bulan=$i+1;                    
                    $ket_bulan=$no_bulan <= 9 ? "0$no_bulan" : $no_bulan;
                    $target_fisik=$bulan[$no_bulan];
                    $target_sp2d=($target_fisik/$bobot)*$nilai_pagu;                    
                    if (12 > $i+1) {                                                                                        
                        $values="$values ($id,'$target_fisik','$target_sp2d','$ket_bulan','$tahun'),";
                    }else{                                
                        $values="$values ($id,'$target_fisik','$target_sp2d','$ket_bulan','$tahun')";
                    }   
                }
                $str = "INSERT INTO target_uraian (iduraian,fisik,target_sp2d,bulan,tahun) VALUES $values";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');            
            }else {
                $this->DB->query('ROLLBACK');
            }        
        }
        $this->gridRencanaTargetFisik->EditItemIndex=-1;
        $this->populateRencanaTargetFisik ();
    }
    public function deleteItem($sender,$param) {        
        $this->idProcess='view';
        $id=$this->gridRencanaTargetFisik->DataKeys[$param->Item->ItemIndex];        
        $this->DB->deleteRecord("target_uraian WHERE iduraian=$id");
        $this->gridRencanaTargetFisik->EditItemIndex=-1;
        $this->populateRencanaTargetFisik ();
    }
    public function closeRencanaTargetKinerjaPage ($sender,$param) {
        $idproyek=$_SESSION['currentPageUraian']['dataKegiatan']['idproyek'];
        $_SESSION['currentPageUraian']['RencanaTargetFisikPage']=false;
        $this->kegiatan->redirect('d.belanja.Uraian',array('id'=>$idproyek));
    }
}
?>