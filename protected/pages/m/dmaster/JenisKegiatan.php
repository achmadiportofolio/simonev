<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class JenisKegiatan extends MainPageDMaster {	
	
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showJenisKegiatan=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {			
            if (!isset($_SESSION['currentPageJenisKegiatan'])||$_SESSION['currentPageJenisKegiatan']['page_name']!='m.dmaster.JenisKegiatan') {
                $_SESSION['currentPageJenisKegiatan']=array('page_name'=>'m.dmaster.JenisKegiatan','page_num'=>0);												
			}            
			$this->populateData ();
		}	
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageJenisKegiatan']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    protected function populateData () {        
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageJenisKegiatan']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('jeniskegiatan','idjenis_kegiatan');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageJenisKegiatan']['page_num']=0;}
        $str="SELECT idjenis_kegiatan,nama_jenis FROM jeniskegiatan ORDER BY nama_jenis ASC  LIMIT $offset,$limit";			        
		$this->DB->setFieldTable(array('idjenis_kegiatan','nama_jenis'));		
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
    public function checkJenisKegiatan($sender,$param) {						
        $this->idProcess=$sender->getId()=='addJenis'?'add':'edit';
        $jenis=$param->Value;		
        if ($jenis != '') {
            try {   
                if ($this->hiddenjenis->Value!=$jenis) {                   
                    if ($this->DB->checkRecordIsExist('nama_jenis','jeniskegiatan',$jenis)) {                                
                        throw new Exception ("<p class='msg error'>Nama Jenis ($jenis) sudah tidak tersedia silahkan ganti dengan yang lain.</p>");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }						
    }
	protected function saveData () {
		if ($this->Page->IsValid) {            
			$nama = addslashes($this->txtAddJenis->Text);			
			$str = "INSERT INTO jeniskegiatan (nama_jenis) VALUES ('$nama')";			
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.JenisKegiatan');			
		}
	}				
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS); 
        $str = "SELECT nama_jenis FROM jeniskegiatan WHERE idjenis_kegiatan='$id'";        
		$this->DB->setFieldTable(array('nama_jenis'));		
        $result=$this->DB->getRecord($str);
		$this->hiddenjenis->Value=$id;
		$this->txtEditJenis->Text=$result[1]['nama_jenis'];				
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenjenis->Value;
			$nama_jenis = addslashes($this->txtEditJenis->Text);			
			$str = "UPDATE jeniskegiatan SET nama_jenis='$nama_jenis' WHERE idjenis_kegiatan='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.JenisKegiatan');
		}else {
			$this->idProcess = 'edit';
		}
	}
    public function deleteRecord ($sender,$param) {		
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("jeniskegiatan WHERE idjenis_kegiatan='$id'");
		$this->populateData();
	}
}

?>