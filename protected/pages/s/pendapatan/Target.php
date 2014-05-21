<?php
prado::using ('Application.pages.s.pendapatan.MainPagePendapatan');
class Target extends MainPagePendapatan {	
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showTarget=true;                
		if (!$this->IsCallback&&!$this->IsPostback) {				            
            if (!isset($_SESSION['currentPageTarget'])||$_SESSION['currentPageTarget']['page_name']!='s.pendapatan.Target') {
                $_SESSION['currentPageTarget']=array('page_name'=>'s.pendapatan.Target','page_num'=>0,'no_rek4'=>'none','search'=>false);												
			}  
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();            
            $this->toolbarOptionsBulanRealisasi->Enabled=false;             
            $_SESSION['currentPageTarget']['search']=false;
            $_SESSION['currentPageTarget']['no_rek4']='none';
            
            $rekening=$this->kegiatan->getList('rek2 WHERE no_rek1=4',array('no_rek2','nama_rek2'),'no_rek2',null,7);        
            $this->cmbAddKelompok->DataSource=$rekening;
            $this->cmbAddKelompok->dataBind();
        
            $this->populateData();
                                   
		}			
	}
    public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;                
        $this->populateData ($_SESSION['currentPageTarget']['search']);
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageTarget']['page_num']=$param->NewPageIndex;
		$this->populateData();
	}  
    public function changeRekening ($sender,$param) {	
        $_SESSION['currentPageTarget']['no_rek4']='none';
		switch ($sender->getId()) {
			case 'cmbAddTransaksi' :
				$idtransaksi=$this->cmbAddTransaksi->Text;
				$this->disableComponentRekening1 ();
				$this->disableAndEnabled();
				if ($idtransaksi != 'none' || $idtransaksi != '') {
					$result=$this->rekening->getListKelompok($idtransaksi);
					if (count($result)> 1) {
						$this->cmbAddKelompok->DataSource=$result;
						$this->cmbAddKelompok->Enabled=true;
						$this->cmbAddKelompok->dataBind();
					}
				}
			break;			
			case 'cmbAddKelompok' :
				$idkelompok = $this->cmbAddKelompok->Text;
				$this->disableComponentRekening2 ();
				$this->disableAndEnabled();
				if ($idkelompok != 'none' || $idkelompok !='') {
					$result=$this->rekening->getListJenis($idkelompok);
					if (count($result)> 1) {
						$this->cmbAddJenis->DataSource=$result;
						$this->cmbAddJenis->Enabled=true;
						$this->cmbAddJenis->dataBind();
					}
				}
			break;
			case 'cmbAddJenis' :
				$idjenis = $this->cmbAddJenis->Text;
				$this->disableComponentRekening3 ();
				$this->disableAndEnabled();
				if ($idjenis != 'none' || $idjenis != '') {
					$result=$this->rekening->getListObjek($idjenis);
					if (count($result)> 1) {
						$this->cmbAddObjek->DataSource=$result;
						$this->cmbAddObjek->Enabled=true;
						$this->cmbAddObjek->dataBind();
					}
				}
			break;			
			case 'cmbAddObjek' :
				$idobjek = $this->cmbAddObjek->Text;
                if ($idobjek != 'none') {
                    $_SESSION['currentPageTarget']['no_rek4']=$idobjek;
                    $this->populateData();
                    $this->disableAndEnabled(true);
                }else{
                    $this->disableAndEnabled(false);
                }				
			break;			
		}
	}	    
    public function filterRecord($sender,$param) {
        $_SESSION['currentPageTarget']['no_rek4']='none';
        $_SESSION['currentPageTarget']['search']=true;
        $this->populateData($_SESSION['currentPageTarget']['search']);
    }
    protected function populateData($search=false) {		
        $tahun=$_SESSION['ta'];        
        $no_rek4=$_SESSION['currentPageTarget']['no_rek4'];
        $str_filter=$no_rek4=='none'||$no_rek4==''?'':" AND no_rek4='$no_rek4'";
        $str = "SElECT no_rek5,nama_rek5 FROM v_rekening WHERE no_rek1=4$str_filter";
        $str_jumlah = "v_rekening WHERE no_rek1=4$str_filter";
        if ($search) {            
            $kriteria=$this->txtKriteria->Text;            
            if ($this->cmbBerdasarkan->Text=='kode') {
                $str_jumlah = "$str_jumlah AND no_rek5 LIKE '%$kriteria%'";
                $str = "$str AND no_rek5 LIKE '%$kriteria%'";
            }else {
                $str_jumlah = "$str_jumlah AND nama_rek5 LIKE '%$kriteria%'";
                $str = "$str AND nama_rek5 LIKE '%$kriteria%'";
            }                
        }        
        $jumlah_baris=$this->DB->getCountRowsOfTable ($str_jumlah,'no_rek5');
        $this->RepeaterS->VirtualItemCount=$jumlah_baris;
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageTarget']['page_num'];
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageTarget']['page_num']=0;}
        $str = "$str LIMIT $offset,$limit";
        $this->DB->setFieldTable(array('no_rek5','nama_rek5'));
		$r=$this->DB->getRecord($str);
        $result=array();
        $str = "SELECT target FROM target_penerimaan WHERE tahun=$tahun";
        $this->DB->setFieldTable(array('target'));
        while (list($k,$v)=each ($r)) {    
            $no_rek5=$v['no_rek5'];
            $target=$this->DB->getRecord("$str AND no_rek5='$no_rek5'");            
            $v['target']=isset($target[1])?$this->finance->toRupiah($target[1]['target']):0;
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();	
	}	       
	public function saveData ($sender,$param) {
		if ($this->Page->IsValid) {
            $tahun=$this->session['ta'];
            foreach ($this->RepeaterS->Items as $inputan) {
                $item=$inputan->txtTargetPenerimaan->getNamingContainer();
                $no_rek5=$this->RepeaterS->DataKeys[$item->getItemIndex()];
                $target=$this->finance->toInteger($inputan->txtTargetPenerimaan->Text);
                $this->DB->deleteRecord("target_penerimaan WHERE no_rek5='$no_rek5' AND tahun=$tahun");
                $this->DB->insertRecord("INSERT INTO target_penerimaan (idtarget,no_rek5,target,tahun) VALUES (NULL,'$no_rek5',$target,$tahun)");                
            }
            $this->redirect('s.pendapatan.Target');
        }
	}		
	private function disableAndEnabled ($flag=false) {
		if ($flag) {						
				
		}else {					
            
		}
	}
	
	private function disableComponentRekening1 () {		
		$this->cmbAddKelompok->DataSource=array();
		$this->cmbAddKelompok->Enabled=false;
		$this->cmbAddKelompok->dataBind();
					
		$this->cmbAddJenis->DataSource=array();
		$this->cmbAddJenis->Enabled=false;
		$this->cmbAddJenis->dataBind();	
					
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();	
	
	}
	
	private function disableComponentRekening2 () {	
		$this->cmbAddJenis->DataSource=array();
		$this->cmbAddJenis->Enabled=false;
		$this->cmbAddJenis->dataBind();	
					
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();	
	}
	private function disableComponentRekening3 () {								
		$this->cmbAddObjek->DataSource=array();
		$this->cmbAddObjek->Enabled=false;
		$this->cmbAddObjek->dataBind();			
	}   
}
?>