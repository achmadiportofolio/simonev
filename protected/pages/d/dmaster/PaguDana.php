<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class PaguDana extends MainPageDMaster {	
	public function onLoad ($param) {		
		parent::onLoad ($param);		
        $this->showPaguDana=true;   
        $this->createObjFinance();
		if (!$this->IsPostBack&&!$this->IsCallBack) {	            
            if (!isset($_SESSION['currentPagePaguDana'])||$_SESSION['currentPagePaguDana']['page_name']!='d.dmaster.PaguDana') {
                $_SESSION['currentPagePaguDana']=array('page_name'=>'d.dmaster.PaguDana','page_num'=>0);												
			}            
			$this->populateData ();		
		}	
	}   
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPagePaguDana']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
    public function itemCreated($sender,$param){
        $item=$param->Item;
        if($item->ItemType==='EditItem') {                           
           
           $item->ColumnNilaiPagu->TextBox->Attributes->OnKeyUp='formatangka(this,false)'; 
            
           $item->EditColumn->UpdateButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->UpdateButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->EditColumn->CancelButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->CancelButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->DeleteColumn->Button->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->DeleteColumn->Button->ClientSide->OnComplete="$('loadingbar').hide()";           
           
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')  {                        
            
           $item->EditColumn->EditButton->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->EditColumn->EditButton->ClientSide->OnComplete="$('loadingbar').hide()";
           
           $item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'Hapus nilai pagu pada tahun yang dipilih ?\')) return false;';
           $item->DeleteColumn->Button->ClientSide->OnPreDispatch="$('loadingbar').show()";
           $item->DeleteColumn->Button->ClientSide->OnComplete="$('loadingbar').hide()";
        }        
    }
	protected function populateData () {
        $idunit=$this->idunit;
        $str = "SELECT idpagudana,nilai_pagu,tahun FROM pagudana WHERE idunit=$idunit ORDER BY tahun DESC";
		$this->DB->setFieldTable(array('idpagudana','nilai_pagu','tahun'));
		$r=$this->DB->getRecord($str);        
        $result=array();
        while (list($k,$v)=each($r)) {
            $v['nilai_pagu']=$this->finance->toRupiah($v['nilai_pagu']);
            $result[$k]=$v;
        }
		$this->GridS->DataSource=$result;
		$this->GridS->dataBind();		        
	}     
    public function editItem($sender,$param) {                   
        $this->GridS->EditItemIndex=$param->Item->ItemIndex;
        $this->populateData ();        
    }
    public function cancelItem($sender,$param) {                
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();        
    }
    public function saveItem($sender,$param) {                
        $item=$param->Item;
        $id=$this->GridS->DataKeys[$item->ItemIndex];                
        $nilai_pagu=$this->finance->toInteger($item->ColumnNilaiPagu->TextBox->Text);                    
        $str = "UPDATE pagudana SET nilai_pagu=$nilai_pagu WHERE idpagudana=$id";        
        $this->DB->updateRecord($str);
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();        
    }
    public function deleteItem($sender,$param) {                
        $id=$this->GridS->DataKeys[$param->Item->ItemIndex];        
        $this->DB->deleteRecord("pagudana WHERE idpagudana=$id");
        $this->GridS->EditItemIndex=-1;
        $this->populateData ();        
    }
	public function checkDanaPagu ($sender,$param) {
        $tahun=$this->lblAddTahun->Text;       
        if ($this->DB->checkRecordIsExist('tahun','pagudana',$tahun)) {
            $param->IsValid=false;
            $sender->ErrorMessage="Nilai Pagu untuk tahun ($tahun) telah ada.";
        }
	}	
	public function saveData($sender,$param) {		
		if ($this->Page->IsValid) {	
            $tahun=$this->lblAddTahun->Text;            
            $idunit = $this->idunit;   
            $nilai_pagu=$this->finance->toInteger($this->txtAddNilaiPagu->Text); 
			$str = "INSERT INTO pagudana (idpagudana,idunit,nilai_pagu,tahun) VALUES (NULL,$idunit,$nilai_pagu,$tahun)";						
			$this->DB->insertRecord($str);
            $this->redirect('d.dmaster.PaguDana');
		}
	}
		
	public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$idprogram=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->txtIdProgram->Value=$idprogram;
        $str = "SELECT idprogram,idunit,kode_program2,nama_program,tahun FROM program p WHERE idprogram='$idprogram'";
        $this->DB->setFieldTable(array('idprogram','idunit','kode_program2','nama_program','tahun'));	
        $r=$this->DB->getRecord($str);    
		$result = $r[1];        
		$this->txtEditIdSatKer->Value=$result['idunit'];
		$this->lblEditSatKer->Text=$this->Pengguna->getDataUser('kode_unit').'.';
		$this->txtEditKodeProgram->Text=$result['kode_program2'];
		$this->txtEditKodeProgram2->Value=$this->lblEditSatKer->Text.$result['kode_program2'];
		$this->txtEditNamaProgram->Text=$result['nama_program'];		
        $this->cmbEditTahun->DataSource=$this->TGL->getYear();
		$this->cmbEditTahun->Text=$result['tahun'];
		$this->cmbEditTahun->dataBind();
	}
	public function updateData($sender,$param) {
		if ($this->Page->IsValid) {
			$idprogram=$this->txtIdProgram->Value;
			$nama_program=ucwords(addslashes($this->txtEditNamaProgram->Text));
			$kode_program= $this->lblEditSatKer->Text.$this->txtEditKodeProgram->Text;
			$kode_program = str_replace(' ','',$kode_program);
			$str = "UPDATE program SET idunit='".$this->txtEditIdSatKer->Value."',kode_program='$kode_program',kode_program2='".$this->txtEditKodeProgram->Text."',nama_program='$nama_program',tahun='".$this->cmbEditTahun->Text."' WHERE idprogram='$idprogram'";	
			$this->DB->updateRecord($str);
            $this->redirect('d.dmaster.PaguDana');
		}
	}   
    public function deleteRecord ($sender,$param) {
		$id=$this->txtIdProgram->Value;
		$this->DB->deleteRecord("program WHERE idprogram=$id");		
		$this->modalDelete->hide();        
        $this->redirect('d.dmaster.PaguDana');
	}
}

?>
