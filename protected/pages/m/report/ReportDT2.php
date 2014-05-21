<?php
prado::using ('Application.pages.m.report.MainPageReports');
class ReportDT2 extends MainPageReports {
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showLokasi=true; 
        $this->showDT2=true;
        $this->createObjFinance();
        $this->createObjKegiatan();
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            $this->toolbarOptionsBulanRealisasi->Enabled=false;
            if (isset($_SESSION['currentPageReportDT2']['datakegiatan']['iddt2'])) {
                $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
                $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
                $this->toolbarOptionsTahunAnggaran->dataBind();                          
                $this->detailProcess();
            }else {
                if (!isset($_SESSION['currentPageReportDT2'])||$_SESSION['currentPageReportDT2']['page_name']!='m.report.ReportDT2') {
                    $_SESSION['currentPageReportDT2']=array('page_name'=>'m.report.ReportDT2','page_num'=>0,'datakegiatan'=>array());												
                }                            
                $this->toolbarOptionsTahunAnggaran->Enabled=false;
                $this->literalHeader->Text='Laporan Kegiatan Berdasarkan Daerah Tingkat II ';
                $this->populateData ();			
            }
		}
	}    
    public function changeTahunAnggaran ($sender,$param) {	
        if (isset($_SESSION['currentPageReportDT1']['datakegiatan']['iddt1'])) {
            $this->idProcess='view';
            $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
            $this->contentReport->Text=$this->printContent();
        }else {
            $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
            $this->populateData();
        }        
	}
	protected function populateData () {	
        $ta=$this->session['ta'];
        $str = 'SELECT iddt2,nama_dt1,nama_dt2 FROM dt2 LEFT JOIN dt1 ON (dt1.iddt1=dt2.iddt1) ORDER BY dt2.iddt1 ASC';        
		$this->DB->setFieldTable(array('iddt2','nama_dt1','nama_dt2'));
		$r=$this->DB->getRecord($str);    
        $result=array();
        $str = "SELECT idlok,COUNT(idproyek) AS jumlah_proyek,SUM(nilai_pagu) AS nilai_pagu FROM proyek WHERE ket_lok='dt2' AND tahun_anggaran=$ta GROUP BY idlok ORDER BY idlok ASC";
        $this->DB->setFieldTable(array('idlok','jumlah_proyek','nilai_pagu'));
		$kegiatan=$this->DB->getRecord($str);    
        while (list($k,$v)=each($r)) {
            $jumlah_kegiatan=0;
            $nilai_pagu=0;
            foreach ($kegiatan as $lokasi) {                
                if ($lokasi['idlok']==$v['iddt2']) {
                    $jumlah_kegiatan=$lokasi['jumlah_proyek'];
                    $nilai_pagu=$this->finance->toRupiah($lokasi['nilai_pagu']);
                    break;
                }                
            }
            $v['jumlah_kegiatan']=$jumlah_kegiatan;
            $v['nilai_pagu']=$nilai_pagu;
            $result[$k]=$v;
        }        
		$this->RepeaterS->DataSource=$result;
		$this->RepeaterS->dataBind();      		
	}
    public function viewRecord($sender,$param) {
        $iddt2=$this->getDataKeyField($sender, $this->RepeaterS);
        $str = "SELECT iddt2,nama_dt1,nama_dt2 FROM dt2 LEFT JOIN dt1 ON (dt1.iddt1=dt2.iddt1) WHERE iddt2=$iddt2";        
		$this->DB->setFieldTable(array('iddt2','nama_dt1','nama_dt2'));
		$r=$this->DB->getRecord($str);            
        $_SESSION['currentPageReportDT2']['datakegiatan']=$r[1];
        $this->redirect('m.report.ReportDT2');
    }    
    public function detailProcess () {
        $this->idProcess='view';
        $datalokasi=$_SESSION['currentPageReportDT2']['datakegiatan'];        
        $this->literalHeader->Text="Daftar Kegiatan di {$datalokasi['nama_dt2']} - {$datalokasi['nama_dt1']}";
        $this->contentReport->Text=$this->printContent();
    }
    /**
	* digunakan untuk mendapatkan nilai total pagu untuk satu unit
	*
	*/
	private function getTotalNilaiPaguUnit ($idunit,$ta)  {
        $iddt2=$_SESSION['currentPageReportDT2']['datakegiatan']['iddt2'];
		$str = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$ta' AND ket_lok='dt2' AND idlok=$iddt2";				
		$this->DB->setFieldTable (array('total'));
		$r=$this->DB->getRecord($str);
		if (isset($r[1]))
			return $r[1]['total'];
		else
			return 0;
	}	   
    /**
	*
	* total fisik satu proyek, tahun, bulan penggunaan
	*/
	private function getTotalFisik ($idproyek,$tahun) {
		$str = "SELECT SUM(fisik) AS total FROM v_laporan_a WHERE tahun_penggunaan='$tahun' AND idproyek='$idproyek'";				
		$this->DB->setFieldTable (array('total'));
		$r=$this->DB->getRecord($str);
		if (isset($r[1]))
			return $r[1]['total'];
		else
			return 0;
	}
	
	/**
	*
	* jumlah realisasi satu proyek, tahun, bulan penggunaan
	*/
	private function getJumlahFisik ($idproyek,$tahun) {
		$str = "SELECT COUNT(realisasi) AS total FROM v_laporan_a WHERE tahun_penggunaan='$tahun' AND idproyek='$idproyek'";				
		$this->DB->setFieldTable (array('total'));
		$r=$this->DB->getRecord($str);
		if (isset($r[1]))
			return $r[1]['total'];
		else
			return 0;
	} 
	public function printContent () {
        $datalokasi=$_SESSION['currentPageReportDT2']['datakegiatan'];
		$idunit=$this->idunit;
		$ta=$this->session['ta'];
        
        $this->DB->setFieldTable (array('idprogram','kode_program','nama_program'));
		$str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$ta'";
        //daftar program pada unit
        $daftar_program=$this->DB->getRecord($str);		       
         $content="<p class=\"msg info\">
                        Belum ada program pada tahun anggaran $ta.</p>";
        if (isset($daftar_program[1])) {
            //total pagu satu unit
            $totalPaguUnit = $this->getTotalNilaiPaguUnit ($idunit,$ta);
            if ($totalPaguUnit > 0) {
                $content=
                '<table class="list" style="font-size:9px">
                    <thead>       
                        <tr>
                            <th rowspan="4" class="center">NO</th>
                            <th rowspan="4" width="350" class="center">PROGRAM/KEGIATAN</th>
                            <th rowspan="4" class="center">SUMBER<br />DANA</th>
                            <th rowspan="4" class="center">PAGU DANA</th>
                            <th rowspan="4" class="center">BOBOT</th>
                            <th colspan="5" class="center">REALISASI</th>										
                            <th rowspan="2" colspan="2" class="center">SISA ANGGARAN</th>		                            										
                        </tr>	
                        <tr>		
                            <th colspan="2" class="center">FISIK</th>
                            <th colspan="3" class="center">KEUANGAN</th>					                   
                        </tr>
                        <tr>																	
                            <th class="center">% per</th>		
                            <th class="center">% per</th>										
                            <th rowspan="2" class="center">(Rp)</th>
                            <th class="center">% per</th>
                            <th class="center">% per</th>
                            <th rowspan="2" class="center">(Rp)</th>
                            <th rowspan="2" class="center">(%)</th>					
                        </tr>				
                        <tr>																	
                            <th class="center">kegiatan</th>		
                            <th class="center">SPPD</th>										                    
                            <th class="center">kegiatan</th>		
                            <th class="center">SPPD</th>										                    
                        </tr>				
                        <tr>				
                            <th class="center">1</th>
                            <th class="center">2</th>
                            <th class="center">3</th>					
                            <th class="center">4</th>
                            <th class="center">5</th>
                            <th class="center">6</th>					
                            <th class="center">7</th>					
                            <th class="center">8</th>
                            <th class="center">9</th>
                            <th class="center">10</th>
                            <th class="center">11</th>
                            <th class="center">12</th>					                            
                        </tr>
                    </thead><tbody>';
                $no_huruf=ord('a');
                $totalRealisasiKeseluruhan=0;                
                $totalPersenRealisasiPerSPPD='0.00';                
                $totalSisaAnggaran=0;
                $jumlah_kegiatan=0;
                while (list($k,$v)=each($daftar_program)) {
                    $idprogram=$v['idprogram'];
                    $this->DB->setFieldTable(array('idproyek','nama_proyek','nilai_pagu','sumber_anggaran','idlok','ket_lok'));			
                    $str =  "SELECT p.idproyek,p.nama_proyek,p.nilai_pagu,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE idprogram='$idprogram' AND ket_lok='dt2' AND idlok='{$datalokasi['iddt2']}'";
                    $daftar_kegiatan = $this->DB->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {
                        $totalpagueachprogram=0;
                        foreach ($daftar_kegiatan as $eachprogram) {
                            $totalpagueachprogram+=$eachprogram['nilai_pagu'];
                        }
                        $totalpagueachprogram=$this->finance->toRupiah($totalpagueachprogram,'tanpa_rp');
                        $content.= '<tr>				
                                <td class="center"><strong>'.chr($no_huruf).'</strong></td>
                                <td class="left"><strong>'.$v['nama_program'].'</strong></td>
                                <td class="left">&nbsp;</td>					
                                <td class="right"><strong>'.$totalpagueachprogram.'</strong></td>
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>						
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>                                													
                            </tr>';
                        $no=1;                   
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $content.= '<tr>';
                            $content.= '<td class="center">'.$n['no'].'</td>';
                            $content.= '<td class="left">'.$n['nama_proyek'].'</td>';
                            $content.= '<td class="center">'.$n['sumber_anggaran'].'</td>';
                            $nilai_pagu_proyek=$n['nilai_pagu'];					
                            $rp_nilai_pagu_proyek=$this->finance->toRupiah($nilai_pagu_proyek,'tanpa_rp');
                            $content.= "<td class=\"right\">$rp_nilai_pagu_proyek</td>";
                            $persen_bobot=number_format(($nilai_pagu_proyek/$totalPaguUnit)*100,2);
                            $totalPersenBobot+=$persen_bobot;
                            $content.= "<td class=\"center\">$persen_bobot</td>";                        
                            $str = "SELECT SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek AND tahun_penggunaan='$ta'";                                                
                            $this->DB->setFieldTable(array('total'));
                            $realisasi=$this->DB->getRecord($str);                        
                            $persen_fisik='0.00';
                            $persenFisikPerSPPD='0.00';
                            $totalrealisasi=0;                        
                            $persen_realisasi='0.00';
                            $persenRealisasiPerSPPD='0.00';
                            $sisa_anggaran=0;
                            $persen_sisa_anggaran='0.00';
                            if ($realisasi[1]['total'] > 0 ){
                                //fisik
                                $totalFisikSatuProyek=$this->getTotalFisik($idproyek,$ta);
                                $jumlahRealisasiFisikSatuProyek=$this->getJumlahFisik ($idproyek,$ta);    				
                                $persen_fisik=number_format(($totalFisikSatuProyek/$jumlahRealisasiFisikSatuProyek),2);
                                $totalPersenFisik+=$persen_fisik;												
                                $persenFisikPerSPPD=number_format(($persen_fisik/100)*$persen_bobot,2);
                                $totalPersenFisikPerSPPD+=$persenFisikPerSPPD;
                            
                                $totalrealisasi=$realisasi[1]['total'];
                                $totalRealisasiKeseluruhan+=$totalrealisasi;
                                $persen_realisasi=number_format(($totalrealisasi/$nilai_pagu_proyek)*100,2);
                                $totalPersenRealisasi+=$persen_realisasi;
                                $persenRealisasiPerSPPD=number_format(($persen_realisasi/100)*$persen_bobot,2);
                                $totalPersenRealisasiPerSPPD+=$persenRealisasiPerSPPD;
                                
                                $sisa_anggaran=$nilai_pagu_proyek- $totalrealisasi;
                                $persen_sisa_anggaran=number_format(($sisa_anggaran/$totalPaguUnit)*100,2);
                                $totalPersenSisaAnggaran+=$persen_sisa_anggaran;
                                $totalSisaAnggaran+=$sisa_anggaran;
                            }
                            $content.= '<td class="center">'.$persen_fisik.'</td>';
                            $content.= '<td class="center">'.$persenFisikPerSPPD.'</td>';                        
                            $content.= '<td class="right">'.$this->finance->toRupiah($totalrealisasi,'tanpa_rp').'</td>';										
                            $content.= '<td class="center">'.$persen_realisasi.'</td>';
                            $content.= '<td class="center">'.$persenRealisasiPerSPPD.'</td>';                        
                            $content.= '<td class="right">'.$this->finance->toRupiah($sisa_anggaran,'tanpa_rp').'</td>';										
                            $content.= '<td class="center">'.$persen_sisa_anggaran.'</td>';                            
                            $content.='</tr>';
                            $no++;
                            $jumlah_kegiatan++;
                        }
                        $no_huruf++;                        
                    }
                }
                $rp_total_pagu_unit=$this->finance->toRupiah($totalPaguUnit,'tanpa_rp');
                $totalPersenBobot=number_format($totalPersenBobot);
                $rp_total_realisasi_keseluruhan=$this->finance->toRupiah($totalRealisasiKeseluruhan,'tanpa_rp');                                   
                if ($totalPersenRealisasi > 0) 
                    $totalPersenRealisasi=number_format(($totalPersenRealisasi/$jumlah_kegiatan),2);
                if ($totalPersenSisaAnggaran > 0) 
                    $totalPersenSisaAnggaran=number_format(($totalPersenSisaAnggaran/$jumlah_kegiatan),2);
                $totalPersenFisik=number_format($totalPersenFisik/$jumlah_kegiatan,2);
                $totalPersenRealisasiPerSPPD=number_format($totalPersenRealisasiPerSPPD,2);
                $totalPersenFisikPerSPPD=number_format($totalPersenFisikPerSPPD,2);
                $rp_total_sisa_anggaran=$this->finance->toRupiah($totalSisaAnggaran,'tanpa_rp');
                $content.= '<tr>
                        <td colspan="2" class="right"><strong>Jumlah</strong></td>
                        <td class="left"></td>
                        <td class="right">'.$rp_total_pagu_unit.'</td>                           
                        <td class="center">'.$totalPersenBobot.'</td>							
                        <td class="center">'.$totalPersenFisik.'</td>
                        <td class="center">'.$totalPersenFisikPerSPPD.'</td>				
                        <td class="right">'.$rp_total_realisasi_keseluruhan.'</td>
                        <td class="center">'.$totalPersenRealisasi.'</td>								                
                        <td class="center">'.$totalPersenRealisasiPerSPPD.'</td>							
                        <td class="right">'.$rp_total_sisa_anggaran.'</td>										
                        <td class="center">'.$totalPersenSisaAnggaran.'</td>                        
                        </tr>';
                $content.='</tbody></table>';
            }
                
        }
		return $content;
	}
    public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $this->createObjReport();        
        $this->report->dataKegiatan=$_SESSION['currentPageReportDT2']['datakegiatan'];
        $this->report->dataKegiatan['idunit']=$this->idunit;
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];        
        $this->report->dataKegiatan['tipe_lokasi']='dt2';        
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->report->printLokasi($this->linkOutput);
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->report->printLokasi($this->linkOutput);                   
            break;
        }        
    }  
    public function close($sender,$param) {        
        unset($_SESSION['currentPageReportDT2']);
        $this->redirect('m.report.ReportDT2');
    }
}

?>