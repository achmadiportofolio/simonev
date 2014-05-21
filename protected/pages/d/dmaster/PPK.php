<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class PPK extends MainPageDMaster {	
	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showPejabat=true;
        $this->showPPK=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {			
            if (!isset($_SESSION['currentPagePPK'])||$_SESSION['currentPagePPK']['page_name']!='d.dmaster.PPK') {
                $_SESSION['currentPagePPK']=array('page_name'=>'d.dmaster.PPK','page_num'=>0);												
			}            
			$this->populateData ();
		}	
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePPK']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    protected function populateData () {
        $idunit = $this->idunit;  
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePPK']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ("ppk WHERE ppk.nip_ppk!='none' AND ppk.idunit='$idunit'",'nip_ppk');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePPK']['page_num']=0;}
        $str="SELECT ppk.nip_ppk,ppk.nama_ppk FROM ppk,unit u WHERE u.idunit=ppk.idunit AND ppk.idunit='$idunit' AND ppk.nip_ppk!='none' ORDER BY ppk.nip_ppk ASC  LIMIT $offset,$limit";			        
		$this->DB->setFieldTable(array('nip_ppk','nama_ppk'));		
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
    public function cekNIP($sender,$param) {						
        $this->idProcess=$sender->getId()=='addNIP'?'add':'edit';
        $nip=$param->Value;		
        if ($nip != '') {
            try {   
                if ($this->hiddennip->Value!=$nip) {
                    $idunit=$this->idunit;
                    if ($this->DB->checkRecordIsExist('nip_ppk','ppk',$nip," AND idunit=$idunit")) {                                
                        $nip=$this->nipFormat($nip);
                        throw new Exception ("<p class='msg error'>NIP ($nip) sudah tidak tersedia silahkan ganti dengan yang lain.</p>");		
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
            $idunit=$this->idunit;
			$nip = addslashes(trim($this->txtAddNip->Text));
			$nama = addslashes(trim($this->txtAddNamaPejabat->Text));
			$str = 'INSERT INTO ppk (nip_ppk,idunit,nama_ppk) VALUES ';
			$str = $str . "('$nip',$idunit,'$nama')";
			$this->DB->insertRecord($str);
            $this->redirect('d.dmaster.PPK');			
		}
	}				
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS); 
        $str = "SELECT nip_ppk,nama_ppk FROM ppk WHERE nip_ppk='$id'";        
		$this->DB->setFieldTable(array('nip_ppk','nama_ppk'));		
        $result=$this->DB->getRecord($str);        
		$this->hiddennip->Value=$result[1]['nip_ppk'];
		$this->txtEditNip->Text=$result[1]['nip_ppk'];		
		$this->txtEditNamaPejabat->Text=$result[1]['nama_ppk'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennip->Value;
			$nip = addslashes(trim($this->txtEditNip->Text));
			$nama = addslashes(trim($this->txtEditNamaPejabat->Text));
			$str = "UPDATE ppk SET nip_ppk='$nip',nama_ppk='$nama' WHERE nip_ppk='$id' AND idunit={$this->idunit}";
			$this->DB->updateRecord($str);
            $this->redirect('d.dmaster.PPK');
		}else {
			$this->idProcess = 'edit';
		}
	}
    public function deleteRecord ($sender,$param) {		
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("ppk WHERE nip_ppk='$id' AND idunit={$this->idunit}");
		$this->populateData();
	}
}

?>