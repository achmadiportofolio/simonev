<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class UnitKerja extends MainPageDMaster {	
	public function onLoad ($param) {
		parent::onLoad ($param);
        $this->showUnitKerja=true;          
        $this->createObjKegiatan();
		if (!$this->IsPostBack&&!$this->IsCallBack) {		
            if (!isset($_SESSION['currentPageUnitKerja'])||$_SESSION['currentPageUnitKerja']['page_name']!='m.dmaster.UnitKerja') {
                $_SESSION['currentPageUnitKerja']=array('page_name'=>'m.dmaster.UnitKerja','page_num'=>0);												
			}			
			$this->populateData ();
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageUnitKerja']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
	protected function populateData () {
		$this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageUnitKerja']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ('unit','kode_unit');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageUnitKerja']['page_num']=0;}
        $str = "SELECT idunit,kode_unit,nama_bagian,nama_unit FROM unit u,bagian b WHERE b.idbagian=u.idbagian ORDER BY kode_unit ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idunit','nama_bagian','kode_unit','nama_unit'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();
	}
	public function addProcess ($sender,$param) {
		$this->idProcess='add';		
        $this->cmbAddBagian->DataSource=$this->kegiatan->getList('bagian',array('idbagian','kode_bagian','nama_bagian'),'kode_bagian',null,2);  ;
        $this->cmbAddBagian->dataBind();
	}    
	public function cmbBagianChanged ($sender,$param) {
		$this->idProcess='add';		
        $idbagian=$this->cmbAddBagian->Text;        
        if ($idbagian=='none') {
            $this->lblAddKodeBagian->Text='';
        }else {            
            $str = "SELECT kode_bagian FROM bagian WHERE idbagian=$idbagian";
            $this->DB->setFieldTable(array('kode_bagian'));
            $r=$this->DB->getRecord($str);        
            $bagian=$r[1]['kode_bagian'];
            $this->lblAddKodeBagian->Text="$bagian.";
        }
	}
	public function checkKodeUnitKerja ($sender,$param) {
		$this->idProcess=$sender->getId()==='checkAddKodeUnitKerja'?'add':'edit';
        $kode_unit=$param->Value;
        if ($kode_unit != '') {
            try {
                $kode_Bagian=$sender->getId()==='checkAddKodeUnitKerja'?$this->lblAddKodeBagian->Text:$this->lblEditKodeBagian->Text;
                $kode_unit = $kode_Bagian.$kode_unit;
                if ($this->hiddenkodeunit->Value != $kode_unit){                                        
                    if ($this->DB->checkRecordIsExist ('kode_unit','unit',$kode_unit)) {
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
			$nama_UnitKerja=  addslashes(strtoupper($this->txtAddNamaUnitKerja->Text));
            $idbagian=$this->cmbAddBagian->Text;
			$kode_UnitKerja=$this->lblAddKodeBagian->Text.$this->txtAddKodeUnitKerja->Text;
            $kode_unit2=$this->txtAddKodeUnitKerja->Text;
			$str = "INSERT INTO unit (kode_unit,kode_unit2,idbagian,nama_unit) VALUES('$kode_UnitKerja','$kode_unit2',$idbagian,'$nama_UnitKerja')";
			$this->DB->insertRecord($str);
            $this->redirect('m.dmaster.UnitKerja');
		}
	}
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $str = "SELECT kode_unit,kode_unit2,nama_unit FROM unit u WHERE idunit='$id'";
		$this->DB->setFieldTable(array('kode_unit','kode_unit2','nama_unit'));
		$result=$this->DB->getRecord($str);	
        $kode_bagian=substr($result[1]['kode_unit'],0,strlen($result[1]['kode_unit'])-strlen($result[1]['kode_unit2']));
      
		$this->hiddenkodeunit->Value=$result[1]['kode_unit'];		
		$this->lblEditKodeBagian->Text=$kode_bagian;
		$this->txtEditKodeUnitKerja->Text=$result[1]['kode_unit2'];
		$this->txtEditNamaUnitKerja->Text=$result[1]['nama_unit'];
	}	
	
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
            $id=$this->hiddenkodeunit->Value;
			$kode=$this->lblEditKodeBagian->Text.$this->txtEditKodeUnitKerja->Text;			
			$nama_UnitKerja=  addslashes(strtoupper($this->txtEditNamaUnitKerja->Text));
            $kode_unit2=$this->txtEditKodeUnitKerja->Text;
			$str = "UPDATE unit SET kode_unit='$kode',kode_unit2='$kode_unit2',nama_unit='$nama_UnitKerja' WHERE kode_unit='$id'";
			$this->DB->updateRecord($str);
            $this->redirect('m.dmaster.UnitKerja');						
		}
	}
    public function deleteRecord ($sender,$param) {
        $id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("unit WHERE idunit='$id'");		
		$this->redirect('m.dmaster.UnitKerja');		
	}
}

?>