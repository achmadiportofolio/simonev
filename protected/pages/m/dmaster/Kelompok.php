<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Kelompok extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->showKelompok=true;  
        $this->showRekening=true;
        $this->createObjRekening();
		if (!$this->IsPostBack&&!$this->IsCallBack) {		
            if (!isset($_SESSION['currentPageKelompok'])||$_SESSION['currentPageKelompok']['page_name']!='m.dmaster.Kelompok') {
                $_SESSION['currentPageKelompok']=array('page_name'=>'m.dmaster.Kelompok','page_num'=>0);												
			}			
			$this->populateData ();
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKelompok']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKelompok']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('rek2','no_rek2');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageKelompok']['page_num']=0;}
        $str = "SELECT no_rek2,nama_rek2 FROM rek2 ORDER BY no_rek2 ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_rek2','nama_rek2'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	public function addProcess ($sender,$param) {
		$this->idProcess='add';		
        $this->cmbAddTransaksi->DataSource=$this->rekening->getList('rek1',array('no_rek1','nama_rek1'),'no_rek1',null,7);  ;
        $this->cmbAddTransaksi->dataBind();
	}    
	public function cmbTransaksiChanged ($sender,$param) {
		$this->idProcess='add';		
        $transaksi=$this->cmbAddTransaksi->Text;
        if ($transaksi=='none')
            $this->lblAddKodeTransaksi->Text='';
        else
            $this->lblAddKodeTransaksi->Text="$transaksi.";
	}
	public function checkKodeKelompok ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeKelompok'?'add':'edit';
        $no_rek2=$param->Value;
        if ($no_rek2 != '') {
            try {
                $kode_transaksi=$sender->getId()==='checkAddKodeKelompok'?$this->lblAddKodeTransaksi->Text:$this->lblEditKodeTransaksi->Text;
                $no_rek2 = $kode_transaksi.$no_rek2;
                if ($this->hiddennorek2->Value != $no_rek2){                                        
                    if ($this->DB->checkRecordIsExist ('no_rek2','rek2',$no_rek2)) {
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
			$nama_kelompok=strtoupper($this->txtAddNamaKelompok->Text);
            $kode_transaksi=$this->cmbAddTransaksi->Text;
			$kode_kelompok=$this->lblAddKodeTransaksi->Text.$this->txtAddKodeKelompok->Text;
			$str = "INSERT INTO rek2 (no_rek2,no_rek1,nama_rek2) VALUES('$kode_kelompok','$kode_transaksi','$nama_kelompok')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Kelompok');
		}
	}
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->rekening->getList("rek2 WHERE no_rek2='$id'",array('no_rek2','no_rek1','nama_rek2'));		
		$this->hiddennorek2->Value=$id;		
		$this->lblEditKodeTransaksi->Text=$result[1]['no_rek1'].'.';
		$this->txtEditKodeKelompok->Text=$this->rekening->getKodeRekeningTerakhir($result[1]['no_rek2']);
		$this->txtEditNamaKelompok->Text=$result[1]['nama_rek2'];
	}	
	
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennorek2->Value;
			$kode=$this->lblEditKodeTransaksi->Text.$this->txtEditKodeKelompok->Text;			
			$nama_kelompok=strtoupper($this->txtEditNamaKelompok->Text);
			$str = "UPDATE rek2 SET no_rek2='$kode',nama_rek2='$nama_kelompok' WHERE no_rek2='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Kelompok');						
		}
	}
    public function deleteRecord ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("rek2 WHERE no_rek2='$id'");		
		$this->populateData();		
	}
    public function printOut ($sender,$param) {           
        $this->createObjReport();                
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printRekening('kelompok');
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printRekening('kelompok');                
            break;
        }        
    }
}

?>