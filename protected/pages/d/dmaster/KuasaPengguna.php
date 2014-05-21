<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class KuasaPengguna extends MainPageDMaster {	
	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showPejabat=true;
        $this->showKuasaPengguna=true;
		if (!$this->IsPostBack&&!$this->IsCallback) {			
            if (!isset($_SESSION['currentPageKuasaPengguna'])||$_SESSION['currentPageKuasaPengguna']['page_name']!='d.dmaster.KuasaPengguna') {
                $_SESSION['currentPageKuasaPengguna']=array('page_name'=>'d.dmaster.KuasaPengguna','page_num'=>0);												
			}            
			$this->populateData ();
		}	
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageKuasaPengguna']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    protected function populateData () {
        $idunit = $this->idunit;  
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageKuasaPengguna']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ("kuasa_pengguna WHERE idunit='$idunit'",'nip_kuasa_pengguna');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageKuasaPengguna']['page_num']=0;}
        $str="SELECT kp.nip_kuasa_pengguna,kp.nama_kuasa_pengguna FROM kuasa_pengguna kp,unit u WHERE u.idunit=kp.idunit AND kp.idunit='$idunit' ORDER BY kp.nip_kuasa_pengguna ASC  LIMIT $offset,$limit";			        
		$this->DB->setFieldTable(array('nip_kuasa_pengguna','nama_kuasa_pengguna'));		
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
                    if ($this->DB->checkRecordIsExist('nip_kuasa_pengguna','kuasa_pengguna',$nip," AND idunit=$idunit")) {                                
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
			$str = 'INSERT INTO kuasa_pengguna (nip_kuasa_pengguna,idunit,nama_kuasa_pengguna) VALUES ';
			$str = $str . "('$nip',$idunit,'$nama')";
			$this->DB->insertRecord($str);
            $this->redirect('d.dmaster.KuasaPengguna');			
		}
	}				
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS); 
        $str = "SELECT nip_kuasa_pengguna,nama_kuasa_pengguna FROM kuasa_pengguna WHERE nip_kuasa_pengguna='$id' AND idunit={$this->idunit}";        
		$this->DB->setFieldTable(array('nip_kuasa_pengguna','nama_kuasa_pengguna'));		
        $result=$this->DB->getRecord($str);        
		$this->hiddennip->Value=$result[1]['nip_kuasa_pengguna'];
		$this->txtEditNip->Text=$result[1]['nip_kuasa_pengguna'];		
		$this->txtEditNamaPejabat->Text=$result[1]['nama_kuasa_pengguna'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennip->Value;
			$nip = addslashes(trim($this->txtEditNip->Text));
			$nama = addslashes(trim($this->txtEditNamaPejabat->Text));
			$str = "UPDATE kuasa_pengguna SET nip_kuasa_pengguna='$nip',nama_kuasa_pengguna='$nama' WHERE nip_kuasa_pengguna='$id' AND idunit={$this->idunit}";
			$this->DB->updateRecord($str);
            $this->redirect('d.dmaster.KuasaPengguna');
		}else {
			$this->idProcess = 'edit';
		}
	}
    public function deleteRecord ($sender,$param) {		
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("kuasa_pengguna WHERE nip_kuasa_pengguna='$id' AND idunit={$this->idunit}");
		$this->populateData();
	}
}

?>