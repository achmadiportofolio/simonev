<?php
prado::using ('Application.pages.d.report.MainPageReports');
class RealisasiPenerimaan extends MainPageReports {	    
	public function onLoad ($param) {		
		parent::onLoad ($param);
        $this->showFormRealisasi=true;
        $this->createObjFinance();
        $this->createObjKegiatan();
        if (!$this->IsPostBack&&!$this->IsCallBack) {            
            if (!isset($_SESSION['currentPageRealisasiPenerimaan'])||$_SESSION['currentPageRealisasiPenerimaan']['page_name']!='d.report.RealisasiPenerimaan') {
                $_SESSION['currentPageRealisasiPenerimaan']=array('page_name'=>'d.report.RealisasiPenerimaan','page_num'=>0,'dataKegiatan'=>array(),'idprogram'=>'none','search'=>false,'userid'=>'none');												
            }                
            $this->toolbarOptionsTahunAnggaran->DataSource=$this->TGL->getYear();
            $this->toolbarOptionsTahunAnggaran->Text=$this->session['ta'];
            $this->toolbarOptionsTahunAnggaran->dataBind();

            $this->toolbarOptionsBulanRealisasi->DataSource=$this->TGL->getMonth (3);
            $this->toolbarOptionsBulanRealisasi->Text=$this->session['bulanrealisasi'];
            $this->toolbarOptionsBulanRealisasi->dataBind();            
            
            $this->populateData();
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
    protected function populateData ($search=false) {	
        $this->contentReport->Text=$this->printContent();
	}    
    private function getRekeningProyek () {		 
        $str = 'SELECT vr.no_rek1,vr.nama_rek1,vr.no_rek2,vr.nama_rek2,vr.no_rek3,vr.nama_rek3,vr.no_rek4,vr.nama_rek4,vr.no_rek5,vr.nama_rek5 FROM v_rekening vr WHERE no_rek1=4';
        $this->DB->setFieldTable(array('no_rek1','nama_rek1','no_rek2','nama_rek2','no_rek3','nama_rek3','no_rek4','nama_rek4','no_rek5','nama_rek5'));
		$r=$this->DB->getRecord($str);
        $tingkat=array();
		foreach ($r as $v) {			            
			$tingkat[1][$v['no_rek1']]=$v['nama_rek1'];
			$tingkat[2][$v['no_rek2']]=$v['nama_rek2'];
			$tingkat[3][$v['no_rek3']]=$v['nama_rek3'];
			$tingkat[4][$v['no_rek4']]=$v['nama_rek4'];
			$tingkat[5][$v['no_rek5']]=$v['nama_rek5'];				
		}
		return $tingkat;
	}
	public function printContent() {		
		$no_bulan=$this->session['bulanrealisasi'];                
		$tahun=$this->session['ta'];
        if ($no_bulan=='01') {
            $bulan_lalu='';
        }else {
            $tgl = new DateTime ("$tahun-$no_bulan-01",new DateTimeZone('Asia/Jakarta'));
            $tgl->modify('-1 month');
            $bulan_lalu=$tgl->format('m');
        }
        
		$content = '<table class="list" style="font-size:9px">';
        $content.= '<thead>';
		$content.= '<tr class="center">';
		$content.= '<th rowspan="2" colspan="5">KODE <BR>REKENING</th>';				
		$content.= '<th rowspan="2" width="300" class="center">JENIS PENERIMAAN</th>';
		$content.= '<th rowspan="2" width="100" class="center">TARGET PENERIMAAN</th>';				        
		$content.= '<th colspan="3" class="center">REALISASI PENERIMAAN</th>';		
		$content.= '<th rowspan="2" class="center">LEBIH / KURANG<br />PENCAPAIAN<br />TARGET</th>';				
        $content.= '<th rowspan="2" class="center">% PENCAPAIAN <br />TARGET</th>';				
		$content.= '</tr>';	
		
		$content.= '<tr class="center">';
        $content.= '<td class="center">BULAN INI JUMLAH (RP)</td>';		
		$content.= '<td class="center">S/D BULAN LALU JUMLAH (RP)</td>';						        
        $content.= '<td class="center">S/D BULAN INI JUMLAH (RP)</td>';	                
		$content.= '</tr>';
		$content.= '<tr>';
		$content.= '<td colspan="5" class="center">1</td>';				
		$content.= '<td class="center">2</td>';
		$content.= '<td class="center">3</td>';				
		$content.= '<td class="center">4</td>';		
		$content.= '<td class="center">5</td>';	
        $content.= '<td class="center">6</td>';			
        $content.= '<td class="center">7</td>';			                
        $content.= '<td class="center">8</td>';			                
		$content.= '</tr>';
		$content.= '</thead>';        		
        $tingkat = $this->getRekeningProyek();                
        $totalpersenpencapaiantarget='0.00';
        if (isset($tingkat[1])) {      
            $tingkat_1=$tingkat[1];            
            $tingkat_2=$tingkat[2];
            $tingkat_3=$tingkat[3];
            $tingkat_4=$tingkat[4];
            $tingkat_5=$tingkat[5];                        
            while (list($k1,$v1)=each($tingkat_1)) {
                foreach ($tingkat_5 as $k5=>$v5) {
                    $rek1=substr($k5,0,1);
                    if ($rek1 == $k1) {
                        //tingkat i                        
                        $content.= '<tr>';
                        $content.= '<td width="10" class="center">'.$k1.'</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td width="10" class="center">&nbsp;</td>';
                        $content.= '<td class="left">'.$v1.'</td>';
                        $content.= '<td class="right"></td>';										
                        $content.= '<td class="center">&nbsp;</td>';	
                        $content.= '<td class="center">&nbsp;</td>';
                        $content.= '<td class="center">&nbsp;</td>';										
                        $content.= '<td class="center">&nbsp;</td>';										
                        $content.= '<td class="center">&nbsp;</td>';			
                        $content.= '</tr>';

                        //tingkat ii
                        foreach ($tingkat_2 as $k2=>$v2) {
                            $rek2_tampil=array();
                            foreach ($tingkat_5 as $k5_level2=>$v5_level2) {
                                $rek2=substr($k5_level2,0,3);								
                                if ($rek2 == $k2) {
                                    if (!array_key_exists($k2,$rek2_tampil)){															
                                        $rek2_tampil[$rek2]=$v2;
                                    }							
                                }
                            }
                            foreach ($rek2_tampil as $a=>$b) {                                
                                $no_=explode ('.',$a);                                
                                $content.= '<tr>';
                                $content.= '<td class="center">'.$no_[0].'.</td>';
                                $content.= '<td class="center">'.$no_[1].'.</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="left">'.$b.'</td>';
                                $content.= '<td class="right"></td>';														
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';
                                $content.= '<td class="center">&nbsp;</td>';							
                                $content.= '<td class="center">&nbsp;</td>';		
                                $content.= '<td class="right"></td>';	
                                $content.= '</tr>';

                                //tingkat iii
                                foreach ($tingkat_3 as $k3=>$v3) {	
                                    $rek3=substr($k3,0,3);
                                    if ($a==$rek3) {                                        
                                        $no_=explode (".",$k3);                                        
                                        $content.= '<tr>';
                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                        $content.= '<td class="center">&nbsp;</td>';
                                        $content.= '<td class="center">&nbsp;</td>';											
                                        $content.= '<td class="left">'.$v3.'</td>';									
                                        $content.= '<td class="right"></td>';																		
                                        $content.= '<td class="center">&nbsp;</td>';									
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '<td class="right"></td>';	
                                        $content.= '</tr>';

                                        foreach ($tingkat_4 as $k4=>$v4) {
                                            if (ereg ($k3,$k4)) {                                                
                                                $no_=explode (".",$k4);
                                                $content.= '<tr>';
                                                $content.= '<td class="center">'.$no_[0].'.</td>';
                                                $content.= '<td class="center">'.$no_[1].'.</td>';
                                                $content.= '<td class="center">'.$no_[2].'.</td>';
                                                $content.= '<td class="center">'.$no_[3].'.</td>';											
                                                $content.= '<td class="center">&nbsp;</td>';		
                                                $content.= '<td class="left">'.$v4.'</td>';																					
                                                $content.= '<td class="right"></td>';														
                                                $content.= '<td class="right"></td>';							
                                                $content.= '<td class="center">&nbsp;</td>';
                                                $content.= '<td class="right"></td>';																						
                                                $content.= '<td class="right"></td>';																																												
                                                $content.= '<td class="right"></td>';	
                                                $content.= '</tr>';

                                                foreach ($tingkat_5 as $k5=>$v5) {
                                                    if (ereg ($k4,$k5)) {                                                       
                                                        $no_=explode (".",$k5);															                                                        
                                                        $str_rek5 = "SELECT idtarget,target FROM target_penerimaan WHERE no_rek5='$k5' AND tahun=$tahun";
                                                        $this->DB->setFieldTable(array('idtarget','target'));                                                
                                                        $data_rek5=$this->DB->getRecord($str_rek5);
                                                        $target=0;
                                                        $penerimaanbulanini=0;
                                                        $penerimaanbulanlalu=0;
                                                        $penerimaanSampaiBulanINI=0;
                                                        $pencapaiantarget=0;
                                                        $persenpencapaiantarget='0.00';                                                        
                                                        if ($data_rek5[1]) {
                                                            $idtarget=$data_rek5[1]['idtarget'];
                                                            $target=$data_rek5[1]['target'];
                                                            $url=$this->Service->constructUrl('d.report.RealisasiPenerimaanDetails',array('id'=>$idtarget));
                                                            $tetaut = "<a href=\"$url\">$v5</a>";
                                                            $str = "SELECT realisasi FROM realisasi_penerimaan WHERE idtarget=$idtarget";
                                                            $this->DB->setFieldTable(array('realisasi'));
                                                            $penerimaan_bulan_ini=$this->DB->getRecord("$str AND bulan='$no_bulan'");
                                                            $penerimaanbulanini=isset($penerimaan_bulan_ini[1])?$penerimaan_bulan_ini[1]['realisasi']:0;                                                            
                                                            $penerimaanbulanlalu=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<='$bulan_lalu'");                                                             
                                                            $penerimaanSampaiBulanINI=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<='$no_bulan'");
                                                            $pencapaiantarget=$penerimaanSampaiBulanINI-$target;
                                                            $persenpencapaiantarget=$penerimaanSampaiBulanINI>0?number_format(($penerimaanSampaiBulanINI/$target)*100,2):'0.00'; 
                                                        }else {
                                                            $tetaut=$v5;
                                                        }
                                                        $totaltarget+=$target;
                                                        $totalpenerimaanbulanini+=$penerimaanbulanini;
                                                        $totalpenerimaanbulanlalu+=$penerimaanbulanlalu;
                                                        $totalpenerimaanSampaiBulanINI+=$penerimaanSampaiBulanINI;
                                                        $totalpencapaiantarget+=$pencapaiantarget;                                                        
                                                        $totalpersenpencapaiantarget+=$persenpencapaiantarget;
                                                        $content.= '<tr>';
                                                        $content.= '<td class="center">'.$no_[0].'.</td>';
                                                        $content.= '<td class="center">'.$no_[1].'.</td>';
                                                        $content.= '<td class="center">'.$no_[2].'.</td>';
                                                        $content.= '<td class="center">'.$no_[3].'.</td>';															
                                                        $content.= '<td class="center">'.$no_[4].'.</td>';	                                                        
                                                        $content.= '<td class="left">'.$tetaut.'</td>';												
                                                        $content.= '<td class="right">'.$this->finance->toRupiah($target).'</td>';
                                                        $content.= '<td class="right">'.$this->finance->toRupiah($penerimaanbulanini).'</td>';																					
                                                        $content.= '<td class="right">'.$this->finance->toRupiah($penerimaanbulanlalu).'</td>';
                                                        $content.= '<td class="right">'.$this->finance->toRupiah($penerimaanSampaiBulanINI).'</td>';
                                                        $content.= '<td class="right">'.$this->finance->toRupiah($pencapaiantarget).'</td>';
                                                        $content.= '<td class="center">'.$persenpencapaiantarget.'</td>';
                                                        $content.= '</tr>';															
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }						
                        }
                        break;
                    }
                    continue;
                }
            }            
            $content.= '<tr>';
            $content.= '<td colspan="6" class="right"><strong>Jumlah</strong></td>';            
            $content.= '<td class="right">'.$this->finance->toRupiah($totaltarget).'</td>';
            $content.= '<td class="right">'.$this->finance->toRupiah($totalpenerimaanbulanini).'</td>';																					
            $content.= '<td class="right">'.$this->finance->toRupiah($totalpenerimaanbulanlalu).'</td>';
            $content.= '<td class="right">'.$this->finance->toRupiah($totalpenerimaanSampaiBulanINI).'</td>';
            $content.= '<td class="right">'.$this->finance->toRupiah($totalpencapaiantarget).'</td>';
            $content.= '<td class="center">'.$totalpersenpencapaiantarget.'</td>';		                 
            $content.= '</tr></table>';		

            return $content;
        }else {
            $this->btnPrint->Enabled=false;
            return "<p class=\"msg info\">
                        Belum ada Realisasi Fisik dan Keuangan sampai dengan bulan dan tahun anggaran $no_bulan/$tahun.</p>";
        }
	}
	public function printOut ($sender,$param) {   
        $this->idProcess='view';
        $this->createObjReport();        
        $this->report->dataKegiatan=$_SESSION['currentPageRealisasiPenerimaan']['dataKegiatan'];
        $this->report->dataKegiatan['tahun']=$_SESSION['ta'];
        $this->report->dataKegiatan['bulanrealisasi']=$_SESSION['bulanrealisasi'];
        $filetype=$this->cmbTipePrintOut->Text;        		
        switch($filetype) {
            case 'excel2003' :                				
                $this->report->setMode('excel2003');
                $this->printRealisasiPenerimaan();
            break;
            case 'excel2007' :				
                $this->report->setMode('excel2007');                
                $this->printRealisasiPenerimaan();                
            break;
        }        
    }
    public function printRealisasiPenerimaan() {
		$no_bulan=$this->session['bulanrealisasi'];
		$tahun=$this->session['ta'];
        if ($no_bulan=='01') {
            $bulan_lalu='';
        }else {
            $tgl = new DateTime ("$tahun-$no_bulan-01",new DateTimeZone('Asia/Jakarta'));
            $tgl->modify('-1 month');
            $bulan_lalu=$tgl->format('m');
        }
        switch ($this->report->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->report->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->report->rpt->getDefaultStyle()->getFont()->setSize('9');                                    
                $row=1;                
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:L$row");				                
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'TARGET DAN REALISASI PENERIMAAN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:L$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'PENDAPATAN DAERAH PROVINSI KEPULAUAN RIAU TAHUN');
                $row+=1;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:L$row");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row","ANGGARAN $tahun");                
                $styleArray=array( 
								'font' => array('bold' => true,'size'=>'11'),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),								
							);
                $this->report->rpt->getActiveSheet()->getStyle("A1:A$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(3);
                $this->report->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(40);
                $this->report->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(20);
                $this->report->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(20);                
                
                $row+=2;
                $row_akhir=$row+1;                            
                $this->report->rpt->getActiveSheet()->mergeCells("A$row:E$row_akhir");		
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'KODE REKENING');
                
                $row_akhir=$row+1;                                     
                $this->report->rpt->getActiveSheet()->mergeCells("F$row:F$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("F$row",'JENIS PENERIMAAN');                
                
                $row_akhir=$row+1;                                     
                $this->report->rpt->getActiveSheet()->mergeCells("G$row:G$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("G$row",'TARGET PENERIMAAN');                                                             
                      
                $this->report->rpt->getActiveSheet()->mergeCells("H$row:J$row");                
                $this->report->rpt->getActiveSheet()->setCellValue("H$row",'REALISASI PENERIMAAN');
                $row_akhir=$row+1;                                                            
                $this->report->rpt->getActiveSheet()->setCellValue("H$row_akhir",'BULAN INI JUMLAH (RP.)');
                $this->report->rpt->getActiveSheet()->setCellValue("I$row_akhir",'S/D BULAN LALU JUMLAH (RP.)');
                $this->report->rpt->getActiveSheet()->setCellValue("J$row_akhir",'S/D BULAN INI JUMLAH (RP.)');
                
                $row_akhir=$row+1;               
                $this->report->rpt->getActiveSheet()->mergeCells("K$row:K$row_akhir");                
                $this->report->rpt->getActiveSheet()->setCellValue("K$row",'LEBIH / KURANG PENCAPAIAN TARGET');                
                
                $row_akhir=$row+1;                               
                $this->report->rpt->getActiveSheet()->mergeCells("L$row:L$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("L$row",'% PENCAPAIAN TARGET');                
                
                $row_akhir=$row+2;
                $this->report->rpt->getActiveSheet()->mergeCells("A$row_akhir:E$row_akhir");
                $this->report->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');
                $this->report->rpt->getActiveSheet()->setCellValue("F$row_akhir",'2');                
                $this->report->rpt->getActiveSheet()->setCellValue("G$row_akhir",'3');                
                $this->report->rpt->getActiveSheet()->setCellValue("H$row_akhir",'4');
                $this->report->rpt->getActiveSheet()->setCellValue("I$row_akhir",'5');
                $this->report->rpt->getActiveSheet()->setCellValue("J$row_akhir",'6');
                $this->report->rpt->getActiveSheet()->setCellValue("K$row_akhir",'7');
                $this->report->rpt->getActiveSheet()->setCellValue("L$row_akhir",'8');                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:L$row_akhir")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row:L$row_akhir")->getAlignment()->setWrapText(true);
                $this->report->rpt->getActiveSheet()->setTitle ('Target dan Realisasi Penerimaan');               
                
                $tingkat = $this->getRekeningProyek();        
                if (isset($tingkat[1])) {                    
                    $tingkat_1=$tingkat[1];            
                    $tingkat_2=$tingkat[2];
                    $tingkat_3=$tingkat[3];
                    $tingkat_4=$tingkat[4];
                    $tingkat_5=$tingkat[5];
                    $row+=3;               
                    $row_awal=$row;
                    while (list($k1,$v1)=each($tingkat_1)) {
                        foreach ($tingkat_5 as $k5=>$v5) {
                            $rek1=substr($k5,0,1);
                            if ($rek1 == $k1) {
                                //tingkat i                                                                
                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$k1,PHPExcel_Cell_DataType::TYPE_STRING);
                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v1);	                                                                
                                $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                $row+=1;
                                //tingkat ii
                                foreach ($tingkat_2 as $k2=>$v2) {
                                    $rek2_tampil=array();
                                    foreach ($tingkat_5 as $k5_level2=>$v5_level2) {
                                        $rek2=substr($k5_level2,0,3);								
                                        if ($rek2 == $k2) {
                                            if (!array_key_exists($k2,$rek2_tampil)){															
                                                $rek2_tampil[$rek2]=$v2;
                                            }							
                                        }
                                    }
                                    foreach ($rek2_tampil as $a=>$b) {                                        
                                        $no_=explode ('.',$a);                                        
                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);
                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);
                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$b);	                                                                                
                                        $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                        $row+=1;
                                        //tingkat iii
                                        foreach ($tingkat_3 as $k3=>$v3) {	
                                            $rek3=substr($k3,0,3);
                                            if ($a==$rek3) {                                                
                                                $no_=explode (".",$k3);                                                
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v3);                                                                                                
                                                $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                                $row+=1;                                              
                                                foreach ($tingkat_4 as $k4=>$v4) {
                                                    if (ereg ($k3,$k4)) {                                                        																																				                                                        
                                                        $no_=explode (".",$k4);
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                        $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                        $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v4);                                                                                                  
                                                        $this->report->rpt->getActiveSheet()->getStyle("A$row:G$row")->getFont()->setBold(true);
                                                        $row+=1;
                                                        foreach ($tingkat_5 as $k5=>$v5) {
                                                            if (ereg ($k4,$k5)) {
                                                                $no_=explode (".",$k5);															                                                                
                                                                $str_rek5 = "SELECT idtarget,target FROM target_penerimaan WHERE no_rek5='$k5' AND tahun=$tahun";
                                                                $this->DB->setFieldTable(array('idtarget','target'));                                                
                                                                $data_rek5=$this->DB->getRecord($str_rek5);
                                                                $target=0;
                                                                $penerimaanbulanini=0;
                                                                $penerimaanbulanlalu=0;
                                                                $penerimaanSampaiBulanINI=0;
                                                                $pencapaiantarget=0;
                                                                $persenpencapaiantarget='0.00';                                                        
                                                                if ($data_rek5[1]) {
                                                                    $idtarget=$data_rek5[1]['idtarget'];
                                                                    $target=$data_rek5[1]['target'];                                                 
                                                                    $str = "SELECT realisasi FROM realisasi_penerimaan WHERE idtarget=$idtarget";
                                                                    $this->DB->setFieldTable(array('realisasi'));
                                                                    $penerimaan_bulan_ini=$this->DB->getRecord("$str AND bulan='$no_bulan'");
                                                                    $penerimaanbulanini=isset($penerimaan_bulan_ini[1])?$penerimaan_bulan_ini[1]['realisasi']:0;                                                            
                                                                    $penerimaanbulanlalu=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<='$bulan_lalu'");                                                             
                                                                    $penerimaanSampaiBulanINI=$this->DB->getSumRowsOfTable('realisasi',"realisasi_penerimaan WHERE idtarget=$idtarget AND DATE_FORMAT(tanggal_realisasi,'%m')<='$no_bulan'");
                                                                    $pencapaiantarget=$penerimaanSampaiBulanINI-$target;
                                                                    $persenpencapaiantarget=$penerimaanSampaiBulanINI>0?number_format(($penerimaanSampaiBulanINI/$target)*100,2):'0.00'; 
                                                                }
                                                                $totaltarget+=$target;
                                                                $totalpenerimaanbulanini+=$penerimaanbulanini;
                                                                $totalpenerimaanbulanlalu+=$penerimaanbulanlalu;
                                                                $totalpenerimaanSampaiBulanINI+=$penerimaanSampaiBulanINI;
                                                                $totalpencapaiantarget+=$pencapaiantarget;                                                        
                                                                $totalpersenpencapaiantarget+=$persenpencapaiantarget;
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("A$row",$no_[0],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("B$row",$no_[1],PHPExcel_Cell_DataType::TYPE_STRING);								
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("C$row",$no_[2],PHPExcel_Cell_DataType::TYPE_STRING);								                                            
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("D$row",$no_[3],PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("E$row",$no_[4],PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValue("F$row",$v5);                                                                                                                                
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$this->finance->toRupiah($target),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("H$row",$this->finance->toRupiah($penerimaanbulanini),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("I$row",$this->finance->toRupiah($penerimaanbulanlalu),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("J$row",$this->finance->toRupiah($penerimaanSampaiBulanINI),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$this->finance->toRupiah($pencapaiantarget),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $this->report->rpt->getActiveSheet()->setCellValueExplicit("L$row",$this->finance->toRupiah($persenpencapaiantarget),PHPExcel_Cell_DataType::TYPE_STRING);
                                                                $row+=1;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }						
                                }
                                break;
                            }
                            continue;
                        }
                    }
                }
                $row-=1;
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:L$row")->applyFromArray($styleArray);
                $this->report->rpt->getActiveSheet()->getStyle("A$row_awal:L$row")->getAlignment()->setWrapText(true);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("F$row_awal:F$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->report->rpt->getActiveSheet()->getStyle("G$row_awal:L$row")->applyFromArray($styleArray);                

                $this->report->rpt->getActiveSheet()->mergeCells("A$row:F$row");
                $this->report->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("G$row",$this->finance->toRupiah($totaltarget),PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("H$row",$this->finance->toRupiah($totalpenerimaanbulanini),PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("I$row",$this->finance->toRupiah($totalpenerimaanbulanlalu),PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("J$row",$this->finance->toRupiah($totalpenerimaanSampaiBulanINI),PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("K$row",$this->finance->toRupiah($totalpencapaiantarget),PHPExcel_Cell_DataType::TYPE_STRING);
                $this->report->rpt->getActiveSheet()->setCellValueExplicit("L$row",$this->finance->toRupiah($totalpersenpencapaiantarget),PHPExcel_Cell_DataType::TYPE_STRING);
                
                $this->report->rpt->getActiveSheet()->getStyle("A$row:L$row")->getFont()->setBold(true);
                $this->report->printOut('targetdanrealisasi');                
                $this->report->setLink($this->linkOutput,'Target dan Realisasi Pendapatan');
            break;
        }
    }
}
?>

