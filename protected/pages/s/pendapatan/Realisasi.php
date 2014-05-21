<?php
prado::using ('Application.pages.s.pendapatan.MainPagePendapatan');
class Realisasi extends MainPagePendapatan {	
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showRealisasi=true;                
		if (!$this->IsCallback&&!$this->IsPostback) {
            if (isset($_SESSION['currentPageRealisasiTarget']['dataPendapatan']['no_rek5'])) {
                $this->idProcess='add';
                $this->processDetail();
            }else {
                if (!isset($_SESSION['currentPageRealisasiTarget'])||$_SESSION['currentPageRealisasiTarget']['page_name']!='s.pendapatan.Realisasi') {
                    $_SESSION['currentPageRealisasiTarget']=array('page_name'=>'s.pendapatan.Realisasi','page_num'=>0,'no_rek5'=>'none','search'=>false,'dataPendapatan'=>array());												
                }  
                $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
                $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
                $this->toolbarOptionsTahunAnggaran->dataBind(); 

                $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
                $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
                $this->toolbarOptionsBulanRealisasi->dataBind();             

                $_SESSION['currentPageRealisasiTarget']['search']=false;
                $_SESSION['currentPageRealisasiTarget']['no_rek5']='none';

                $rekening=$this->kegiatan->getList('rek2 WHERE no_rek1=4',array('no_rek2','nama_rek2'),'no_rek2',null,7);        
                $this->cmbAddKelompok->DataSource=$rekening;
                $this->cmbAddKelompok->dataBind();

                $this->populateData();
            }
                                   
		}			
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;                
        $this->populateData ($_SESSION['currentPageRealisasiTarget']['search']);
	}
    public function changeBulanRealisasi ($sender,$param) {	
        $_SESSION['bulanrealisasi']=$this->toolbarOptionsBulanRealisasi->Text;
        $this->populateData ($_SESSION['currentPageRealisasiTargetTarget']['search']);
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageRealisasiTarget']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}  
    public function changeRekening ($sender,$param) {	
        $_SESSION['currentPageRealisasiTarget']['no_rek5']='none';
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
				if ($idrincian == 'none' && $idrincian != '') {                    
                    $this->disableAndEnabled(false);
                }else {
                    $_SESSION['currentPageRealisasiTarget']['no_rek5']=$idrincian;
                    $this->populateData();
                    $this->disableAndEnabled(true);
                }
            break;
		}
	}	    
    public function filterRecord($sender,$param) {
        $_SESSION['currentPageRealisasiTarget']['no_rek5']='none';
        $_SESSION['currentPageRealisasiTarget']['search']=true;
        $this->populateData($_SESSION['currentPageRealisasiTarget']['search']);
    }
    protected function populateData($search=false) {		
        $tahun=$_SESSION['ta'];        
        $no_rek5=$_SESSION['currentPageRealisasiTarget']['no_rek5'];
        $str_filter=$no_rek5=='none'||$no_rek5==''?'':" AND tp.no_rek5='$no_rek5'";
        $str = "select tp.no_rek5,rek5.nama_rek5,tp.target,rp.realisasi,rp.tanggal_realisasi FROM realisasi_penerimaan rp,target_penerimaan tp,rek5 WHERE rp.idtarget=tp.idtarget AND tp.no_rek5=rek5.no_rek5 AND tp.tahun=$tahun $str_filter";            
        $str_jumlah = "realisasi_penerimaan rp,target_penerimaan tp,rek5 WHERE rp.idtarget=tp.idtarget AND tp.no_rek5=rek5.no_rek5 AND tp.tahun=$tahun $str_filter";
        if ($search) {            
            $kriteria=$this->txtKriteria->Text;            
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah AND tp.no_rek5 LIKE '%$kriteria%'";
                $str = "$str AND tp.no_rek5 LIKE '%$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah AND nama_rek5 LIKE '%$kriteria%'";
                $str = "$str AND nama_rek5 LIKE '%$kriteria%'";
            }                
        }        
        $jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'tp.no_rek5');
        $this->RepeaterS->VirtualItemCount=$jumlah_baris;
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageRealisasiTarget']['page_num'];
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit <= 0) {$offset=0;$limit=10;$_SESSION['currentPageRealisasiTarget']['page_num']=0;}
        $str = "$str ORDER BY no_rek5 ASC,bulan ASC LIMIT $offset,$limit";
        $this->DB->setFieldTable(array('no_rek5','nama_rek5','target','realisasi','tanggal_realisasi'));
		$r=$this->DB->getRecord($str);
        $this->RepeaterS->DataSource=$r;
        $this->RepeaterS->dataBind();	
	}	
    public function addProcess ($sender,$param) {
        if ($this->IsValid) {
            $tahun=$_SESSION['ta']; 
            $idrincian=$this->cmbAddRincian->Text;
            $str = "SELECT tp.idtarget,vr.no_rek2,vr.nama_rek2,vr.no_rek3,vr.nama_rek3,vr.no_rek4,vr.nama_rek4,tp.no_rek5,vr.nama_rek5,tp.target FROM v_rekening vr,target_penerimaan tp WHERE tp.no_rek5=vr.no_rek5 AND tp.no_rek5='$idrincian' AND tp.tahun=$tahun";
            $this->DB->setFieldTable(array('idtarget','no_rek2','nama_rek2','no_rek3','nama_rek3','no_rek4','nama_rek4','no_rek5','nama_rek5','target'));
            $r=$this->DB->getRecord($str);
            if (isset($r[1])) {
                $_SESSION['currentPageRealisasiTarget']['dataPendapatan']=$r[1];
                $this->redirect('s.pendapatan.Realisasi');
            }else {
                $this->errormessage->Text="<span style='color:red'>Nilai target penerimaan kode rekening <strong>($idrincian) belum terisi.</strong></span>";
            }
        }        
    }
    public function processdetail () {
        $idtarget= $_SESSION['currentPageRealisasiTarget']['dataPendapatan']['idtarget'];
        $bulan=$this->TGL->getMonth(3);
        $str = "SELECT bulan FROM realisasi_penerimaan WHERE idtarget=$idtarget ORDER BY bulan ASC";
        $this->DB->setFieldTable(array('bulan'));	
		$r=$this->DB->getRecord($str);   
        $month=$bulan; 
        if (isset($r[1])) {
            foreach ($r as $n) {                        
                $bulan_temp[$n['bulan']]=$n['bulan'];
            }                 
            while (list($k,$v)=each($bulan)) {                    
                if (!in_array($k,$bulan_temp)) {                    
                    $temp[$k]=$v;
                }
            }
            $month=$temp;              
        }            			
        $this->cmbAddBulan->DataSource=$month;        
        $this->cmbAddBulan->dataBind();
        $this->populatePenerimaan ();
        $this->populateDataRealisasi ();
    }
    public function populatePenerimaan () {
        $idtarget= $_SESSION['currentPageRealisasiTarget']['dataPendapatan']['idtarget'];
        $target=$_SESSION['currentPageRealisasiTarget']['dataPendapatan']['target'];
        $bulan_ini = date('m');
        $str = "SELECT realisasi FROM realisasi_penerimaan WHERE idtarget=$idtarget";
        $this->DB->setFieldTable(array('realisasi'));
        $penerimaan_bulan_ini=$this->DB->getRecord("$str AND bulan='$bulan_ini'");
        $penerimaanbulanini=isset($penerimaan_bulan_ini[1])?$penerimaan_bulan_ini[1]['realisasi']:0;
        $this->penerimaanBulanINI->Text=$this->finance->toRupiah($penerimaanbulanini,false);        
        $bulan_lalu=date('m')-1;        
        $penerimaanbulanlalu=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<=$bulan_lalu");
        $this->penerimaanBulanLalu->Text=$this->finance->toRupiah($penerimaanbulanlalu,false);
        $penerimaanSampaiBulanINI=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<=$bulan_ini");
        $this->penerimaanSampaiBulanINI->Text=$this->finance->toRupiah($penerimaanSampaiBulanINI,false);
        $pencapaiantarget=$penerimaanSampaiBulanINI-$target;
        $this->pencapaiantarget->Text=$this->finance->toRupiah($pencapaiantarget,false);
        $this->persenpencapaiantarget->Text=$penerimaanSampaiBulanINI>0?number_format(($penerimaanSampaiBulanINI/$target)*100,2):'0.00';        
    }
    public function itemCreated($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {
            // set column width of textboxes                                    
            $item->RealisasiColumn->TextBox->Attributes->OnKeyUp='formatangka(this,false)';
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')  {
            // add an aleart dialog to delete buttons
            $item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'Apakah anda yakin ingin menghapus realisasi pendapatan ini ?\')) return false;';
        }        
    }
    public function populateDataRealisasi () {
        $idtarget= $_SESSION['currentPageRealisasiTarget']['dataPendapatan']['idtarget'];
        $str = "SELECT idrealisasi,realisasi,tanggal_realisasi FROM realisasi_penerimaan WHERE idtarget=$idtarget";
        $this->DB->setFieldTable(array('idrealisasi','realisasi','tanggal_realisasi'));
        $r=$this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=  each($r)) {
            $v['realisasi']=$this->finance->toRupiah($v['realisasi']);
            $result[$k]=$v;
        }
        $this->GridRealisasi->DataSource=$result;
        $this->GridRealisasi->dataBind();
    }
    public function editItem($sender,$param) {
        $this->idProcess='add';
        $this->GridRealisasi->EditItemIndex=$param->Item->ItemIndex;
        $this->populateDataRealisasi ();        
    }
    public function cancelItem($sender,$param) {
        $this->idProcess='add';
        $this->GridRealisasi->EditItemIndex=-1;
        $this->populateDataRealisasi ();       
    }
    public function saveItem($sender,$param) {
        $this->idProcess='add';
        $item=$param->Item;
        $id=$this->GridRealisasi->DataKeys[$item->ItemIndex];
        $realisasi=$this->finance->toInteger($item->RealisasiColumn->TextBox->Text);        
        $str = "UPDATE realisasi_penerimaan SET realisasi=$realisasi WHERE idrealisasi=$id";
        $this->DB->updateRecord($str);
        $this->GridRealisasi->EditItemIndex=-1;
        $this->populatePenerimaan();
        $this->populateDataRealisasi ();
    }
    public function deleteItem($sender,$param) {
        $this->idProcess='add';
        $id=$this->GridRealisasi->DataKeys[$param->Item->ItemIndex];
        $this->DB->deleteRecord("realisasi_penerimaan WHERE idrealisasi=$id");
        $this->redirect('s.pendapatan.Realisasi');
    }
    public function closeRealisasi () {
        unset($_SESSION['currentPageRealisasiTarget']);
        $this->redirect('s.pendapatan.Realisasi');
    }
    public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {
            $idtarget= $_SESSION['currentPageRealisasiTarget']['dataPendapatan']['idtarget'];
            $bulan=$this->cmbAddBulan->Text;
            $realisasi=$this->finance->toInteger($this->txtAddRealisasi->Text);
            $tahun=$_SESSION['ta'];
            $tanggal_realisasi="$tahun-$bulan-".date('m');
            $str = 'INSERT INTO realisasi_penerimaan (idrealisasi,idtarget,bulan,realisasi,tanggal_realisasi) VALUES ';
            $str .= "(NULL,$idtarget,'$bulan','$realisasi','$tanggal_realisasi')";
            $this->DB->insertRecord($str);
            $this->redirect('s.pendapatan.Realisasi');
        }
	}		
	private function disableAndEnabled ($flag=false) {
		if ($flag) {						
            $this->btnTambahRealisasi->Enabled=true;
		}else {					
            $this->btnTambahRealisasi->Enabled=false;
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
}
?>