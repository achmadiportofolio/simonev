<?php
prado::using ('Application.pages.s.belanja.MainPageBelanja');
class Realisasi extends MainPageBelanja  {	
	public function onLoad ($param) {		
		parent::onLoad ($param);           
        $this->showRealisasi=true;	
		if (!$this->IsPostBack && !$this->IsCallback) {	
            $iduraian=addslashes($this->request['id']);
            if (!isset($_SESSION['currentPageRealisasi'])||$_SESSION['currentPageRealisasi']['page_name']!='s.belanja.Realisasi') {
                $_SESSION['currentPageRealisasi']=array('page_name'=>'s.belanja.Realisasi','page_num'=>0,'dataUraian'=>array(),'viewindex'=>0);												
			}  
			$str = "SELECT p.idproyek,u.iduraian,p.kode_proyek,p.nama_proyek,p.tahun_anggaran,u.rekening,u.nama_uraian,volume,satuan,harga_satuan,nilai,u.idlok,u.ket_lok,u.nama_perusahaan,u.tgl_kontrak,u.tgl_mulai_pelaksanaan,u.tgl_selesai_pelaksanaan FROM uraian u,proyek p WHERE u.idproyek=p.idproyek AND iduraian='$iduraian'";
			$this->DB->setFieldTable(array('idproyek','iduraian','kode_proyek','nama_proyek','tahun_anggaran','rekening','nama_uraian','volume','satuan','harga_satuan','nilai','idlok','ket_lok','nama_perusahaan','tgl_kontrak','nama_perusahaan','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan'));
			$result=$this->DB->getRecord($str);		
            if (isset($result[1])) {             
                $this->RelasiTabPanel->ActiveViewIndex=$_SESSION['currentPageRealisasi']['viewindex'];
    			$result[1]['tgl_kontrak']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_kontrak']);
                $result[1]['tgl_mulai_pelaksanaan']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_mulai_pelaksanaan']);
    			$result[1]['tgl_selesai_pelaksanaan']=$this->TGL->tanggal('l, j F Y',$result[1]['tgl_selesai_pelaksanaan']);
    			$_SESSION['currentPageRealisasi']['dataUraian']=$result[1];	 			
                $this->uraianAnchor->NavigateUrl=$this->Service->constructUrl('s.belanja.Uraian',array('id'=>$result[1]['idproyek']));
                $this->uraianAnchor2->NavigateUrl=$this->Service->constructUrl('s.belanja.Uraian',array('id'=>$result[1]['idproyek']));
                $this->realisasiAnchor->NavigateUrl=$this->Service->constructUrl('s.belanja.Realisasi',array('id'=>$iduraian));
                $this->cmbPhotoBulan->DataSource=$this->TGL->getMonth (1);
                $this->cmbPhotoBulan->dataBind();
    			$this->populateData();
                $this->populatePermasalahan();
                $this->populatePhoto();
            }else {
                unset($_SESSION['currentPageRealisasi']['dataUraian']);                
                $this->idProcess='view';                
            }
        }
	}	
    public function panelViewChanged ($sender,$param) {
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
        switch ($sender->getId()) {
            case 'chkrealisasi' :
                $_SESSION['currentPageRealisasi']['viewindex']=0;
            break;
            case 'chkpermasalahan' :
                $_SESSION['currentPageRealisasi']['viewindex']=1;
            break;
            case 'chkphoto' :
                $_SESSION['currentPageRealisasi']['viewindex']=2;
            break;
        }
        $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));				
    }
    protected function populateData() {
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
		$str="SELECT idpenggunaan,nilai,bulan,tahun,target,realisasi,fisik FROM penggunaan p,uraian u WHERE u.iduraian=p.iduraian AND p.iduraian='$iduraian' ORDER BY bulan ASC";
		$this->DB->setFieldTable(array('idpenggunaan','nilai','bulan','tahun','target','realisasi','fisik'));		
		$r=$this->DB->getRecord($str);
		$finance=$this->finance;
		$bulan=$this->TGL->getMonth(3);		
        $result=array();
        while (list($k,$v)=each($r)) {
            $target=$target+$v['target'];
            $realisasi=$realisasi+$v['realisasi'];
            $dpa=$v['nilai']-$target;			
            $kas=$target-$realisasi;
            $v['bulan']=$bulan[$v['bulan']];
            $v['dpa']=$finance->toRupiah($dpa);
            $v['kas']=$finance->toRupiah($kas);
            $v['realisasi']=$finance->toRupiah($v['realisasi']);
            $v['target']=$finance->toRupiah($v['target']);
            $v['nilai']=$finance->toRupiah($v['nilai']);
            $result[$k]=$v;	
        }
        $this->RepeaterS->DataSource=$result;
        $this->RepeaterS->dataBind();
		
	}        
    public function addProcess ($sender,$param) {
        $this->idProcess='add';
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
        $m_realisasi=$this->kegiatan->getMonthRealisasi($iduraian,'belum'); 			
        $this->cmbAddBulan->DataSource=$m_realisasi;        
        $this->cmbAddBulan->dataBind();
        $totalfisik=$this->kegiatan->getTotalFisik($iduraian);
        $this->fisikSlider->MaxValue=100-$totalfisik;
        $nilai_pagu_uraian=$this->session['currentPageRealisasi']['dataUraian']['nilai'];
        $totaltarget=$this->kegiatan->getTotalTarget($iduraian);
        $this->hiddennilaitarget->Value=$totaltarget;
        $totalrealisasi=$this->kegiatan->getTotalRealisasi($iduraian);
        $this->hiddennilairealisasi->Value=$totalrealisasi;
        $this->hiddendpa->Value=$nilai_pagu_uraian-$totaltarget;        
        $this->hiddenkas->Value=$totaltarget-$totalrealisasi;
        
    }
    public function checkPaguUraian ($sender,$param) {
        $this->idProcess=$sender->getId()==='checkAddTarget'?'add':'edit';
        $nilai_target=$this->finance->toInteger($param->Value);        
        if ($nilai_target != '') {
            try {                
                if ($this->hiddennilaitarget->Value != $nilai_target){                     
                    $nilai_target+=$this->hiddennilaitarget->Value;
                    $nilai_pagu_uraian=$this->session['currentPageRealisasi']['dataUraian']['nilai'];                         
                    if ($nilai_target > $nilai_pagu_uraian) {
                        throw new Exception ("<p class=\"msg error\">Nilai Target jangan lebih dari nilai pagu uraian.</p>");		
                    }
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }
        }			
	}
	public function checkRealisasi ($sender,$param) {
        try {
            $realisasi=$this->finance->toInteger($param->Value);    
            if ($sender->getId()=='checkAddRealisasi') {
                $target=$this->finance->toInteger($this->txtAddTarget->Text);
                $kas=$this->hiddenkas->Value;
                $nilai=$target+$kas;                
                if ($realisasi > $nilai) {
                    $target=$this->finance->toRupiah($target);
                    $kas=$this->finance->toRupiah($kas);
                    throw new Exception ("<p class=\"msg error\">Realisasi jangan lebih dari target ($target) atau kas ($kas).</p>");		
                }
            }else {
                
            }
        }catch (Exception $e) {
            $param->IsValid=false;
            $sender->ErrorMessage=$e->getMessage();
        } 
	}
	public function saveData ($sender,$param) {
		if ($this->IsValid) {			            
            $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
            $ta=$this->session['currentPageRealisasi']['dataUraian']['tahun_anggaran'];
            $bulan=$this->cmbAddBulan->Text;
			$target=$this->finance->toInteger($this->txtAddTarget->Text);
			$realisasi=$this->finance->toInteger($this->txtAddRealisasi->Text);
            $fisik=$this->hiddenAddFisik->getValue();
            $tanggal_realisasi="$ta-$bulan-".date('m');
			$str = 'INSERT INTO penggunaan (idpenggunaan,iduraian,bulan,tahun,target,realisasi,fisik,tanggal_realisasi) VALUES ';
			$str .= "(NULL,'$iduraian','$bulan','$ta','$target','$realisasi','$fisik','$tanggal_realisasi')";
            $this->DB->insertRecord($str);
            $_SESSION['currentPageRealisasi']['viewindex']=0;
            $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));				
		}
	}		
    public function deleteRecord($sender,$param) {
        $idpenggunaan=$this->getDataKeyField($sender,$this->RepeaterS);
        $this->DB->deleteRecord("penggunaan WHERE idpenggunaan='$idpenggunaan'");			
        $_SESSION['currentPageRealisasi']['viewindex']=0;
        $this->populateData();					
    }
    public function populatePermasalahan () {
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
		$str="SELECT idproblem,tanggal,judul FROM masalah_realisasi WHERE iduraian='$iduraian' ORDER BY tanggal DESC";
		$this->DB->setFieldTable(array('idproblem','tanggal','judul'));		
		$r=$this->DB->getRecord($str);
        
        $this->RepeaterPermasalahan->DataSource=$r;
        $this->RepeaterPermasalahan->dataBind();
    }    
    public function addPermasalahan ($sender,$param) {
        $this->idProcess='edit';
    }
    public function savePermasalahan($sender,$param) {
        if ($this->IsValid) {			            
            $permasalahan=addslashes($this->txtPermasalahan->Text);
            $pemecahan=addslashes($this->txtPemecahan->Text);
            $tanggal=date('Y-m-d',$this->cmbTanggal->TimeStamp);
            $judul=addslashes($this->txtJudul->Text);
            $userid=$this->userid;
            $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
            $str = "INSERT INTO masalah_realisasi (idproblem,iduser,iduraian,tanggal,judul,isi,pemecahan) VALUES (NULL,$userid,$iduraian,'$tanggal','$judul','$permasalahan','$pemecahan')";
            $this->DB->insertRecord($str);            
            $_SESSION['currentPageRealisasi']['viewindex']=1;
            $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));				
        }
    }
    public function deletePermasalahan ($sender,$param) {
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
        $idpermasalahan=$this->getDataKeyField($sender,$this->RepeaterPermasalahan);
        $this->DB->deleteRecord("masalah_realisasi WHERE idproblem=$idpermasalahan");
        $_SESSION['currentPageRealisasi']['viewindex']=1;
        $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));	
    }
    public function populatePhoto() {
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
        $str = "SELECT idphoto,bulan,nama_photo,keterangan FROM realisasi_photo WHERE iduraian=$iduraian";
        $this->DB->setFieldTable(array('idphoto','bulan','nama_photo','keterangan'));		
		$r=$this->DB->getRecord($str);
        $this->RepeaterPhoto->DataSource=$r;
        $this->RepeaterPhoto->dataBind();
    }
    public function fileUploaded ($sender,$param) {
        if ($sender->HasFile) {            
            $filename=$sender->FileName;
            $pathname=BASEPATH . "media/realisasi/$filename";
            $sender->saveAs($pathname);
            $this->hiddenfileuploaded->Value="media/realisasi/$filename";
        }
    }
    public function savePhoto ($sender,$param) {
        if ($this->IsValid) {
            $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
            $bulan=$this->cmbPhotoBulan->Text;
            $filename=$this->hiddenfileuploaded->Value;
            $keterangan=  addslashes($this->txtKeterangan->Text);
            $str = "INSERT INTO realisasi_photo (idphoto,iduraian,bulan,nama_photo,keterangan) VALUES (NULL,$iduraian,'$bulan','$filename','$keterangan')";
            $this->DB->insertRecord($str);
            $_SESSION['currentPageRealisasi']['viewindex']=2;
            $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));
        }
    }
    public function deletePhoto ($sender,$param) {
        $_SESSION['currentPageRealisasi']['viewindex']=2;
        $iduraian=$this->session['currentPageRealisasi']['dataUraian']['iduraian'];
        $id=$this->getDataKeyField($sender, $this->RepeaterPhoto);
        $nama_photo=BASEPATH.'/'.$sender->CommandParameter;
        if (unlink($nama_photo)) {
            $this->DB->deleteRecord("realisasi_photo WHERE idphoto=$id");
            $this->kegiatan->redirect('s.belanja.Realisasi',array('id'=>$iduraian));
        }
    }
}

?>