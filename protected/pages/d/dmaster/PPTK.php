<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class PPTK extends MainPageDMaster {	
	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showPejabat=true;
        $this->showPPTK=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {			
            if (!isset($_SESSION['currentPagePPTK'])||$_SESSION['currentPagePPTK']['page_name']!='d.dmaster.PPTK') {
                $_SESSION['currentPagePPTK']=array('page_name'=>'d.dmaster.PPTK','page_num'=>0);												
			}            
			$this->populateData ();
		}	
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePPTK']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    protected function populateData () {
        $idunit = $this->idunit;  
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePPTK']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ("pptk WHERE pptk.nip_pptk!='none' AND pptk.idunit='$idunit'",'nip_pptk');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePPTK']['page_num']=0;}
        $str="SELECT pptk.nip_pptk,pptk.nama_pptk FROM pptk,unit u WHERE u.idunit=pptk.idunit AND pptk.idunit='$idunit' AND pptk.nip_pptk!='none' ORDER BY pptk.nip_pptk ASC  LIMIT $offset,$limit";			        
		$this->DB->setFieldTable(array('nip_pptk','nama_pptk'));		
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
                    if ($this->DB->checkRecordIsExist('nip_pptk','pptk',$nip," AND idunit=$idunit")) {                                
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
			$str = 'INSERT INTO pptk (nip_pptk,idunit,nama_pptk) VALUES ';
			$str = $str . "('$nip',$idunit,'$nama')";
			$this->DB->insertRecord($str);
            $this->redirect('d.dmaster.PPTK');			
		}
	}				
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS); 
        $str = "SELECT nip_pptk,nama_pptk FROM pptk WHERE nip_pptk='$id'";        
		$this->DB->setFieldTable(array('nip_pptk','nama_pptk'));		
        $result=$this->DB->getRecord($str);                
		$this->hiddennip->Value=$result[1]['nip_pptk'];	
		$this->txtEditNip->Text=$result[1]['nip_pptk'];		
		$this->txtEditNamaPejabat->Text=$result[1]['nama_pptk'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennip->Value;
			$nip = addslashes(trim($this->txtEditNip->Text));
			$nama = addslashes(trim($this->txtEditNamaPejabat->Text));
			$str = "UPDATE pptk SET nip_pptk='$nip',nama_pptk='$nama' WHERE nip_pptk='$id' AND idunit={$this->idunit}";
			$this->DB->updateRecord($str);
            $this->redirect('d.dmaster.PPTK');
		}else {
			$this->idProcess = 'edit';
		}
	}
    public function deleteRecord ($sender,$param) {		
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("pptk WHERE nip_pptk='$id' AND idunit={$this->idunit}");
		$this->populateData();
	}
}

?>