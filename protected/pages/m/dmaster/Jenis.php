<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Jenis extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->createObjRekening();
        $this->showJenis=true;
        $this->showRekening=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageJenis'])||$_SESSION['currentPageJenis']['page_name']!='m.dmaster.Jenis') {
                $_SESSION['currentPageJenis']=array('page_name'=>'m.dmaster.Jenis','page_num'=>0);												
			}
			$this->populateData ();		
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageJenis']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageJenis']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('rek3','no_rek3');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageJenis']['page_num']=0;}
        $str = "SELECT no_rek3,nama_rek3 FROM rek3 ORDER BY no_rek3 ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_rek3','nama_rek3'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	public function addProcess ($sender,$param) {
		$this->idProcess='add';		        
        $this->cmbAddKelompok->DataSource=$this->rekening->getList('rek2',array('no_rek2','nama_rek2'),'no_rek2',null,7);  ;
        $this->cmbAddKelompok->dataBind();	
	} 
	public function cmbKelompokChanged ($sender,$param) {
		$this->idProcess='add';		
		$transaksi=$this->cmbAddKelompok->Text;
        if ($transaksi=='none')
            $this->lblAddKodeKelompok->Text='';
        else
            $this->lblAddKodeKelompok->Text="$transaksi.";
	}
	public function checkKodeJenis ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeJenis'?'add':'edit';
        $no_rek3=$param->Value;
        if ($no_rek3 != '') {
            try {
                $kode_transaksi=$sender->getId()==='checkAddKodeJenis'?$this->lblAddKodeKelompok->Text:$this->lblEditKodeKelompok->Text;
                $no_rek3 = $kode_transaksi.$no_rek3;
                if ($this->hiddennorek3->Value != $no_rek3){                                        
                    if ($this->DB->checkRecordIsExist ('no_rek3','rek3',$no_rek3)) {
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
			$nama_jenis=strtoupper($this->txtAddNamaJenis->Text);
            $kode_kelompok=$this->cmbAddKelompok->Text;
			$kode_jenis=$kode_kelompok.'.'.$this->txtAddKodeJenis->Text;
			$str = "INSERT INTO rek3 (no_rek3,no_rek2,nama_rek3) VALUES('$kode_jenis','$kode_kelompok','$nama_jenis')";			
            $this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Jenis');
		}
	}
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$result = $this->rekening->getList("rek3 WHERE no_rek3='$id'",array('no_rek3','no_rek2','nama_rek3'));		
    	$this->hiddennorek3->Value=$id;
		$this->lblEditKodeKelompok->Text=$result[1]['no_rek2'].'.';
		$this->txtEditKodeJenis->Text=$this->rekening->getKodeRekeningTerakhir($result[1]['no_rek3']);
		$this->txtEditNamaJenis->Text=$result[1]['nama_rek3'];
	}	
	
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
			$id=$this->hiddennorek3->Value;
            $no_rek2=$this->lblEditKodeKelompok->Text;
			$no_rek3=$no_rek2.$this->txtEditKodeJenis->Text;
			$nama_jenis=strtoupper($this->txtEditNamaJenis->Text);
			$str = "UPDATE rek3 SET no_rek3='$no_rek3',nama_rek3='$nama_jenis' WHERE no_rek3='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Jenis');					
		}
	}
    
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("rek3 WHERE no_rek3='$id'");		
		$this->populateData();
	}
    public function printOut ($sender,$param) {           
        $this->createObjReport();                
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printRekening('jenis');
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printRekening('jenis');                
            break;
        }        
    }
}

?>