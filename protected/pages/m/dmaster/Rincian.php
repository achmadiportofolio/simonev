<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Rincian extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->createObjRekening();
        $this->showRincian=true;
        $this->showRekening=true;        
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (!isset($_SESSION['currentPageRincian'])||$_SESSION['currentPageRincian']['page_name']!='m.dmaster.Rincian') {
                $_SESSION['currentPageRincian']=array('page_name'=>'m.dmaster.Rincian','page_num'=>0,'no_rek4'=>'none','search'=>false);												
			}
            $rekening=$this->rekening->getList('rek2',array('no_rek2','nama_rek2'),'no_rek2',null,7);        
            $this->cmbKelompok->DataSource=$rekening;
            $this->cmbKelompok->dataBind();
            $_SESSION['currentPageRincian']['no_rek4']='none';
            $_SESSION['currentPageRincian']['search']=false;
			$this->populateData ();			
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageRincian']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageRincian']['search']);
	}
    public function filterRecord ($sender,$param) {
        $_SESSION['currentPageRincian']['no_rek4']='none';
        $_SESSION['currentPageRincian']['search']=true;
        $this->populateData($_SESSION['currentPageRincian']['search']);
    }
    public function changeRekening ($sender,$param) {	
        $_SESSION['currentPageRincian']['no_rek4']='none';
		switch ($sender->getId()) {
			case 'cmbTransaksi' :
				$idtransaksi=$this->cmbTransaksi->Text;
				$this->disableComponentRekening1 ();
				$this->disableAndEnabled();
				if ($idtransaksi != 'none' || $idtransaksi != '') {
					$result=$this->rekening->getListKelompok($idtransaksi);
					if (count($result)> 1) {
						$this->cmbKelompok->DataSource=$result;
						$this->cmbKelompok->Enabled=true;
						$this->cmbKelompok->dataBind();
					}
				}
			break;			
			case 'cmbKelompok' :
				$idkelompok = $this->cmbKelompok->Text;
				$this->disableComponentRekening2 ();
				$this->disableAndEnabled();
				if ($idkelompok != 'none' || $idkelompok !='') {
					$result=$this->rekening->getListJenis($idkelompok);
					if (count($result)> 1) {
						$this->cmbJenis->DataSource=$result;
						$this->cmbJenis->Enabled=true;
						$this->cmbJenis->dataBind();
					}
				}
			break;
			case 'cmbJenis' :
				$idjenis = $this->cmbJenis->Text;
				$this->disableComponentRekening3 ();
				$this->disableAndEnabled();
				if ($idjenis != 'none' || $idjenis != '') {
					$result=$this->rekening->getListObjek($idjenis);
					if (count($result)> 1) {
						$this->cmbObjek->DataSource=$result;
						$this->cmbObjek->Enabled=true;
						$this->cmbObjek->dataBind();
					}
				}
			break;			
			case 'cmbObjek' :
				$idobjek = $this->cmbObjek->Text;
                if ($idobjek != 'none') {
                    $_SESSION['currentPageRincian']['no_rek4']=$idobjek;
                    $this->populateData();
                    $this->disableAndEnabled(true);
                }else{
                    $this->disableAndEnabled(false);
                }				
			break;			
		}
	}	
	protected function populateData ($search=false) {        
        $no_rek4=$_SESSION['currentPageRincian']['no_rek4'];  
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageRincian']['page_num'];        
        if ($search) {
            $str_jumlah='rek5';
            $str = 'SELECT no_rek5,nama_rek5 FROM rek5';
            $kriteria=$this->txtKriteria->Text;
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah WHERE no_rek5 LIKE '$kriteria%'";
                $str = "$str WHERE no_rek5 LIKE '$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah WHERE nama_rek5 LIKE '%$kriteria%'";
                $str = "$str WHERE nama_rek5 LIKE '%$kriteria%'";
            }                
        }else {
            $str_filter=$no_rek4=='none'||$no_rek4==''?'':" WHERE no_rek4='$no_rek4'";
            $str_jumlah="rek5$str_filter";
            $str = "SELECT no_rek5,nama_rek5 FROM rek5$str_filter";
        }        
		$jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'no_rek5');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageRincian']['page_num']=0;}
        $str = "$str ORDER BY no_rek5 ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_rek5','nama_rek5'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}	  	
    public function addProcess ($sender,$param) {
		$this->idProcess='add';		               
        $this->cmbAddJenis->DataSource=$this->rekening->getList('rek3',array('no_rek3','nama_rek3'),'no_rek3',null,7);  ;
        $this->cmbAddJenis->dataBind();
	} 
	public function cmbJenisChanged ($sender,$param) {
		$this->idProcess='add';		
        $rek3=$sender->Text;        
        if ($rek3 == 'none') {
            $bool=false;
            $objek=array(); 
            $this->txtAddKodeRincian->Enabled=$bool;
            $this->txtAddNamaRincian->Enabled=$bool;
            $this->btnSave->Enabled=$bool;
        }else {
            $objek=$this->rekening->getList("rek4 WHERE no_rek3='$rek3'",array('no_rek4','nama_rek4'),'no_rek4',null,7);
            $bool=true;
        }
		$this->cmbAddObjek->DataSource=$objek;
        $this->cmbAddObjek->Enabled=$bool;
        $this->cmbAddObjek->dataBind();                
        $this->lblAddKodeObjek->Text='';        
	}
    public function cmbObjekChanged ($sender,$param) {
		$this->idProcess='add';		        
		$transaksi=$this->cmbAddObjek->Text;
        if ($transaksi=='none' || $transaksi=='') {            
            $this->lblAddKodeObjek->Text='';
            $this->txtAddKodeRincian->Enabled=false;
            $this->txtAddNamaRincian->Enabled=false;
            $this->btnSave->Enabled=false;
        }else {
            $this->lblAddKodeObjek->Text="$transaksi.";
            $this->txtAddKodeRincian->Enabled=true;
            $this->txtAddNamaRincian->Enabled=true;            
            $this->btnSave->Enabled=true;
        }
	}
	public function checkKodeRincian ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeRincian'?'add':'edit';        
        $no_rek5=$param->Value;
        if ($no_rek5 != '') {
            try {
                $kode_transaksi=$sender->getId()==='checkAddKodeRincian'?$this->lblAddKodeObjek->Text:$this->lblEditKodeObjek->Text;
                $no_rek5 = $kode_transaksi.$no_rek5;
                if ($this->hiddennorek5->Value != $no_rek5){                                        
                    if ($this->DB->checkRecordIsExist ('no_rek5','rek5',$no_rek5)) {
                        $param->IsValid=false;					
                    }
                }
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }		
	}
	public function saveData($sender,$param) {
		if ($this->Page->IsValid) {
			$nama_rincian=strtoupper($this->txtAddNamaRincian->Text);
			$kode_rincian=$this->lblAddKodeObjek->Text.$this->txtAddKodeRincian->Text;					
			$kode_Objek=$this->cmbAddObjek->Text;	
			$str = "INSERT INTO rek5 (no_rek5,no_rek4,nama_rek5) VALUES('$kode_rincian','$kode_Objek','$nama_rincian')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Rincian');
		}
	}	
	public function editRecord ($sender,$param) {	
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);   		
		$result = $this->rekening->getList("rek5 WHERE no_rek5='$id'",array('no_rek5','no_rek4','nama_rek5'));		
		$this->hiddennorek5->Value=$id;		
		$this->lblEditKodeObjek->Text=$result[1]['no_rek4'].'.';
		$this->txtEditKodeRincian->Text=$this->rekening->getKodeRekeningTerakhir($result[1]['no_rek5']);
		$this->txtEditNamaRincian->Text=$result[1]['nama_rek5'];
	}
	
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
			$id=$this->hiddennorek5->Value;
			$no_rek5=$this->lblEditKodeObjek->Text.$this->txtEditKodeRincian->Text;
			$nama_rek5=strtoupper($this->txtEditNamaRincian->Text);
			$str = "UPDATE rek5 SET no_rek5='$no_rek5',nama_rek5='$nama_rek5' WHERE no_rek5='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Rincian');					
		}
	}
    public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("rek5 WHERE no_rek5='$id'");		
		$this->populateData();
	}
    private function disableAndEnabled ($flag=false) {
		if ($flag) {						
				
		}else {					
            
		}
	}
	
	private function disableComponentRekening1 () {		
		$this->cmbKelompok->DataSource=array();
		$this->cmbKelompok->Enabled=false;
		$this->cmbKelompok->dataBind();
					
		$this->cmbJenis->DataSource=array();
		$this->cmbJenis->Enabled=false;
		$this->cmbJenis->dataBind();	
					
		$this->cmbObjek->DataSource=array();
		$this->cmbObjek->Enabled=false;
		$this->cmbObjek->dataBind();	
	
	}
	
	private function disableComponentRekening2 () {	
		$this->cmbJenis->DataSource=array();
		$this->cmbJenis->Enabled=false;
		$this->cmbJenis->dataBind();	
					
		$this->cmbObjek->DataSource=array();
		$this->cmbObjek->Enabled=false;
		$this->cmbObjek->dataBind();	
	}
	private function disableComponentRekening3 () {								
		$this->cmbObjek->DataSource=array();
		$this->cmbObjek->Enabled=false;
		$this->cmbObjek->dataBind();			
	}   
    public function printOut ($sender,$param) {           
        $this->createObjReport();                
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printRekening('rincian');
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printRekening('rincian');                
            break;
        }        
    }
}

?>