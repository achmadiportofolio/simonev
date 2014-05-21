<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class DT2 extends MainPageDMaster {
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showLokasi=true; 
        $this->showDT2=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageDT2'])||$_SESSION['currentPageDT2']['page_name']!='m.dmaster.DT2') {
                $_SESSION['currentPageDT2']=array('page_name'=>'m.dmaster.DT2','page_num'=>0);												
			}
			$this->populateData ();			
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDT2']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDT2']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('dt1,dt2 WHERE dt2.iddt1=dt1.iddt1','iddt2');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDT2']['page_num']=0;}
        $str = "SELECT iddt2,nama_dt1,nama_dt2 FROM dt1,dt2 WHERE dt2.iddt1=dt1.iddt1 ORDER BY dt2.iddt1,dt2.nama_dt2 ASC LIMIT $offset,$limit";        
		$this->DB->setFieldTable(array('iddt2','nama_dt1','nama_dt2'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();      		
	}
    public function addProcess ($sender,$param) {
		$this->idProcess='add';		        
        $this->cmbAddDTI->DataSource=$this->dmaster->getList('dt1',array('iddt1','nama_dt1'),'nama_dt1',null,4);
        $this->cmbAddDTI->dataBind();
	} 
    public function checkNamaDT2 ($sender,$param) {
		$this->idProcess=$sender->getId()=='checkAddNamaDT2'?'add':'edit';
        $nama_dt2=$param->Value;
        if ($nama_dt2 != '') {
            try {
                if ($this->hiddennamadt2->Value != $nama_dt2){                                        
                    $iddt1=$sender->getId()=='checkAddNamaDT2'?$this->cmbAddDTI->Text:$this->cmbEditDTI->Text;
                    if ($this->DB->checkRecordIsExist ('nama_dt2','dt2',$nama_dt2," AND iddt1=$iddt1")) {
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
            $dt1=$this->cmbAddDTI->Text;
			$nama_dt2 = strtoupper($this->txtAddNamaDT2->Text);
			$str = "INSERT INTO dt2 (iddt2,iddt1,nama_dt2) VALUES (NULL,$dt1,'$nama_dt2')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.DT2');
		}
	}	
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->dmaster->getList("dt2 WHERE iddt2=$id",array('iddt2','iddt1','nama_dt2'));
		$this->hiddeniddt2->Value=$id;
        $this->cmbEditDTI->DataSource=$this->dmaster->getList('dt1',array('iddt1','nama_dt1'),'nama_dt1',null,4);        
		$this->cmbEditDTI->Text=$result[1]['iddt1'];
		$this->cmbEditDTI->dataBind();		
		$this->txtEditNamaDT2->Text=$result[1]['nama_dt2'];		
        $this->hiddennamadt2->Value=$result[1]['nama_dt2'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddeniddt2->Value;
			$nama_dt2 = strtoupper($this->txtEditNamaDT2->Text);
            $iddt1=$this->cmbEditDTI->Text;
			$str = "UPDATE dt2 SET iddt1=$iddt1,nama_dt2='$nama_dt2' WHERE iddt2=$id";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.DT2');
		}
	}
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("dt2 WHERE iddt2=$id");		
		$this->populateData();		
	}	
	
}

?>