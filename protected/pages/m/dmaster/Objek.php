<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Objek extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->createObjRekening();
        $this->showObjek=true;
        $this->showRekening=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageObjek'])||$_SESSION['currentPageObjek']['page_name']!='m.dmaster.Objek') {
                $_SESSION['currentPageObjek']=array('page_name'=>'m.dmaster.Objek','page_num'=>0,'search'=>false,'no_rek3'=>'none');												
			}
            $_SESSION['currentPageObjek']['search']=false;
            $_SESSION['currentPageObjek']['no_rek3']='none';
            $rekening=$this->rekening->getList('rek2',array('no_rek2','nama_rek2'),'no_rek2',null,7);        
            $this->cmbKelompok->DataSource=$rekening;
            $this->cmbKelompok->dataBind();
			$this->populateData ();		
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageObjek']['page_num']=$param->NewPageIndex;
		$this->populateData($_SESSION['currentPageObjek']['search']);
	} 
    public function filterRecord ($sender,$param) {
        $_SESSION['currentPageObjek']['no_rek3']='none';
        $_SESSION['currentPageObjek']['search']=true;
        $this->populateData($_SESSION['currentPageObjek']['search']);
    }
    public function changeRekening ($sender,$param) {	
        $_SESSION['currentPageObjek']['no_rek3']='none';
		switch ($sender->getId()) {			
			case 'cmbKelompok' :
				$idkelompok = $this->cmbKelompok->Text;
				$this->cmbJenis->DataSource=array();
                $this->cmbJenis->Enabled=false;
                $this->cmbJenis->dataBind();	
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
				if ($idjenis != 'none' || $idjenis != '') {
					$_SESSION['currentPageObjek']['no_rek3']=$idjenis;
                    $this->populateData();
				}
			break;						
		}
	}
	protected function populateData ($search=false) {
        $no_rek3=$_SESSION['currentPageObjek']['no_rek3'];        
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageObjek']['page_num'];        
        if ($search) {
            $str_jumlah='rek4';
            $str = 'SELECT no_rek4,nama_rek4 FROM rek4 ';
            $kriteria=$this->txtKriteria->Text;
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah WHERE no_rek4 LIKE '$kriteria%'";
                $str = "$str WHERE no_rek4 LIKE '$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah WHERE nama_rek5 LIKE '%$kriteria%'";
                $str = "$str WHERE nama_rek4 LIKE '%$kriteria%'";
            }                
        }else {
            $str_filter=$no_rek3=='none'||$no_rek3==''?'':" WHERE no_rek3='$no_rek3'";
            $str_jumlah="rek4$str_filter";
            $str = "SELECT no_rek4,nama_rek4 FROM rek4$str_filter ";
        }
		$jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'no_rek4');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageObjek']['page_num']=0;}
        $str = "$str ORDER BY no_rek4 ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_rek4','nama_rek4'));
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
		$transaksi=$this->cmbAddJenis->Text;
        if ($transaksi=='none')
            $this->lblAddKodeObjek->Text='';
        else
            $this->lblAddKodeJenis->Text="$transaksi.";
	}	
    public function checkKodeObjek ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeObjek'?'add':'edit';
        $no_rek4=$param->Value;
        if ($no_rek4 != '') {
            try {
                $kode_transaksi=$sender->getId()==='checkAddKodeObjek'?$this->lblAddKodeJenis->Text:$this->lblEditKodeJenis->Text;
                $no_rek4 = $kode_transaksi.$no_rek4;
                if ($this->hiddennorek4->Value != $no_rek4){                                        
                    if ($this->DB->checkRecordIsExist ('no_rek4','rek4',$no_rek4)) {
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
			$nama_objek=strtoupper($this->txtAddNamaObjek->Text);
            $kode_jenis=$this->cmbAddJenis->Text;
			$kode_objek=$kode_jenis.'.'.$this->txtAddKodeObjek->Text;			
			$str = "INSERT INTO rek4 (no_rek4,no_rek3,nama_rek4) VALUES('$kode_objek','$kode_jenis','$nama_objek')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Objek');
		}
	}
	public function editRecord ($sender,$param) {	
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);   
		$result = $this->rekening->getList("rek4 WHERE no_rek4='$id'",array('no_rek4','no_rek3','nama_rek4'));		
		$this->hiddennorek4->Value=$id;		
		$this->lblEditKodeJenis->Text=$result[1]['no_rek3'].'.';
		$this->txtEditKodeObjek->Text=$this->rekening->getKodeRekeningTerakhir($result[1]['no_rek4']);
		$this->txtEditNamaObjek->Text=$result[1]['nama_rek4'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
			$id=$this->hiddennorek4->Value;			
            $kode_jenis=$this->lblEditKodeJenis->Text;
            $kode_objek=$kode_jenis.$this->txtEditKodeObjek->Text;
			$nama_jenis=strtoupper($this->txtEditNamaObjek->Text);
			$str = "UPDATE rek4 SET no_rek4='$kode_objek',nama_rek4='$nama_jenis' WHERE no_rek4='$id'";
            $this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Objek');
        }
	}
    public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("rek4 WHERE no_rek4='$id'");		
		$this->populateData();
	}
    public function printOut ($sender,$param) {           
        $this->createObjReport();                
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printRekening('objek');
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printRekening('objek');                
            break;
        }        
    }
}

?>