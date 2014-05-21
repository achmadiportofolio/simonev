<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Negara extends MainPageDMaster {
	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->showLokasi=true; 
        $this->showNegara=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {		
            if (!isset($_SESSION['currentPageNegara'])||$_SESSION['currentPageNegara']['page_name']!='m.dmaster.Negara') {
                $_SESSION['currentPageNegara']=array('page_name'=>'m.dmaster.Negara','page_num'=>0);												
			}
			$this->populateData ();
		}
	}
	public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageNegara']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageNegara']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('negara','idnegara');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageNegara']['page_num']=0;}
        $str = "SELECT idnegara,nama_negara FROM negara ORDER BY idnegara ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idnegara','nama_negara'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	
	public function checkNamaNegara ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddNamaNegara'?'add':'edit';
        $nama_negara=$param->Value;
        if ($nama_negara != '') {
            try {
                if ($this->hiddennamanegara->Value != $nama_negara){                                        
                    if ($this->DB->checkRecordIsExist ('nama_negara','negara',$nama_negara)) {
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
			$nama_negara=strtoupper($this->txtAddNamaNegara->Text);
			$str = "INSERT INTO negara (nama_negara) VALUES ('$nama_negara')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Negara');
		}
	}
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$result = $this->dmaster->getList("negara WHERE idnegara='$id'",array('idnegara','nama_negara'));
		$this->hiddenidnegara->Value=$id;		
		$this->hiddennamanegara->Value=$result[1]['nama_negara'];	
		$this->txtEditNamaNegara->Text=$result[1]['nama_negara'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenidnegara->Value;            
			$nama_negara=strtoupper($this->txtEditNamaNegara->Text);
			$str = "UPDATE negara SET nama_negara='$nama_negara' WHERE idnegara=$id";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Negara');
		}
	}
	public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("negara WHERE idnegara='$id'");		
		$this->populateData();		
	}
}

?>