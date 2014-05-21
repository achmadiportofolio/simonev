<?php
prado::using ('Application.pages.m.report.MainPageReports');
class PelaksanaanAnggaran extends MainPageReports {			
	public function onLoad ($param) {		
		parent::onLoad ($param);	
        $this->showPelaksanaanAnggaran=true;
        $this->createObjFinance();
        $this->createObjKegiatan();
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            if (!isset($_SESSION['currentPagePelaksanaanAnggaran'])||$_SESSION['currentPagePelaksanaanAnggaran']['page_name']!='m.report.PelaksanaanAnggaran') {
                $_SESSION['currentPagePelaksanaanAnggaran']=array('page_name'=>'m.report.PelaksanaanAnggaran','page_num'=>0,'listProgram'=>null,'idunitkerja'=>'none');												
            }
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();
            $this->toolbarOptionsBulanRealisasi->Enabled=false;            
            
            $tahun=$this->session['ta'];
            $idunit=$this->idunit;            
            $result=$this->kegiatan->getList("program WHERE idunit=$idunit AND tahun=$tahun", array('idprogram','kode_program','nama_program'),'kode_program',null,2);	            
            $this->listProgram->DataSource=$result;
            $this->listProgram->DataBind();
            
            $listProgram=$_SESSION['currentPagePelaksanaanAnggaran']['listProgram'];
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
        $_SESSION['currentPagePelaksanaanAnggaran']['listProgram']=count($result)>0?$result:null;
        $this->populateData();
	}
    protected function populateData () {		       
       $this->contentReport->Text=$this->printContent();
	}    
	public function printContent () {		
		$ta=$this->session['ta'];
		$no_bulan = $this->session['bulanrealisasi'];
        $str_pagu = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.tahun='$ta'";				
        if (is_array($_SESSION['currentPagePelaksanaanAnggaran']['listProgram'])) {
            $listProgram=$_SESSION['currentPagePelaksanaanAnggaran']['listProgram'];
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
            $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE tahun='$ta'";
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
                            <th width="150" class="center" rowspan="2">KODE <br />PROGRAM & <br />KEGIATAN</th>
                            <th width="350" class="center" rowspan="2">URAIAN</th>
                            <th width="100" class="center" rowspan="2">LOKASI <br />KEGIATAN</th>                            
                            <th class="center" rowspan="2">TARGET<br />KINERJA</th>              
                            <th class="center" rowspan="2">SUMBER<br />DANA</th>                            
                            <th class="center" colspan="4">TRIWULAN</th>                            
                            <th class="center" rowspan="2">JUMLAH</th>		                            										
                        </tr>	
                        <tr>				
                            <th class="center">I</th>
                            <th class="center">II</th>
                            <th class="center">III</th>					
                            <th class="center">IV</th>					
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
                            <th class="center">10 = 6 + 7 + 8 + 9</th>                            
                        </tr>
                    </thead><tbody>';
                
                $totalQ1EachProgram=0;
                $totalQ2EachProgram=0;
                $totalQ3EachProgram=0;
                $totalQ4EachProgram=0;
                $totalAllQ1=0;                
                $totalAllQ2=0;                
                $totalAllQ3=0;
                $totalAllQ4=0;                                
                while (list($k,$v)=each($daftar_program)) {
                    $idprogram=$v['idprogram'];
                    $this->DB->setFieldTable(array('idproyek','kode_proyek','nama_proyek','nilai_pagu','tk_hasil','sumber_anggaran','idlok','ket_lok'));			
                    $str =  "SELECT p.idproyek,p.kode_proyek,p.nama_proyek,p.nilai_pagu,tk_hasil,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE idprogram='$idprogram'";
                    $daftar_kegiatan = $this->DB->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {   
                        $str = "SELECT QUARTER(tanggal_realisasi) AS year_qtr,SUM(realisasi) AS total FROM v_laporan_a WHERE idprogram=$idprogram GROUP BY year_qtr";                                                                        
                        $this->DB->setFieldTable(array('year_qtr','total'));
                        $realisasi=$this->DB->getRecord($str);
                        $totalQ1EachProgram=isset($realisasi[1])?$realisasi[1]['total']:0;
                        $totalQ2EachProgram=isset($realisasi[2])?$realisasi[2]['total']:0;
                        $totalQ3EachProgram=isset($realisasi[3])?$realisasi[3]['total']:0;
                        $totalQ4EachProgram=isset($realisasi[4])?$realisasi[4]['total']:0;
                        $totalQEachProgram=$totalQ1EachProgram+$totalQ2EachProgram+$totalQ3EachProgram+$totalQ4EachProgram;
                        $content.= '<tr>				
                                <td class="left"><strong>'.$v['kode_program'].'</strong></td>
                                <td class="left"><strong>'.$v['nama_program'].'</strong></td>
                                <td class="left">&nbsp;</td>					                                
                                <td class="left">&nbsp;</td>
                                <td class="left">&nbsp;</td>
                                <td class="right"><strong>'.$this->finance->toRupiah($totalQ1EachProgram).'</strong></td>
                                <td class="right"><strong>'.$this->finance->toRupiah($totalQ2EachProgram).'</strong></td>
                                <td class="right"><strong>'.$this->finance->toRupiah($totalQ3EachProgram).'</strong></td>
                                <td class="right"><strong>'.$this->finance->toRupiah($totalQ4EachProgram).'</strong></td>
                                <td class="right"><strong>'.$this->finance->toRupiah($totalQEachProgram).'</strong></td>
                            </tr>';
                        $no=1;                   
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $content.= '<tr>';
                            $content.= '<td class="left">'.$n['kode_proyek'].'</td>';
                            $content.= '<td class="left">'.$n['nama_proyek'].'</td>';
                            $tempat=$this->kegiatan->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $content.= "<td class=\"left\">$tempat</td>";
                            $content.= '<td class="center">'.$n['tk_hasil'].'</td>';
                            $content.= '<td class="center">'.$n['sumber_anggaran'].'</td>';                                                        
                            $str = "SELECT QUARTER(tanggal_realisasi) AS year_qtr,SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek GROUP BY year_qtr";                                                
                            $this->DB->setFieldTable(array('year_qtr','total'));
                            $realisasi=$this->DB->getRecord($str);              
                            $q1=isset($realisasi[1])?$realisasi[1]['total']:0;
                            $totalAllQ1+=$q1;
                            $q2=isset($realisasi[2])?$realisasi[2]['total']:0;
                            $totalAllQ2+=$q2;
                            $q3=isset($realisasi[3])?$realisasi[3]['total']:0;
                            $totalAllQ3+=$q3;
                            $q4=isset($realisasi[4])?$realisasi[4]['total']:0;
                            $totalAllQ4+=$q4;
                            $total_q=$q1+$q2+$q3+$q4;
                            $rp_q1=$this->finance->toRupiah($q1);
                            $rp_q2=$this->finance->toRupiah($q2);
                            $rp_q3=$this->finance->toRupiah($q3);
                            $rp_q4=$this->finance->toRupiah($q4);
                            $rp_total_q=$this->finance->toRupiah($total_q);
                            $content.= "<td class=\"right\">$rp_q1</td>";                     
                            $content.= "<td class=\"right\">$rp_q2</td>";                                             
                            $content.= "<td class=\"right\">$rp_q3</td>";                                             
                            $content.= "<td class=\"right\">$rp_q4</td>";                     
                            $content.= "<td class=\"right\">$rp_total_q</td>";                                                 
                            $content.='</tr>';                                                       
                        }                                      
                    }
                }
                $rp_totalAllQ1=$this->finance->toRupiah($totalAllQ1);
                $rp_totalAllQ2=$this->finance->toRupiah($totalAllQ2);
                $rp_totalAllQ3=$this->finance->toRupiah($totalAllQ3);
                $rp_totalAllQ4=$this->finance->toRupiah($totalAllQ4);                
                $totalAllQ=$totalAllQ1+$totalAllQ2+$totalAllQ3+$totalAllQ4;
                $rp_totalAllQ=$this->finance->toRupiah($totalAllQ);                
                $content.= '<tr>
                        <td colspan="5" class="right"><strong>Jumlah</strong></td>                        
                        <td class="right">'.$rp_totalAllQ1.'</td>                                                   
                        <td class="right">'.$rp_totalAllQ2.'</td>				
                        <td class="right">'.$rp_totalAllQ3.'</td>
                        <td class="right">'.$rp_totalAllQ4.'</td>								                
                        <td class="right">'.$rp_totalAllQ.'</td>							                   
                        </tr>';
                $content.='</tbody></table>';
            }
                
        }
		return $content;
	}
    public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $listProgram=$_SESSION['currentPagePelaksanaanAnggaran']['listProgram'];
        $this->createObjReport();        
        $this->report->dataKegiatan['idunit']='none';
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];
        $this->report->dataKegiatan['userid']='none';
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->report->printPelaksanaanAnggaran($this->linkOutput,$listProgram);
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->report->printPelaksanaanAnggaran($this->linkOutput,$listProgram);                
            break;
        }        
    }	
}
?>
