<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class DT1 extends MainPageDMaster {
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showLokasi=true; 
        $this->showDT1=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageDT1'])||$_SESSION['currentPageDT1']['page_name']!='m.dmaster.DT1') {
                $_SESSION['currentPageDT1']=array('page_name'=>'m.dmaster.DT1','page_num'=>0);												
			}
			$this->populateData ();			
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageDT1']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageDT1']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('negara n,dt1 WHERE dt1.idnegara=n.idnegara','iddt1');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageDT1']['page_num']=0;}
        $str = "SELECT iddt1,nama_negara,nama_dt1 FROM negara n,dt1 WHERE dt1.idnegara=n.idnegara ORDER BY dt1.nama_dt1,n.nama_negara ASC LIMIT $offset,$limit";        
		$this->DB->setFieldTable(array('iddt1','nama_negara','nama_dt1'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();      		
	}
    public function addProcess ($sender,$param) {
		$this->idProcess='add';		        
//        $this->cmbAddNegara->DataSource=$this->dmaster->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,4);
//        $this->cmbAddNegara->dataBind();
        $this->cmbAddNegara->DataSource=array(2=>'INDONESIA');
        $this->cmbAddNegara->Text=2;
        $this->cmbAddNegara->dataBind();
	} 
    public function checkNamaDT1 ($sender,$param) {
		$this->idProcess=$sender->getId()=='checkAddNamaDT1'?'add':'edit';
        $nama_dt1=$param->Value;
        if ($nama_dt1 != '') {
            try {
                if ($this->hiddennamadt1->Value != $nama_dt1){                                        
                    $idnegara=$sender->getId()=='checkAddNamaDT1'?$this->cmbAddNegara->Text:$this->cmbEditNegara->Text;
                    if ($this->DB->checkRecordIsExist ('nama_dt1','dt1',$nama_dt1," AND idnegara=$idnegara")) {
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
            $idnegara=$this->cmbAddNegara->Text;
			$nama_dt1 = strtoupper($this->txtAddNamaDT1->Text);
			$str = "INSERT INTO dt1 (iddt1,idnegara,nama_dt1) VALUES (NULL,$idnegara,'$nama_dt1')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.DT1');
		}
	}	
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->dmaster->getList("dt1 WHERE iddt1=$id",array('iddt1','idnegara','nama_dt1'));
		$this->hiddeniddt1->Value=$id;
//        $this->cmbEditNegara->DataSource=$this->dmaster->getList('negara',array('idnegara','nama_negara'),'nama_negara',null,4);        
//		$this->cmbEditNegara->Text=$result[1]['idnegara'];
//		$this->cmbEditNegara->dataBind();		
        $this->cmbEditNegara->DataSource=array(2=>'INDONESIA');       
		$this->cmbEditNegara->Text=2;
		$this->cmbEditNegara->dataBind();		
		$this->txtEditNamaDT1->Text=$result[1]['nama_dt1'];		
        $this->hiddennamadt1->Value=$result[1]['nama_dt1'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddeniddt1->Value;
			$nama_dt1 = strtoupper($this->txtEditNamaDT1->Text);
            $idnegara=$this->cmbEditNegara->Text;
			$str = "UPDATE dt1 SET idnegara=$idnegara,nama_dt1='$nama_dt1' WHERE iddt1=$id";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.DT1');
		}
	}
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("dt1 WHERE iddt1=$id");		
		$this->populateData();		
	}	
	
}

?>