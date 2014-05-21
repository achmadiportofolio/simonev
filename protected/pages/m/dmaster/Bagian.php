<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class Bagian extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->showBagian=true;                  
		if (!$this->IsPostBack&&!$this->IsCallBack) {		
            if (!isset($_SESSION['currentPageBagian'])||$_SESSION['currentPageBagian']['page_name']!='m.dmaster.Bagian') {
                $_SESSION['currentPageBagian']=array('page_name'=>'m.dmaster.Bagian','page_num'=>0);												
			}			
			$this->populateData ();
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageBagian']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageBagian']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('bagian','kode_bagian');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit <= 0) {$offset=0;$limit=10;$_SESSION['currentPageBagian']['page_num']=0;}
        $str = "SELECT idbagian,kode_bagian,nama_bagian FROM bagian ORDER BY kode_bagian ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idbagian','kode_bagian','nama_bagian'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}	 	
	public function checkKodeBagian ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeBagian'?'add':'edit';
        $kode_bagian=$param->Value;
        if ($kode_bagian != '') {
            try {               
                if ($this->hiddenkodebagian->Value != $kode_bagian){                                        
                    if ($this->DB->checkRecordIsExist ('kode_bagian','bagian',$kode_bagian)) {
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
			$nama_Bagian=  addslashes(strtoupper($this->txtAddNamaBagian->Text));            
			$kode_Bagian=$this->txtAddKodeBagian->Text;
			$str = "INSERT INTO bagian (kode_bagian,nama_bagian) VALUES('$kode_Bagian','$nama_Bagian')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.Bagian');
		}
	}
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $str = "SELECT idbagian,kode_bagian,nama_bagian FROM bagian WHERE idbagian=$id";
        $this->DB->setFieldTable(array('idbagian','kode_bagian','nama_bagian'));
        $result=$this->DB->getRecord($str);		
        $this->hiddenid->Value=$id;
		$this->hiddenkodebagian->Value=$result[1]['kode_bagian'];				
		$this->txtEditKodeBagian->Text=$result[1]['kode_bagian'];
		$this->txtEditNamaBagian->Text=$result[1]['nama_bagian'];
	}	
	
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenid->Value;
			$kode=$this->txtEditKodeBagian->Text;			
			$nama_Bagian=strtoupper($this->txtEditNamaBagian->Text);
			$str = "UPDATE bagian SET kode_bagian='$kode',nama_bagian='$nama_Bagian' WHERE idbagian='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.Bagian');						
		}
	}
    public function deleteRecord ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("bagian WHERE idbagian='$id'");		
		$this->redirect('m.dmaster.Bagian');
	}
}

?>