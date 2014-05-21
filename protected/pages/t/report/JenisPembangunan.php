<?php
prado::using ('Application.pages.t.report.MainPageReports');
class JenisPembangunan extends MainPageReports {	
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showJenisPembangunan=true;    
        $this->createObjKegiatan();
        $this->createObjFinance();
		if (!$this->IsPostBack&&!$this->IsCallback) {
            if (isset($this->session['currentPageReportJenisPembangunan']['dataKegiatan']['idjenis_pembangunan'])) {                
                $this->idProcess='view';
                $this->initialization ();
                $this->toolbarOptionsBulanRealisasi->Enabled=false;          
                $this->toolbarOptionsTahunAnggaran->Enabled=false;          
            }else {
                if (!isset($_SESSION['currentPageReportJenisPembangunan'])||$_SESSION['currentPageReportJenisPembangunan']['page_name']!='t.report.JenisPembangunan') {
                    $_SESSION['currentPageReportJenisPembangunan']=array('page_name'=>'t.report.JenisPembangunan','page_num'=>0,'dataKegiatan'=>array());												
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
        $ta=$this->session['ta'];
        $str = "SELECT idjenis_pembangunan,nama_jenis FROM jenispembangunan ORDER BY nama_jenis ASC";     
        $this->DB->setFieldTable(array('idjenis_pembangunan','nama_jenis'));
        $r=$this->DB->getRecord($str);
        $result=array();
        while (list($k,$v)=each($r)) {
            $idjenis_pembangunan=$v['idjenis_pembangunan'];
            $v['jumlah_uraian']=$this->DB->getCountRowsOfTable ("uraian WHERE idjenis_pembangunan=$idjenis_pembangunan AND status_lelang=1",'idjenis_pembangunan');	
            $result[$k]=$v;
        }
        $this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();	    
	}  	
	public function viewRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);  
        $r = $this->kegiatan->getList("jenispembangunan WHERE idjenis_pembangunan=$id",array('idjenis_pembangunan','nama_jenis'));      
        $_SESSION['currentPageReportJenisPembangunan']['dataKegiatan']=$r[1];
        $this->redirect('t.report.JenisPembangunan');        
	}
    private function initialization () {              
        $nama_jenis=$_SESSION['currentPageReportJenisPembangunan']['dataKegiatan']['nama_jenis'];
        $this->labelJP->Text="Berdasarkan Jenis Pelaksanaan secara $nama_jenis";        
        $this->populateJP();        
    }
    public function closeView ($sender,$param) {
		unset($_SESSION['currentPageReportJenisPembangunan']);
		$this->redirect('t.report.JenisPembangunan');
	}
    private function populateJP() {                
        $idjenis_pembangunan=$this->session['currentPageReportJenisPembangunan']['dataKegiatan']['idjenis_pembangunan'];
        $tahun=$this->session['ta'];        
        $str =  "SELECT u.iduraian,p.nama_proyek ,p.nilai_pagu, u.nama_uraian,u.nilai AS nilai_uraian,hps,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,CONCAT (u.nama_perusahaan,' (',u.nama_direktur,') ') AS penyedia,u.npwp,u.alamat_perusahaan,p.sumber_anggaran,u.idlok,u.ket_lok FROM proyek p,uraian u WHERE u.idproyek=p.idproyek AND p.tahun_anggaran=$tahun AND u.idjenis_pembangunan=$idjenis_pembangunan AND status_lelang=1";
        $this->DB->setFieldTable(array('iduraian','nama_proyek','nama_uraian','nilai_pagu','nilai_uraian','hps','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','penyedia','npwp','alamat_perusahaan','sumber_anggaran','idlok','ket_lok'));
        $result = $this->DB->getRecord($str);	
        if (isset($result[1])) {
            $content = '<table class="list" style="font-size:9px">';
            $content.= '<thead>';
            $content.= '<tr>';
            $content.= '<th width="10" class="center">NO</th>';				
            $content.= '<th width="300" class="center">PROGRAM / KEGIATAN</th>';		            		
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
            $totalPaguDana=0;
            $totalNilaiKontrak=0;
            $totalNilaiSelisih=0;
            $totalHPS=0;
            while (list($k,$v)=each($result)) {
                $tempat=$this->kegiatan->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);
                $nilai_pagu=$v['nilai_pagu'];
                $rp_nilai_pagu=$this->finance->toRupiah($nilai_pagu,'tanpa_rp');
                $hps=$v['hps'];
                $rp_nilai_hps=$this->finance->toRupiah($hps,'tanpa_rp');
                $nilai_kontrak=$v['penawaran'];
                $rp_nilai_kontrak=$this->finance->toRupiah($nilai_kontrak,'tanpa_rp');
                $selisih=$nilai_pagu-$nilai_kontrak;
                $rp_nilai_selisih=$this->finance->toRupiah($selisih,'tanpa_rp');
                $tanggal_kontrak=$this->TGL->tanggal('d F Y',$v['tgl_kontrak']);
                $waktupelaksanaan=$this->TGL->tanggal('d F Y',$v['tgl_mulai_pelaksanaan']). ' s.d '.$this->TGL->tanggal('d F Y',$v['tgl_selesai_pelaksanaan']);
                $content.= '<tr>				
                            <td class="center">'.$v['no'].'</td>
                            <td class="left">'.$v['nama_uraian'].' <br /><strong>('.$v['nama_proyek'].')</strong></td>                            
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
                $totalPaguDana+=$nilai_pagu;
                $totalNilaiKontrak+=$nilai_kontrak;
                $totalNilaiSelisih+=$selisih;
                $totalHPS+=$hps;
                
            }
            $rp_nilai_pagu=$this->finance->toRupiah($totalPaguDana,'tanpa_rp');
            $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak,'tanpa_rp');
            $rp_nilai_selisih=$this->finance->toRupiah($totalNilaiSelisih,'tanpa_rp');
            $rp_totalHPS=$this->finance->toRupiah($totalHPS,'tanpa_rp');
            $content.= '<tr>				
                            <td class="center" colspan="3">Jumlah Total</td>                           
                            <td class="right">'.$rp_nilai_pagu.'</td>                            
                            <td class="left">'.$rp_totalHPS.'</td>
                            <td class="right">'.$rp_nilai_kontrak.'</td>						
                            <td class="right">'.$rp_nilai_selisih.'</td>
                            <td class="left"></td>                                   
                            <td class="left">'.$v['penyedia'].'</td>
                            <td class="left">'.$v['npwp'].'</td>
                            <td class="left">'.$v['alamat_perusahaan'].'</td>
                            <td class="center">'.$v['sumber_anggaran'].'</td>	
                            <td class="left"></td>
                        </tr>';	
            $content.= '</tbody></table>';       
            
        }else {
            $content="<p class=\"msg info\">
                        Belum ada kegiatan berdasarkan jenis kegiatan tersebut diatas pada tahun anggaran $tahun.</p>";
        }        
        $this->contentReport->Text=$content;
    }    
}

?>