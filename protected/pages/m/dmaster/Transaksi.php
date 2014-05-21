<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Transaksi extends MainPageDMaster {
	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->showTransaksi=true; 
        $this->showRekening=true;
        $this->createObjRekening(); 
		if (!$this->IsPostBack&&!$this->IsCallBack) {		
            if (!isset($_SESSION['currentPageTransaksi'])||$_SESSION['currentPageTransaksi']['page_name']!='m.dmaster.Transaksi') {
                $_SESSION['currentPageTransaksi']=array('page_name'=>'m.dmaster.Transaksi','page_num'=>0);												
			}
			$this->populateData ();
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageTransaksi']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageTransaksi']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('rek1','no_rek1');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageTransaksi']['page_num']=0;}
        $str = "SELECT no_rek1,nama_rek1 FROM rek1 ORDER BY no_rek1 ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('no_rek1','nama_rek1'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	
	public function checkKodeTransaksi ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeTransaksi'?'add':'edit';
        $no_rek1=$param->Value;
        if ($no_rek1 != '') {
            try {
                if ($this->hiddennorek1->Value != $no_rek1){                                        
                    if ($this->DB->checkRecordIsExist ('no_rek1','rek1',$no_rek1)) {
                        $param->IsValid=false;					
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
            $no_rek1=$this->txtAddKodeTransaksi->Text;
			$nama_transaksi=strtoupper($this->txtAddNamaTransaksi->Text);
			$str = "INSERT INTO rek1 (no_rek1,nama_rek1) VALUES ('$no_rek1','$nama_transaksi')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Transaksi');
		}
	}
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->rekening->getList("rek1 WHERE no_rek1='$id'",array('no_rek1','nama_rek1'));
		$this->hiddennorek1->Value=$id;		
		$this->txtEditKodeTransaksi->Text=$result[1]['no_rek1'];	
		$this->txtEditNamaTransaksi->Text=$result[1]['nama_rek1'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennorek1->Value;
            $no_rek1=$this->txtEditKodeTransaksi->Text;
			$nama_transaksi=strtoupper($this->txtEditNamaTransaksi->Text);
			$str = "UPDATE rek1 SET no_rek1='$no_rek1',nama_rek1='$nama_transaksi' WHERE no_rek1=$id";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Transaksi');
		}
	}
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("rek1 WHERE no_rek1='$id'");		
		$this->populateData();		
	}
}

?>