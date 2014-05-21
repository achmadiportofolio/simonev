<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class PenggunaAnggaran extends MainPageDMaster {	
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showPejabat=true;
        $this->showPenggunaAnggaran=true;
        $this->createObjKegiatan();
		if (!$this->IsPostBack&&!$this->IsCallback) {			
            if (!isset($_SESSION['currentPagePenggunaAnggaran'])||$_SESSION['currentPagePenggunaAnggaran']['page_name']!='m.dmaster.PenggunaAnggaran') {
                $_SESSION['currentPagePenggunaAnggaran']=array('page_name'=>'m.dmaster.PenggunaAnggaran','page_num'=>0);												
			}            
			$this->populateData ();
		}	
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePenggunaAnggaran']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}
    protected function populateData () {        
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPagePenggunaAnggaran']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('pengguna_anggaran','nip_pengguna_anggaran');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPagePenggunaAnggaran']['page_num']=0;}
        $str="SELECT pa.nip_pengguna_anggaran,pa.idunit,u.nama_unit,pa.nama_pengguna_anggaran FROM pengguna_anggaran pa LEFT JOIN unit u ON (u.idunit=pa.idunit) ORDER BY pa.nip_pengguna_anggaran ASC  LIMIT $offset,$limit";			        
		$this->DB->setFieldTable(array('nip_pengguna_anggaran','idunit','nama_unit','nama_pengguna_anggaran'));		
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        $this->cmbAddBagian->DataSource=$this->kegiatan->getList('bagian',array('idbagian','kode_bagian','nama_bagian'),'kode_bagian',null,2);  
        $this->cmbAddBagian->dataBind();
	}
    public function changeStruktur ($sender,$param) {			
        switch ($sender->getId()) {
            case 'cmbAddBagian' :
                $this->idProcess='add';	
                $idbagian=$this->cmbAddBagian->Text;  
                $this->cmbAddUnitKerja->DataSource=array();
                $this->cmbAddUnitKerja->Enabled=false;
                $this->cmbAddUnitKerja->dataBind();
                if ($idbagian != 'none' && $idbagian != '') {
                    $result=$this->kegiatan->getList("unit WHERE idbagian=$idbagian",array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);
                    if (count($result)> 1) {
						$this->cmbAddUnitKerja->DataSource=$this->kegiatan->getList("unit WHERE idbagian=$idbagian",array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);  
                        $this->cmbAddUnitKerja->Enabled=true;
                        $this->cmbAddUnitKerja->dataBind();
					}
                }
            break;        
            case 'cmbEditBagian' :
                $this->idProcess='edit';
                $idbagian=$this->cmbEditBagian->Text;  
                $this->cmbEditUnitKerja->DataSource=array();
                $this->cmbEditUnitKerja->Enabled=false;
                $this->cmbEditUnitKerja->dataBind();
                if ($idbagian != 'none' && $idbagian != '') {
                    $result=$this->kegiatan->getList("unit WHERE idbagian=$idbagian",array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);
                    if (count($result)> 1) {
						$this->cmbEditUnitKerja->DataSource=$this->kegiatan->getList("unit WHERE idbagian=$idbagian",array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);  
                        $this->cmbEditUnitKerja->Enabled=true;
                        $this->cmbEditUnitKerja->dataBind();
					}
                }
            break;
        }
	}
    public function cekNIP($sender,$param) {						
        $this->idProcess=$sender->getId()=='addNIP'?'add':'edit';
        $idunit=$sender->getId()=='addNIP'?$this->cmbAddUnitKerja->Text:$this->cmbEditUnitKerja->Text;
        $nip=$param->Value;		
        if ($nip != '') {
            try {   
                if ($this->hiddennip->Value!=$nip) {                    
                    if ($this->DB->checkRecordIsExist('nip_pengguna_anggaran','pengguna_anggaran',$nip," AND idunit=$idunit")) {                                
                        $nip=$this->nipFormat($nip);
                        throw new Exception ("<p class='msg error'>NIP ($nip) sudah tidak tersedia lagi pada unit kerja ini, silahkan ganti dengan yang lain.</p>");		
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
            $idunit=$this->cmbAddUnitKerja->Text;			
            $nip = addslashes(trim($this->txtAddNip->Text));
			$nama = addslashes(trim($this->txtAddNamaPejabat->Text));            
			$str = 'INSERT INTO pengguna_anggaran (nip_pengguna_anggaran,idunit,nama_pengguna_anggaran) VALUES ';
			$str = $str . "('$nip',$idunit,'$nama')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.PenggunaAnggaran');			
		}
	}				
	public function editRecord ($sender,$param) {
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS); 
        $idunit=$sender->CommandParameter;
        $this->hiddenidunitkerja->Value=$idunit;
        $str = "SELECT idbagian,pa.idunit,nip_pengguna_anggaran,nama_pengguna_anggaran FROM pengguna_anggaran pa LEFT JOIN unit u ON (u.idunit=pa.idunit) WHERE nip_pengguna_anggaran='$id' AND pa.idunit=$idunit";        
		$this->DB->setFieldTable(array('idbagian','idunit','nip_pengguna_anggaran','nama_pengguna_anggaran'));		
        $result=$this->DB->getRecord($str);  
        $this->hiddennip->Value=$result[1]['nip_pengguna_anggaran'];	
        $idbagian=$result[1]['idbagian'];
        $this->cmbEditBagian->DataSource=$this->kegiatan->getList('bagian',array('idbagian','kode_bagian','nama_bagian'),'kode_bagian',null,2);  
        $this->cmbEditBagian->Text=$result[1]['idbagian'];	
        $this->cmbEditBagian->dataBind();	
        $this->cmbEditUnitKerja->DataSource=$this->kegiatan->getList("unit WHERE idbagian=$idbagian",array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);  
        $this->cmbEditUnitKerja->Text=$result[1]['idunit'];
        $this->cmbEditUnitKerja->Enabled=true;
        $this->cmbEditUnitKerja->dataBind();
		$this->txtEditNip->Text=$result[1]['nip_pengguna_anggaran'];		
		$this->txtEditNamaPejabat->Text=$result[1]['nama_pengguna_anggaran'];
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddennip->Value;
            $nip = addslashes(trim($this->txtEditNip->Text));
			$nama = addslashes(trim($this->txtEditNamaPejabat->Text));
            $idunit=$this->cmbEditUnitKerja->Text;
            $idunit_old=$this->hiddenidunitkerja->Value;
			$str = "UPDATE pengguna_anggaran SET nip_pengguna_anggaran='$nip',idunit=$idunit,nama_pengguna_anggaran='$nama' WHERE nip_pengguna_anggaran='$id' AND idunit=$idunit_old";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.PenggunaAnggaran');
		}else {
			$this->idProcess = 'edit';
		}
	}
    public function deleteRecord ($sender,$param) {		
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
        $idunit=$sender->CommandParameter;
		$this->DB->deleteRecord("pengguna_anggaran WHERE nip_pengguna_anggaran='$id' AND idunit=$idunit");
		$this->populateData();
	}
}

?>