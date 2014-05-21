<?php
prado::using ('Application.pages.d.report.MainPageReports');
class JenisPelaksanaan extends MainPageReports {	
	
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showJenisPelaksanaan=true;    
        $this->createObjKegiatan();
        $this->createObjFinance();
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (isset($this->session['currentPageReportJP']['dataKegiatan']['kode'])) {                
                $this->idProcess='view';
                $this->initialization ();
                $this->toolbarOptionsBulanRealisasi->Enabled=false;          
                $this->toolbarOptionsTahunAnggaran->Enabled=false;          
            }else {
                if (!isset($_SESSION['currentPageReportJP'])||$_SESSION['currentPageReportJP']['page_name']!='d.report.JenisPelaksanaan') {
                    $_SESSION['currentPageReportJP']=array('page_name'=>'d.report.JenisPelaksanaan','page_num'=>0,'dataKegiatan'=>array());												
                }            
                $this->populateData ();
                $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
                $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
                $this->toolbarOptionsTahunAnggaran->dataBind();
                $this->toolbarOptionsBulanRealisasi->Enabled=false;          
            }
              
		}	
	}
    public function changeTahunAnggaran ($sender,$param) {
        $this->idProcess='view';
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;        
        $this->populateData();
	}    
    protected function populateData () {   
        $idunit=$this->idunit;
        $ta=$this->session['ta'];
        $str ="SELECT jp,COUNT(iduraian) AS jumlah FROM uraian u,proyek p,program pr WHERE p.idproyek=u.idproyek AND pr.idprogram=p.idprogram AND pr.idunit=$idunit AND status_lelang=1 AND p.tahun_anggaran=$ta GROUP BY jp";
        $this->DB->setFieldTable (array('jp','jumlah'));	        
		$re=$this->DB->getRecord($str);                 
        $data_uraian=array();
        foreach ($re as $k=>$v) {
            $data_uraian[$v['jp']]=$v['jumlah'];
        }
		$r=$this->kegiatan->getJenisPelaksanaan();
        $result=array();
        $no=1;
        foreach ($r as $k=>$v) {
           if ($k!='none')  {
               $total_nilai_pagu=$this->DB->getSumRowsOfTable('nilai',"uraian u,proyek p WHERE p.idproyek=u.idproyek AND jp='$k' AND p.tahun_anggaran=$ta AND status_lelang=1");
               $jumlah_uraian=isset($data_uraian[$k])?$data_uraian[$k]:0;
               $result[$no]=array('no'=>$no,'kode'=>$k,'nama_jenis'=>$v,'jumlah_uraian'=>$jumlah_uraian,'total_nilai_pagu'=>$this->finance->toRupiah($total_nilai_pagu));          
               $no++;
           }
        }        
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	    
	}  	
	public function viewRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);           
        $dataproyek=array('kode'=>$id);            
        $_SESSION['currentPageReportJP']['dataKegiatan']=$dataproyek;
        $this->redirect('d.report.JenisPelaksanaan');        
	}
    private function initialization () {      
        $kode=$this->session['currentPageReportJP']['dataKegiatan']['kode'];
        $nama_jenis=$this->kegiatan->getJenisPelaksanaan($kode);
        $this->labelJP->Text="Berdasarkan Jenis Pelaksanaan secara $nama_jenis";
        switch ($kode) {
            case 'plfisik' :
                $this->populatePL('plfisik');
            break;
            case 'plperencanaan' :
                $this->populatePL('plperencanaan');
            break;
            case 'plpengawasan' :
                $this->populatePL('plpengawasan');
            break;
            case 'plpengadaan' :
                $this->populatePL('plpengadaan');
            break;        
            case 'lelangfisik' :
                $this->populateLL('lelangfisik');
            break;
            case 'lelangperencanaan' :
                $this->populateLL('lelangperencanaan');
            break;
            case 'lelangpengawasan' :
                $this->populateLL('lelangpengawasan');
            break;
            case 'lelangpengadaan' :
                $this->populateLL('lelangpengadaan');
            break;
        }
        
    }
    public function closeView ($sender,$param) {
		unset($_SESSION['currentPageReportJP']);
		$this->redirect('d.report.JenisPelaksanaan');
	}
    private function populatePL($kode) {        
        $idunit=$this->idunit;
        $tahun=$this->session['ta'];        
        $str =  "SELECT u.iduraian,p.nama_proyek ,p.nilai_pagu, u.nama_uraian,u.nilai AS nilai_uraian,hps,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,CONCAT (u.nama_perusahaan,' (',u.nama_direktur,') ') AS penyedia,u.npwp,u.alamat_perusahaan,p.sumber_anggaran,u.idlok,u.ket_lok FROM program pr,proyek p,uraian u WHERE pr.idprogram=p.idprogram AND u.idproyek=p.idproyek AND pr.idunit=$idunit AND p.tahun_anggaran=$tahun AND u.jp='$kode' AND status_lelang=1";
        $this->DB->setFieldTable(array('iduraian','nama_proyek','nama_uraian','nilai_pagu','nilai_uraian','hps','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','penyedia','npwp','alamat_perusahaan','sumber_anggaran','idlok','ket_lok'));
        $result = $this->DB->getRecord($str);	                
        if (isset($result[1])) {
            $content = '<table class="list" style="font-size:9px">';
            $content.= '<thead>';
            $content.= '<tr>';
            $content.= '<th width="10" class="center">NO</th>';				
            $content.= '<th width="300" class="center">KEGIATAN / URAIAN</th>';		            		
            $content.= '<th class="center">LOKASI</th>';				
            $content.= '<th class="center">PAGU DANA</th>';		
            $content.= '<th class="center">HPS (Rp.)</th>';				
            $content.= '<th class="center">NILAI KONTRAK</th>';				
            $content.= '<th class="center">SELISIH</th>';				
            $content.= '<th class="center">TANGGAL KONTRAK</th>';				
            $content.= '<th class="center">TANGGAL PELAKSANAAN</th>';				
            $content.= '<th class="center">NAMA &<br />DIREKTUR <br />PELAKSANA/PENYEDIA</th>';				
            $content.= '<th class="center">NPWP</th>';				
            $content.= '<th class="center">ALAMAT PENYEDIA</th>';				            			
            $content.= '<th class="center">SUMBER ANGGARAN</th>';				        
            $content.= '</tr>';		
            $content.= '<tr>';
            $content.= '<th class="center">1</th>';				
            $content.= '<th class="center">2</th>';		            		
            $content.= '<th class="center">4</th>';				
            $content.= '<th class="center">5</th>';
            $content.= '<th class="center">6</th>';		
            $content.= '<th class="center">7</th>';				
            $content.= '<th class="center">8</th>';				
            $content.= '<th class="center">9</th>';				
            $content.= '<th class="center">10</th>';				
            $content.= '<th class="center">11</th>';				
            $content.= '<th class="center">12</th>';				
            $content.= '<th class="center">13</th>';				
            $content.= '<th class="center">14</th>';				            
            $content.= '</tr>';		
            $content.= '</thead><tbody>';
            $totalNilaiUraian=0;
            $totalNilaiKontrak=0;
            $totalNilaiSelisih=0;
            $totalHPS=0;
            while (list($k,$v)=each($result)) {
                $tempat=$this->kegiatan->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);
                $nilai_uraian=$v['nilai_uraian'];
                $rp_nilai_pagu=$this->finance->toRupiah($nilai_uraian);
                $hps=$v['hps'];
                $rp_nilai_hps=$this->finance->toRupiah($hps);
                $nilai_kontrak=$v['penawaran'];
                $rp_nilai_kontrak=$this->finance->toRupiah($nilai_kontrak);
                $selisih=$nilai_uraian-$nilai_kontrak;
                $rp_nilai_selisih=$this->finance->toRupiah($selisih);
                $tanggal_kontrak=$this->TGL->tanggal('d F Y',$v['tgl_kontrak']);
                $waktupelaksanaan=$this->TGL->tanggal('d F Y',$v['tgl_mulai_pelaksanaan']). ' s.d '.$this->TGL->tanggal('d F Y',$v['tgl_selesai_pelaksanaan']);
                $content.= '<tr>				
                            <td class="center">'.$v['no'].'</td>
                            <td class="left">'.$v['nama_proyek'].' <br /><strong>('.$v['nama_uraian'].')</strong></td>                            
                            <td class="left">'.$tempat.'</td>
                            <td class="right">'.$rp_nilai_pagu.'</td>                            
                            <td class="right">'.$rp_nilai_hps.'</td>                            
                            <td class="right">'.$rp_nilai_kontrak.'</td>						
                            <td class="right">'.$rp_nilai_selisih.'</td>
                            <td class="left">'.$tanggal_kontrak.'</td>
                            <td class="left">'.$waktupelaksanaan.'</td>                            
                            <td class="left">'.$v['penyedia'].'</td>
                            <td class="left">'.$v['npwp'].'</td>
                            <td class="left">'.$v['alamat_perusahaan'].'</td>
                            <td class="center">'.$v['sumber_anggaran'].'</td>														                            													                            													                            
                        </tr>';	
                $totalNilaiUraian+=$nilai_uraian;
                $totalNilaiKontrak+=$nilai_kontrak;
                $totalNilaiSelisih+=$selisih;
                $totalHPS+=$hps;
                
            }
            $rp_nilai_uraian=$this->finance->toRupiah($totalNilaiUraian);
            $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak);
            $rp_nilai_selisih=$this->finance->toRupiah($totalNilaiSelisih);
            $rp_totalHPS=$this->finance->toRupiah($totalHPS);
            $content.= '<tr>				
                            <td class="center" colspan="3">Jumlah Total</td>                           
                            <td class="right">'.$rp_nilai_uraian.'</td>                            
                            <td class="right">'.$rp_totalHPS.'</td>
                            <td class="right">'.$rp_nilai_kontrak.'</td>						
                            <td class="right">'.$rp_nilai_selisih.'</td>
                            <td class="left"></td>                                   
                            <td class="left"></td>
                            <td class="left"></td>
                            <td class="left"></td>
                            <td class="center"></td>	
                            <td class="left"></td>
                        </tr>';	
            $content.= '</tbody></table>';       
            
        }else {
            $content="<p class=\"msg info\">
                        Belum ada kegiatan berdasarkan jenis kegiatan tersebut diatas pada tahun anggaran $tahun.</p>";
        }        
        $this->contentReport->Text=$content;
    }
    private function populateLL($kode) {  
        $idunit=$this->idunit;
        $tahun=$this->session['ta'];        
        $str = "SELECT u.iduraian,p.nama_proyek,p.nilai_pagu,u.nama_uraian,u.nilai AS nilai_uraian,penawaran,u.idlok,u.ket_lok,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,nama_perusahaan,u.nama_direktur,CONCAT (u.alamat_perusahaan,' / ',u.no_telepon) AS alamat_perusahaan,nama_pptk FROM uraian u JOIN proyek p ON (u.idproyek=p.idproyek) JOIN program pr ON (pr.idprogram=p.idprogram) LEFT JOIN pptk ON (p.nip_pptk=pptk.nip_pptk) WHERE pr.idunit=$idunit AND p.tahun_anggaran=$tahun AND u.jp='$kode' AND status_lelang=1";        
        $this->DB->setFieldTable(array('iduraian','nama_proyek','nilai_pagu','nama_uraian','nilai_uraian','penawaran','idlok','ket_lok','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','nama_perusahaan','nama_direktur','alamat_perusahaan','nama_pptk'));
        $result = $this->DB->getRecord($str);	
        if (isset($result[1])) {
            $content = '<table class="list" style="font-size:9px">';
            $content.= '<thead>';
            $content.= '<tr>';
            $content.= '<th width="10" class="center">NO</th>';				
            $content.= '<th width="300" class="center">KEGIATAN / URAIAN</th>';		
            $content.= '<th width="40" class="center">LOKASI KEGIATAN</th>';				            
            $content.= '<th class="center">PAGU DANA</th>';		            	
            $content.= '<th class="center">BELANJA KONSTRUKSI</th>';				
            $content.= '<th class="center">NAMA PPTK</th>';				
            $content.= '<th class="center">NAMA PERUSAHAAN <br />PEMENANG (PT/CV)</th>';				            				
            $content.= '<th class="center">NAMA &<br />DIREKTUR <br />YG DIKUASAKAN</th>';			
            $content.= '<th class="center">ALAMAT PERUSAHAAN / <br />NO TELEPON</th>';
            $content.= '<th class="center">NILAI KONTRAK</th>';				
            $content.= '<th class="center">REALISASI FISIK (%)</th>';				            			            		        
            $content.= '</tr>';		
            $content.= '<tr>';
            $content.= '<th class="center">1</th>';				
            $content.= '<th class="center">2</th>';		            
            $content.= '<th class="center">3</th>';
            $content.= '<th class="center">4</th>';		
            $content.= '<th class="center">5</th>';				
            $content.= '<th class="center">6</th>';				
            $content.= '<th class="center">7</th>';				
            $content.= '<th class="center">8</th>';				
            $content.= '<th class="center">9</th>';				
            $content.= '<th class="center">10</th>';				
            $content.= '<th class="center">11</th>';				            
            $content.= '</tr>';		
            $content.= '</thead><tbody>';
            $totalNilaiPagu=0;
            $totalNilaiUraian=0;
            $totalNilaiKontrak=0;
            $jumlah_uraian=0;
            while (list($k,$v)=each($result)) {                
                $tempat=$this->kegiatan->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);                    
                $nilai_pagu=$v['nilai_pagu'];
                $nilai_uraian=$v['nilai_uraian'];
                $nilai_penawaran=$v['penawaran'];
                $rp_nilai_pagu=$this->finance->toRupiah($nilai_pagu);               
                $rp_uraian=$this->finance->toRupiah($nilai_uraian);                                               
                $rp_penawaran=$this->finance->toRupiah($nilai_penawaran);                                                               
                $totalfisik=$this->DB->getSumRowsOfTable('fisik',"penggunaan WHERE iduraian={$v['iduraian']}");                
                $content.= '<tr>				
                            <td class="center">'.$v['no'].'</td>
                            <td class="left">'.$v['nama_proyek'].' <br /> <strong>['.$v['nama_uraian'].']</strong></td>
                            <td class="left">'.$tempat.'</td>                            
                            <td class="right">'.$rp_nilai_pagu.'</td>                                                        
                            <td class="right">'.$rp_uraian.'</td>						
                            <td class="left">'.$v['nama_pptk'].'</td>
                            <td class="left">'.$v['nama_perusahaan'].'</td>
                            <td class="left">'.$v['nama_direktur'].'</td>                            
                            <td class="left">'.$v['alamat_perusahaan'].'</td>
                            <td class="left">'.$rp_penawaran.'</td>
                            <td class="center">'.$totalfisik.'</td>                            												                            													                            													                            
                        </tr>';	                
                $jumlah_uraian+=1;
                $totalNilaiPagu+= $nilai_pagu;
                $totalNilaiUraian+=$nilai_uraian;
                $totalNilaiKontrak+=$nilai_penawaran;                
            }           
            $rp_total_pagu=$this->finance->toRupiah($totalNilaiPagu);
            $rp_nilai_uraian=$this->finance->toRupiah($totalNilaiUraian);
            $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak);
            
            $content.= '<tr>				
                            <td class="center" colspan="3">Jumlah Total</td>                                                                                   
                            <td class="right">'.$rp_total_pagu.'</td>
                            <td class="right">'.$rp_nilai_uraian.'</td>
                            <td class="right"></td>						
                            <td class="right"></td>
                            <td class="left"></td>                                   
                            <td class="left"></td>                            
                            <td class="right">'.$rp_nilai_kontrak.'</td>	
                            <td class="left"></td>
                        </tr>';	
            $content.= '</tbody></table>';       
            
        }else {
            $content="<p class=\"msg info\">
                        Belum ada kegiatan berdasarkan jenis kegiatan tersebut diatas pada tahun anggaran $tahun.</p>";
        }        
        $this->contentReport->Text=$content;
    }
    public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $this->createObjReport();        
        $this->report->dataKegiatan=$_SESSION['currentPageReportJP']['dataKegiatan'];
        $this->report->dataKegiatan['idunit']=$this->idunit;
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];
        $this->report->dataKegiatan['nama_jenis']=$this->kegiatan->getJenisPelaksanaan($this->report->dataKegiatan['kode']);
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->report->printJenisPelaksanaan($this->linkOutput);
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->report->printJenisPelaksanaan($this->linkOutput);                
            break;
        }        
    }
}

?>