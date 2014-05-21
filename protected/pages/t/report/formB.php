<?php
prado::using ('Application.pages.t.report.MainPageReports');
class formB extends MainPageReports {			
	public function onLoad ($param) {		
		parent::onLoad ($param);	
        $this->showFormB=true;
        $this->createObjFinance();
        $this->createObjKegiatan();
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPageFormB'])||$_SESSION['currentPageFormB']['page_name']!='t.report.formB') {
                $_SESSION['currentPageFormB']=array('page_name'=>'t.report.formB','page_num'=>0,'listProgram'=>null);												
            }
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();
            
            $tahun=$this->session['ta'];
            $idunit=$this->idunit;            
            $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);	            
            $this->listProgram->DataSource=$result;
            $this->listProgram->DataBind();
            
            $listProgram=$_SESSION['currentPageFormB']['listProgram'];
            $items=$this->listProgram->getItems();
            foreach ($items as $item) {
                if (is_array($listProgram)) {
                    foreach ($listProgram as $k=>$v) {
                        if ($item->Value == $k) {
                            $item->setSelected(true);
                            break;
                        }
                    }
                }
            }
            $this->populateData ();            
        }
	}
	public function changeTahunAnggaran ($sender,$param) {	
        $_SESSION['ta']=$this->toolbarOptionsTahunAnggaran->Text;
        $this->populateData ();
	}
    public function changeBulanRealisasi ($sender,$param) {	
        $_SESSION['bulanrealisasi']=$this->toolbarOptionsBulanRealisasi->Text;
        $this->populateData ();
	}
    public function filterRecord ($sender,$param) {	
        $indices=$this->listProgram->SelectedIndices;
        $result=array();
        foreach ($indices as  $index) {
             $item=$this->listProgram->Items[$index];
             $result[$item->Value]=$item->Text;             
        }        
        $_SESSION['currentPageFormB']['listProgram']=count($result)>0?$result:null;
        $this->populateData();
	}
    protected function populateData () {		
       $this->contentReport->Text=$this->printContent();
	}	   
    /**
	*
	* total fisik satu proyek, tahun, bulan penggunaan
	*/
	private function getTotalFisik ($idproyek,$no_bulan,$tahun) {
		$str = "SELECT SUM(fisik) AS total FROM v_laporan_a WHERE bulan_penggunaan <='$no_bulan' AND tahun_penggunaan='$tahun' AND idproyek='$idproyek'";				
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
	private function getJumlahFisik ($idproyek,$no_bulan,$tahun) {
		$str = "SELECT COUNT(realisasi) AS total FROM v_laporan_a WHERE bulan_penggunaan<='$no_bulan' AND tahun_penggunaan='$tahun'  AND idproyek='$idproyek'";				
		$this->DB->setFieldTable (array('total'));
		$r=$this->DB->getRecord($str);
		if (isset($r[1]))
			return $r[1]['total'];
		else
			return 0;
	} 
	public function printContent () {
		$idunit=$this->idunit;
		$ta=$this->session['ta'];
		$no_bulan = $this->session['bulanrealisasi'];
        $str_pagu = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$ta'";				
        if (is_array($_SESSION['currentPageFormB']['listProgram'])) {
            $listProgram=$_SESSION['currentPageFormB']['listProgram'];
            $daftar_program=array();
            $i=1;
            $str_pagu2='';
            $countIDProgram=count($listProgram);
            foreach ($listProgram as $k=>$v) {
                $dataprogram=explode('-',$v);
                $daftar_program[$i]=array('idprogram'=>$k,'kode_program'=>$dataprogram[0],'nama_program'=>$dataprogram[1]);
                if ($countIDProgram > $i) {
                    $str_pagu2 = "$str_pagu2 p.idprogram=$k OR ";
                }else {
                    $str_pagu2 = "$str_pagu2 p.idprogram=$k";
                }
                $i+=1;
            }            
            $str_pagu="$str_pagu AND ($str_pagu2)";
        }else {
            $this->DB->setFieldTable (array('idprogram','kode_program','nama_program'));
            $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$ta'";
            //daftar program pada unit
            $daftar_program=$this->DB->getRecord($str);		                   
        }
        $content="<p class=\"msg info\">
                        Belum ada program pada bulan dan tahun anggaran $no_bulan/$ta.</p>";
        if (isset($daftar_program[1])) {
            //total pagu satu unit
            $this->DB->setFieldTable (array('total'));
            $r=$this->DB->getRecord($str_pagu);           
            $totalPaguUnit = $r[1]['total']>0?$r[1]['total']:0;
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
                            <th rowspan="4" width="100" class="center">LOKASI</th>										
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
                            <th class="center">13</th>
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
                    $str =  "SELECT p.idproyek,p.nama_proyek,p.nilai_pagu,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE idprogram='$idprogram'";
                    $daftar_kegiatan = $this->DB->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {
                        $totalpagueachprogram=0;
                        foreach ($daftar_kegiatan as $eachprogram) {
                            $totalpagueachprogram+=$eachprogram['nilai_pagu'];
                        }
                        $totalpagueachprogram=$this->finance->toRupiah($totalpagueachprogram);
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
                            $str = "SELECT SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek AND bulan_penggunaan <= '$no_bulan' AND tahun_penggunaan='$ta'";                                                
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
                                $totalFisikSatuProyek=$this->getTotalFisik($idproyek,$no_bulan,$ta);
                                $jumlahRealisasiFisikSatuProyek=$this->getJumlahFisik ($idproyek,$no_bulan,$ta);    				
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
                                $persen_sisa_anggaran=number_format(($sisa_anggaran/$nilai_pagu_proyek)*100,2);
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
                            $tempat=$this->kegiatan->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $content.= "<td class=\"right\">$tempat</td>";
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
                        <td class="left"></td>
                        </tr>';
                $content.='</tbody></table>';
            }
                
        }
		return $content;
	}
    public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $this->createObjReport();        
        $this->report->dataKegiatan['idunit']=$this->idunit;
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];
        $this->report->dataKegiatan['bulanrealisasi']=$_SESSION['bulanrealisasi'];
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->report->printFormB($this->linkOutput);
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->report->printFormB($this->linkOutput);                
            break;
        }        
    }	
}
?>