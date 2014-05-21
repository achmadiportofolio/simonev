<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class Program extends MainPageDMaster {	
	public function onLoad ($param) {		
		parent::onLoad ($param);		
        $this->showProgram=true;        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	            
            if (!isset($_SESSION['currentPageProgram'])||$_SESSION['currentPageProgram']['page_name']!='d.dmaster.Program') {
                $_SESSION['currentPageProgram']=array('page_name'=>'d.dmaster.Program','page_num'=>0);												
			}
            $this->toolbarOptionsBulanRealisasi->Enabled=false;
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();
			$this->populateData ();		
		}	
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
        $this->populateData ();
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageProgram']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}    
	protected function populateData () {
        $idunit = $this->idunit;       
        $tahun=$this->session['ta'];
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageProgram']['page_num'];
		$jumlah_baris=$this->DB->getCountRowsOfTable ("program WHERE idunit=$idunit AND tahun=$tahun",'idprogram');		
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageProgram']['page_num']=0;}
        $str = "SELECT p.idprogram,p.kode_program,p.nama_program FROM program p WHERE p.idunit=$idunit AND tahun=$tahun ORDER BY p.kode_program ASC LIMIT $offset,$limit";
		$this->DB->setFieldTable(array('idprogram','kode_program','nama_program'));
		$r=$this->DB->getRecord($str,$offset+1);        
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();		
        
	}	
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
		$this->txtAddIdSatKer->Value=$this->idunit;
        $this->lblAddSatKer->Text=$this->Pengguna->getDataUser('kode_unit').'.';
	}    
	public function checkId ($sender,$param) {
		switch ($sender->getId ()) {
			case 'addKodeProgram' :
				$kode_program= $this->lblAddSatKer->Text.$this->txtAddKodeProgram->Text;
				$idsatKer=$this->txtAddIdSatKer->Value;		
                $tahun=$this->session['ta'];
			break;
			case 'editKodeProgram' :
                $tahun=$this->cmbEditTahun->Text;
				$kode_program= $this->lblEditSatKer->Text.$this->txtEditKodeProgram->Text;
				$idsatKer=$this->txtEditIdSatKer->Value;				
				$kode_program2=$this->txtEditKodeProgram2->Value;
			break;
		}
		if ($kode_program != $kode_program2) {            
			if ($this->DB->checkRecordIsExist ('kode_program','program',$kode_program," AND tahun=$tahun AND idunit=$idsatKer")) {
				$param->IsValid=false;					
			}
		}
	}
	
	public function saveData($sender,$param) {		
		if ($this->Page->IsValid) {			
			$str = 'INSERT INTO program (idprogram,idunit,kode_program,kode_program2,nama_program,tahun) VALUES (';
			$kode_program= $this->lblAddSatKer->Text.$this->txtAddKodeProgram->Text;
			$kode_program = str_replace(' ','',$kode_program);
			$nama_program =  ucwords(addslashes($this->txtAddNamaProgram->Text));
			$tahun=$this->session['ta'];
			$str .= "NULL,'".$this->txtAddIdSatKer->Value."','$kode_program','".$this->txtAddKodeProgram->Text."','$nama_program',$tahun)";
			$this->DB->insertRecord($str);
            $this->redirect('d.dmaster.Program');
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
            $this->redirect('d.dmaster.Program');
		}
	}
    public function copyProcess ($sender,$param) {	
        $this->idProcess='view';    
        $this->createObjKegiatan();        
        $ta=$this->session['ta'];
        $daftar_tahun=$this->kegiatan->removeIdFromArray($this->TGL->getYear(),$ta);
        $this->cmbTahunAnggaran->DataSource=$daftar_tahun;        
        $this->cmbTahunAnggaran->dataBind();
        $this->populatePrograms();
    }
    private function populatePrograms () {
        $idunit = $this->idunit;  
        $ta=$this->session['ta'];
        $str = "SELECT p.idprogram,p.kode_program,p.nama_program FROM program p WHERE p.idunit=$idunit AND tahun=$ta ORDER BY p.kode_program ASC";
		$this->DB->setFieldTable(array('idprogram','kode_program','nama_program'));
		$r=$this->DB->getRecord($str);        
        $this->RepeaterProgramS->DataSource=$r;
        $this->RepeaterProgramS->dataBind();
    }
    public function copyProgram ($sender,$param) {
        $this->idProcess='view';         
        $idunit = $this->idunit;  
        $ta=$this->cmbTahunAnggaran->Text;
        $str = "SELECT kode_program,kode_program2,nama_program FROM program WHERE idunit=$idunit AND tahun=$ta ORDER BY nama_program ASC";
		$this->DB->setFieldTable(array('kode_program','kode_program2','nama_program'));
		$r=$this->DB->getRecord($str);
        if (isset($r[1])) {            
            $countField = count($r);
            $tahun=$this->session['ta'];
            if ($countField <= 1) {
                $kode_program=$r[1]['kode_program'];
                $kode_program2=$r[1]['kode_program2'];
                $nama_program=$r[1]['nama_program'];                
                $values="(NULL,$idunit,'$kode_program','$kode_program2','$nama_program',$tahun)";
            }else {
                $i=0;
                while (list($k,$v)=each($r)){                    
                    $kode_program=$v['kode_program'];
                    $kode_program2=$v['kode_program2'];
                    $nama_program=$v['nama_program'];          
                    if ($countField > $i+1) {
                        $values=$values."(NULL,$idunit,'$kode_program','$kode_program2','$nama_program',$tahun),";
                    }else {
                        $values=$values."(NULL,$idunit,'$kode_program','$kode_program2','$nama_program',$tahun)";
                    }
                    $i++;
                }
            }
            $this->DB->query('BEGIN');            
            if ($this->DB->deleteRecord("program WHERE tahun=$tahun")) {
                $str = "INSERT INTO program (idprogram,idunit,kode_program,kode_program2,nama_program,tahun) VALUES $values";
                $this->DB->insertRecord($str);
                $this->DB->query('COMMIT');
                $this->populatePrograms ();
            }else {
                $this->DB->query('ROLLBACK');
            }            
        }else{
            $this->errormessage->Text="<p class=\"msg error\">Pada Tahun Anggaran $ta belum ada Prograd.</p>";
        }
    }
	public function showModalDelete ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->createObjFinance();
		$this->txtIdProgram->Value=$id;
        $str = "SELECT kode_program,nama_program FROM program WHERE idprogram=$id";
        $this->DB->setFieldTable (array('kode_program','nama_program'));
        $r=$this->DB->getRecord($str);
        $total_kegiatan=$this->DB->getCountRowsOfTable("proyek WHERE idprogram=$id",'idproyek','');
        $jumlah_pagu_each_program=$this->DB->getSumRowsOfTable('nilai_pagu',"proyek WHERE idprogram=$id");
        $total_realisasi=$this->DB->getSumRowsOfTable('realisasi',"v_laporan_a WHERE idprogram='$id'");
        $this->txtKodeProgram->Text=$r[1]['kode_program'];
        $this->txtNamaProgram->Text=$r[1]['nama_program'];
        $this->txtTotalKegiatan->Text=$total_kegiatan;        
        $this->txtTotalPagu->Text=$this->finance->toRupiah ($jumlah_pagu_each_program);
        $this->txtTotalRealisasi->Text=$this->finance->toRupiah ($total_realisasi);
        
        $this->modalDelete->show();
	}
    public function deleteRecord ($sender,$param) {
		$id=$this->txtIdProgram->Value;
		$this->DB->deleteRecord("program WHERE idprogram=$id");		
		$this->modalDelete->hide();        
        $this->redirect('d.dmaster.Program');
	}
}

?>
