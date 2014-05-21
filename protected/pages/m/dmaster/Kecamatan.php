<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Kecamatan extends MainPageDMaster {
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showLokasi=true; 
        $this->showKecamatan=true;
        $this->createObjKegiatan();
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageKecamatan'])||$_SESSION['currentPageKecamatan']['page_name']!='m.dmaster.Kecamatan') {
                $_SESSION['currentPageKecamatan']=array('page_name'=>'m.dmaster.Kecamatan','page_num'=>0);												
			}
			$this->populateData ();			
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKecamatan']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKecamatan']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('dt2,kecamatan WHERE kecamatan.iddt2=dt2.iddt2','idkecamatan');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageKecamatan']['page_num']=0;}
        $str = "SELECT idkecamatan,nama_dt2,nama_kecamatan FROM dt2,kecamatan WHERE kecamatan.iddt2=dt2.iddt2 ORDER BY kecamatan.iddt2,kecamatan.nama_kecamatan ASC LIMIT $offset,$limit";        
		$this->DB->setFieldTable(array('idkecamatan','nama_dt2','nama_kecamatan'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();      		
	}
    public function addProcess ($sender,$param) {
		$this->idProcess='add';		        
        $this->cmbAddDT1->DataSource=$this->dmaster->getList('dt1',array('iddt1','nama_dt1'),'nama_dt1',null,4);
        $this->cmbAddDT1->dataBind();
	} 
    public function changeDataMaster ($sender,$param) {
		$this->idProcess='add';
        switch ($sender->getId()) {
            case 'cmbAddDT1' :
                $iddt1=$this->cmbAddDT1->Text;
                if ($iddt1!='' && $iddt1 != 'none') {	
                    $dt2=$this->kegiatan->getList("dt2 WHERE iddt1='$iddt1'",array('iddt2','nama_dt2'),'nama_dt2',null,1);                                        
                    if (count($dt2)>1) {
                        $this->cmbAddDTII->DataSource=$dt2;
                        $this->cmbAddDTII->Enabled=true;
                        $this->cmbAddDTII->dataBind();             
                    }else {
                        $this->cmbAddDTII->DataSource=array();
                        $this->cmbAddDTII->Enabled=false;
                        $this->cmbAddDTII->dataBind();                
                    }
                }else {
                    $this->cmbAddDTII->DataSource=array();
                    $this->cmbAddDTII->Enabled=false;
                    $this->cmbAddDTII->dataBind();         
                    $this->txtAddKecamatan->Enabled=false;
                }
            break;
            case 'cmbAddDTII' :                
                $iddt2=$this->cmbAddDTII->Text;
                if ($iddt2!='' && $iddt2 != 'none') {
                    $this->txtAddKecamatan->Enabled=true;
                }else {
                    $this->txtAddKecamatan->Enabled=false;
                }
            break;            
        }
        
    }
    public function checkKecamatan ($sender,$param) {
		$this->idProcess=$sender->getId()=='checkAddKecamatan'?'add':'edit';
        $nama_kecamatan=$param->Value;
        if ($nama_kecamatan != '') {
            try {
                if ($this->hiddennamakecamatan->Value != $nama_kecamatan){                                        
                    $iddt2=$sender->getId()=='checkAddKecamatan'?$this->cmbAddDTII->Text:$this->cmbEditDTII->Text;
                    if ($this->DB->checkRecordIsExist ('nama_kecamatan','kecamatan',$nama_kecamatan," AND iddt2=$iddt2")) {
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
            $dt2=$this->cmbAddDTII->Text;
			$nama_kecamatan = strtoupper($this->txtAddKecamatan->Text);
			$str = "INSERT INTO kecamatan (idkecamatan,iddt2,nama_kecamatan) VALUES (NULL,$dt2,'$nama_kecamatan')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Kecamatan');
		}
	}	
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->dmaster->getList("kecamatan WHERE idkecamatan=$id",array('idkecamatan','iddt2','nama_kecamatan'));
		$this->hiddenidkecamatan->Value=$id;
        $this->cmbEditDTII->DataSource=$this->dmaster->getList('dt2',array('iddt2','nama_dt2'),'nama_dt2',null,4);        
		$this->cmbEditDTII->Text=$result[1]['iddt2'];
		$this->cmbEditDTII->dataBind();		
		$this->txtEditKecamatan->Text=$result[1]['nama_kecamatan'];		
        $this->hiddennamakecamatan->Value=$result[1]['nama_kecamatan'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenidkecamatan->Value;
			$nama_kecamatan = strtoupper($this->txtEditKecamatan->Text);
            $iddt2=$this->cmbEditDTII->Text;
			$str = "UPDATE kecamatan SET iddt2=$iddt2,nama_kecamatan='$nama_kecamatan' WHERE idkecamatan=$id";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Kecamatan');
		}
	}
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("kecamatan WHERE idkecamatan=$id");		
		$this->populateData();		
	}	
	
}

?>