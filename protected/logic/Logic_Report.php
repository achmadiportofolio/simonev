<?php
prado::using ('Application.Logic.Logic_Kegiatan');
class Logic_Report extends Logic_Kegiatan {	
    /**
	* mode dari driver
	*
	*/
	private $driver;
	/**
	* object dari driver2 report misalnya PHPExcel, TCPDF, dll.
	*
	*/
	public $rpt;	
    /**
	* object setup;	
	*/
	public $setup;	
    /**
	* object tanggal;	
	*/
    public $tgl;	
    /**
	* object keuangan;	
	*/
    public $finance;	
	/**
	* Exported Dir
	*
	*/
	private $exportedDir;	
	/**
	* posisi row sekarang
	*
	*/
	public $currentRow=1;	
	/**
	* first row;
	*
	*/
	public $firstRow;		
	public function __construct ($db) {
		parent::__construct ($db);	
        $this->setup = $this->getLogic ('Setup');
		$this->tgl = $this->getLogic ('Penanggalan');
        $this->finance = $this->getLogic ('Finance');
	}		
    /**
	*
	* set mode driver
	*/
	public function setMode ($driver) {
		$this->driver = $driver;
		$path = dirname($this->getPath()).'/';								
		$host=$this->setup->getAddress().'/';				
		switch ($driver) {
            case 'excel2003' :								
                $phpexcel=BASEPATH.'/protected/lib/excel/';
                define ('PHPEXCEL_ROOT',$phpexcel);
                set_include_path(get_include_path() . PATH_SEPARATOR . $phpexcel);
                
                require_once ('PHPExcel.php');                
				$this->rpt=new PHPExcel();
                $this->exportedDir['excel_path']=$host.'exported/excel/';
				$this->exportedDir['full_path']=$path.'exported/excel/';
			break;
			case 'excel2007' :							
                //phpexcel
                $phpexcel=BASEPATH.'/protected/lib/excel/';
                define ('PHPEXCEL_ROOT',$phpexcel);
                set_include_path(get_include_path() . PATH_SEPARATOR . $phpexcel);
                
                require_once ('PHPExcel.php');
				$this->rpt=new PHPExcel();
				$this->exportedDir['excel_path']=$host.'exported/excel/';
				$this->exportedDir['full_path']=$path.'exported/excel/';
			break;
			case 'pdf' :
				prado::using ('System.3rdParty.Tcpdf.config.lang.eng');				
				$this->rpt=$this->Application->getModule ('pdf');			
				$this->rpt->setCreator ('Biro Pembangunan');
				$this->rpt->setAuthor ('Biro Pembangunan Provinsi Kepulaua Riau');
				$this->rpt->setPrintHeader(false);
				$this->rpt->setPrintFooter(false);				
				$this->exportedDir['pdf_path']=$host.'exported/pdf/';	
				$this->exportedDir['full_path']=$path.'exported/pdf/';
			break;			
		}
	}
    /**
     * digunakan untuk mendapatkan driver saat ini
     */
	public function getDriver () {
        return $this->driver;
    }
    /**
	* set header logo;
	*
	*/
	public function setHeaderLogo () {
		$headerLogo = dirname($this->getPath()).'/resources/logo.png';
		switch ($this->driver) {
            case 'excel2003' :
                //drawing
				$drawing = new PHPExcel_Worksheet_Drawing();		
				$drawing->setName('Logo');
				$drawing->setDescription('Logo');			
				
				$drawing->setPath($headerLogo);
				$drawing->setHeight(90);
				$drawing->setCoordinates('A'.$this->currentRow);
				$drawing->setOffsetX(90);
				$drawing->setRotation(25);
				$drawing->getShadow()->setVisible(true);
				$drawing->getShadow()->setDirection(45);
				$drawing->setWorksheet($this->rpt->getActiveSheet());
            break;
			case 'excel2007' :
				//drawing
				$drawing = new PHPExcel_Worksheet_Drawing();		
				$drawing->setName('Logo');
				$drawing->setDescription('Logo');			
				
				$drawing->setPath($headerLogo);
				$drawing->setHeight(90);
				$drawing->setCoordinates('A'.$this->currentRow);
				$drawing->setOffsetX(10);
				$drawing->setRotation(0);
				$drawing->getShadow()->setVisible(true);
				$drawing->getShadow()->setDirection(45);
				$drawing->setWorksheet($this->rpt->getActiveSheet());
			break;
			case 'pdf' :									
				$this->rpt->Image($headerLogo,10,6,27,27,'JPG');
			break;
		}		
	}
    /**
	* digunakan untuk mencetak header 
	*
	*/
	public function setHeader ($endColumn=null,$alignment=null,$columnHeader='C') {			
		switch ($this->driver) {
			case 'excel2003' :
			case 'excel2007' :	
                //cetak logo
                $this->setHeaderLogo();				
				$row=1;
				$this->rpt->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
				$this->rpt->getActiveSheet()->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$this->rpt->getActiveSheet()->setCellValue($columnHeader.$row,'PEMERINTAH KABUPATEN BINTAN');
				
				$row+=1;
				$this->rpt->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
				$this->rpt->getActiveSheet()->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$this->rpt->getActiveSheet()->setCellValue($columnHeader.$row,'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH');
				
				$row+=1;
				$this->rpt->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
				$this->rpt->getActiveSheet()->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$this->rpt->getActiveSheet()->setCellValue($columnHeader.$row,'Jl. Sudirman No. 17 Bintan');
				
				$row+=1;
				$this->rpt->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
				$this->rpt->getActiveSheet()->mergeCells ($columnHeader.$row.':'.$endColumn.$row);
				$this->rpt->getActiveSheet()->setCellValue($columnHeader.$row,'');
								
				$this->rpt->getActiveSheet()->getStyle($columnHeader.($row-3))->getFont()->setSize('10');
				$this->rpt->getActiveSheet()->getStyle($columnHeader.($row-2))->getFont()->setSize('12');	
				$this->rpt->getActiveSheet()->getStyle($columnHeader.($row-1))->getFont()->setSize('10');				
				$this->rpt->getActiveSheet()->getStyle($columnHeader.$row)->getFont()->setSize('10');
				
				
				$this->rpt->getActiveSheet()->duplicateStyleArray(array(
												'font' => array('bold' => true),
												'alignment' => array('horizontal'=>$alignment,
														'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER)					   	
												),
												$columnHeader.$this->currentRow.':'.$columnHeader.$row
											);				
				$this->currentRow=$row;
			break;
			case 'pdf' :
                //cetak logo
                $this->setHeaderLogo($height,$offsetx);
                
				$this->rpt->SetFont ('helvetica','B',16);
				$this->rpt->setXY(15,5);
				$this->rpt->Cell (0,5,'PEMERINTAH KABUPATEN BINTAN',0,0,'C');				
				$this->rpt->setXY(15,11);
				$this->rpt->Cell (0,5,'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH',0,0,'C');
				
				$this->rpt->SetFont ('helvetica','B',12);
				$this->rpt->setXY(15,17);
				$this->rpt->Cell (0,3,'Jl. Sudirman No. 17 Bintan',0,0,'C');
				$this->rpt->setXY(15,23);
				$this->rpt->Cell (0,3,'Telp. (0771) 318533, 318566 Fax. (0771) 318588',0,0,'C');
                $this->rpt->setXY(15,29);
				$this->rpt->Cell (0,3,'Website: www.kepriprov.go.id',0,0,'C');
                $this->rpt->setXY(6,30);
                $this->rpt->Cell (0,3,'','B',0,'C');
				$this->currentRow=30;
			break;            
		}		
	}	
    /**
	* digunakan untuk mencetak laporan
	*
	*/
	public function printOut ($filename) {	
		$filename_to_write =$filename.'_'.date('Y_m_d_H_m_s');	
// 		$filename_to_write =$filename.'_';		//uncoment this line, if you in debug process        
		switch ($this->driver) {
			case 'excel2003' :
                //$writer=new PHPExcel_Writer_Excel5($this->rpt);								
                $writer=PHPExcel_IOFactory::createWriter($this->rpt, 'Excel5');
				$filename_to_write = $filename_to_write . '.xls';
				$writer->save ($this->exportedDir['full_path'].$filename_to_write);		
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['excel_path'].=$filename_to_write;		
            break;
			case 'excel2007' :
				$writer=PHPExcel_IOFactory::createWriter($this->rpt, 'Excel2007');
				$filename_to_write = $filename_to_write . '.xlsx';
				$writer->save ($this->exportedDir['full_path'].$filename_to_write);		
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['excel_path'].=$filename_to_write;		
			break;
			case 'pdf' :
				$filename_to_write=$filename_to_write.'.pdf';
				$this->rpt->output ($this->exportedDir['full_path'].$filename_to_write,'F');
				$this->exportedDir['filename']=$filename;
				$this->exportedDir['pdf_path'].=$filename_to_write;		
			break;			
		}
	}    
    /**
	* digunakan untuk mendapatkan link ke sebuah file hasil dari export	
	* @param obj_out object 
	* @param text in override text result
	*/
	public function setLink ($obj_out,$text='') {
		$filename=$text==''?$this->exportedDir['filename']:$text;		        
		switch ($this->driver) {
			case 'excel2003' :
                $obj_out->Text = "$filename.xls";
				$obj_out->NavigateUrl=$this->exportedDir['excel_path'];				
            break;
			case 'excel2007' :                
				$obj_out->Text = "$filename.xlsx";
				$obj_out->NavigateUrl=$this->exportedDir['excel_path'];				
			break;
			case 'pdf' :
				$obj_out->Text = $filename;
				$obj_out->NavigateUrl=$this->exportedDir['pdf_path'];	
			break;			
		}
	}
	/**
	* digunakan untuk mendapatkan column excel
	*/
	public function getColumnForExcel ($column) {
		//65=A 90=Z
		if ($column >=65 && $column <= 90) {
			return chr($column);
		}elseif ($column >90 && $column <=114) {
			$string_colums='A';
			$column=$column-26;
			return $string_colums.chr($column);
		}		
	}    
    public function printFormB($obj_out,$listProgram=null) {
        $datakegiatan=$this->dataKegiatan;
        $idunit=$datakegiatan['idunit'];
        $tahun=$datakegiatan['tahun'];
        $bulan=$datakegiatan['bulanrealisasi'];
        $nama_bulan=$this->tgl->getMonth(4,$bulan);
        $userid=$datakegiatan['userid'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                     
                $this->rpt->getActiveSheet()->setTitle ("Laporan B");
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row","KABUPATEN BINTAN RIAU TA. $tahun");                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:N$row")->applyFromArray($styleArray);
                $row+=2;
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("A$row:A$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                $this->rpt->getActiveSheet()->mergeCells("B$row:B$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("B$row",'PROGRAM/KEGIATAN');
                $this->rpt->getActiveSheet()->mergeCells("C$row:C$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("C$row",'SUMBER DANA');
                $this->rpt->getActiveSheet()->mergeCells("D$row:D$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');
                $this->rpt->getActiveSheet()->mergeCells("E$row:E$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("E$row",'BOBOT');
                $this->rpt->getActiveSheet()->mergeCells("F$row:J$row");
                $this->rpt->getActiveSheet()->setCellValue("F$row",'REALISASI');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("F$row_akhir:G$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'FISIK');                
                $this->rpt->getActiveSheet()->mergeCells("H$row_akhir:J$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'KEUANGAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'% SPPD');                
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'RP');                
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'% SPPD');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("K$row:L$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("K$row",'SISA ANGGARAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'Rp.');                
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'(%)');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("M$row:M$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("M$row",'LOKASI');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("N$row:N$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("N$row",'KET.');                
                $row_akhir=$row+3;
                $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');
                $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');
                $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');
                $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');
                $this->rpt->getActiveSheet()->setCellValue("M$row_akhir",'13');
                $this->rpt->getActiveSheet()->setCellValue("N$row_akhir",'14');
                
                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(7);                
                $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                $this->rpt->getActiveSheet()->getColumnDimension('N')->setWidth(20);                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->getAlignment()->setWrapText(true);
                $str_pagu = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$tahun'";				
                if (is_array($listProgram)) {                                        
                    $daftar_program=array();
                    $i=1;            
                    $str_pagu = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$tahun'";				
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
                    $this->db->setFieldTable (array('idprogram','kode_program','nama_program'));
                    $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$tahun'";
                    //daftar program pada unit
                    $daftar_program=$this->db->getRecord($str);		                                               
                }                                
                $str=$userid == $str ?'':"$str AND userid=$userid";
                $this->db->setFieldTable (array('total'));
                $r=$this->db->getRecord($str_pagu);                
                $totalnilaipagu=$r[1]['total'];
                //inisialisasi variabel
                $no_huruf=ord('a');
                $totalRealisasiKeseluruhan=0;                
                $totalPersenRealisasiPerSPPD='0.00';                
                $totalSisaAnggaran=0;
                $jumlah_kegiatan=0;
                $row+=4;
                $row_awal=$row;
                while (list($k,$v)=each($daftar_program)) {
                    $idprogram=$v['idprogram'];
                    $this->db->setFieldTable(array('idproyek','nama_proyek','nilai_pagu','sumber_anggaran','idlok','ket_lok','nip_pengguna_anggaran','nama_pengguna_anggaran'));			
                    $str =  "SELECT p.idproyek,p.nama_proyek,p.nilai_pagu,p.sumber_anggaran,idlok,ket_lok,p.nip_pengguna_anggaran,nama_pengguna_anggaran FROM proyek p LEFT JOIN pengguna_anggaran pa ON (pa.nip_pengguna_anggaran=p.nip_pengguna_anggaran) WHERE idprogram='$idprogram'$str_userid";
                    $daftar_kegiatan = $this->db->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {
                        $nama_pengguna_anggaran=$daftar_kegiatan[1]['nama_pengguna_anggaran'];
                        $nip_pengguna_anggaran=$daftar_kegiatan[1]['nip_pengguna_anggaran'];
                        $totalpagueachprogram=0;
                        foreach ($daftar_kegiatan as $eachprogram) {
                            $totalpagueachprogram+=$eachprogram['nilai_pagu'];
                        }
                        $totalpagueachprogram=$this->finance->toRupiah($totalpagueachprogram);
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("A$row",chr($no_huruf));                                                  
                        $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_program']);  
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("D$row",$totalpagueachprogram);                         
                        $row+=1;
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(false);
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(false);
                        $no=1;                   
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$n['no']);  
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$n['nama_proyek']);  
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$n['sumber_anggaran']);
                            $nilai_pagu_proyek=$n['nilai_pagu'];					
                            $rp_nilai_pagu_proyek=$this->finance->toRupiah($nilai_pagu_proyek,'tanpa_rp');
                            $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_nilai_pagu_proyek);
                            $persen_bobot=number_format(($nilai_pagu_proyek/$totalnilaipagu)*100,2);
                            $totalPersenBobot+=$persen_bobot;
                            $this->rpt->getActiveSheet()->setCellValue("E$row",$persen_bobot);
                            $str = "SELECT SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek AND bulan_penggunaan <= '$bulan' AND tahun_penggunaan='$tahun'";                                                
                            $this->db->setFieldTable(array('total'));
                            $realisasi=$this->db->getRecord($str);                        
                            $persen_fisik='0.00';
                            $persenFisikPerSPPD='0.00';
                            $totalrealisasi=0;                        
                            $persen_realisasi='0.00';
                            $persenRealisasiPerSPPD='0.00';
                            $sisa_anggaran=0;
                            $persen_sisa_anggaran='0.00';
                            if ($realisasi[1]['total'] > 0 ){
                                //fisik
                                $str = "SELECT SUM(fisik) AS total FROM v_laporan_a WHERE bulan_penggunaan <='$bulan' AND tahun_penggunaan='$tahun'  AND idproyek='$idproyek'";				
                                $this->db->setFieldTable (array('total'));
                                $r=$this->db->getRecord($str);
                                $totalFisikSatuProyek=$r[1]['total'];
                                
//                                $str = "SELECT COUNT(realisasi) AS total FROM v_laporan_a WHERE bulan_penggunaan<='$bulan' AND tahun_penggunaan='$tahun'  AND idproyek='$idproyek'";				                                
//                                $r=$this->db->getRecord($str);
//                                $jumlahRealisasiFisikSatuProyek=$r[1]['total'];    				
//                                $persen_fisik=number_format(($totalFisikSatuProyek/$jumlahRealisasiFisikSatuProyek),2);
                                $persen_fisik=$totalFisikSatuProyek;
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
                            $this->rpt->getActiveSheet()->setCellValue("F$row",$persen_fisik);
                            $this->rpt->getActiveSheet()->setCellValue("G$row",$persenFisikPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("H$row",$this->finance->toRupiah($totalrealisasi));
                            $this->rpt->getActiveSheet()->setCellValue("I$row",$persen_realisasi);
                            $this->rpt->getActiveSheet()->setCellValue("J$row",$persenRealisasiPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("K$row",$this->finance->toRupiah($sisa_anggaran));
                            $this->rpt->getActiveSheet()->setCellValue("L$row",$persen_sisa_anggaran);
                            $tempat=$this->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $this->rpt->getActiveSheet()->setCellValue("M$row",$tempat);
                            $no+=1;
                            $row+=1;
                            $jumlah_kegiatan+=1;
                        }
                        $no_huruf+=1;                             
                    }                
                }
                
                $this->rpt->getActiveSheet()->mergeCells("A$row:B$row");                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $rp_total_pagu_unit=$this->finance->toRupiah($totalnilaipagu);                                
                $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_total_pagu_unit);
                $this->rpt->getActiveSheet()->setCellValue("E$row",number_format($totalPersenBobot));
                if ($totalPersenRealisasi > 0) 
                    $totalPersenRealisasi=number_format(($totalPersenRealisasi/$jumlah_kegiatan),2);                
                if ($totalPersenSisaAnggaran > 0) 
                    $totalPersenSisaAnggaran=number_format(($totalPersenSisaAnggaran/$jumlah_kegiatan),2);               
                $totalPersenFisik=number_format($totalPersenFisik/$jumlah_kegiatan,2);
                $this->rpt->getActiveSheet()->setCellValue("F$row",$totalPersenFisik);
                $this->rpt->getActiveSheet()->setCellValue("G$row",$totalPersenFisikPerSPPD);
                $rp_total_realisasi_keseluruhan=$this->finance->toRupiah($totalRealisasiKeseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("H$row",$rp_total_realisasi_keseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("I$row",$totalPersenRealisasi);
                $this->rpt->getActiveSheet()->setCellValue("J$row",$totalPersenRealisasiPerSPPD);
                $rp_total_sisa_anggaran=$this->finance->toRupiah($totalSisaAnggaran,'tanpa_rp');
                $this->rpt->getActiveSheet()->setCellValue("K$row",$rp_total_sisa_anggaran);
                $this->rpt->getActiveSheet()->setCellValue("L$row",$totalPersenSisaAnggaran);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->getAlignment()->setWrapText(true);                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("B$row_awal:B$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("M$row_awal:M$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("D$row_awal:D$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("H$row_awal:H$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("K$row_awal:K$row")->applyFromArray($styleArray);                              
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row")->getFont()->setBold(true);
                
                $row+=3;
                $row_awal=$row;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'No.');                                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Uraian');
                $this->rpt->getActiveSheet()->mergeCells("C$row:E$row");
                $this->rpt->getActiveSheet()->setCellValue("C$row",'Jumlah');                            
                
                $this->rpt->getActiveSheet()->mergeCells("L$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("L$row",'Pengguna Anggaran');            
                
                $row+=1;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Posisi Bulan/Tahun');
                $this->rpt->getActiveSheet()->mergeCells("C$row:D$row");
                $this->rpt->getActiveSheet()->setCellValueExplicit("C$row","$nama_bulan $tahun");
                
                $row+=1;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'2');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Pagu Dana Belanja Langsung');
                $this->rpt->getActiveSheet()->mergeCells("C$row:D$row");
                $this->rpt->getActiveSheet()->setCellValueExplicit("C$row",$rp_total_pagu_unit,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $row+=1;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'3');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Realisasi Keuangan (Akumulatif) Rp. (%)');
                $this->rpt->getActiveSheet()->mergeCells("C$row:D$row");
                $this->rpt->getActiveSheet()->setCellValueExplicit("C$row",$rp_total_realisasi_keseluruhan,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rpt->getActiveSheet()->setCellValueExplicit("E$row",$totalPersenFisikPerSPPD,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $row+=1;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'4');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Realisasi Fisik (Akumulatif) (%)');
                $this->rpt->getActiveSheet()->mergeCells("C$row:D$row");
                $this->rpt->getActiveSheet()->setCellValueExplicit("C$row",$totalPersenFisik,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $row+=1;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'5');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'Sisa Dana (Rp)');
                $this->rpt->getActiveSheet()->mergeCells("C$row:D$row");
                $this->rpt->getActiveSheet()->setCellValueExplicit("C$row",$rp_total_sisa_anggaran,PHPExcel_Cell_DataType::TYPE_STRING);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:E$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:E$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );											
                $row_awal+=1;
                $this->rpt->getActiveSheet()->getStyle("B$row_awal:B$row")->applyFromArray($styleArray);                 
                
                $this->rpt->getActiveSheet()->mergeCells("L$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("L$row",$nama_pengguna_anggaran);
                $row+=1;                
                
                $this->rpt->getActiveSheet()->mergeCells("L$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("L$row",'Nip.'.$nip_pengguna_anggaran);
                
                $this->printOut('FormB');
            break;
        }
        $this->setLink($obj_out,'FormB');
    }
    public function printPelaksanaanAnggaran($obj_out,$listProgram=null) {
        $datakegiatan=$this->dataKegiatan;
        $idunit=$datakegiatan['idunit'];
        $tahun=$datakegiatan['tahun'];        
        $userid=$datakegiatan['userid'];        
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');     
                $this->rpt->getActiveSheet()->setTitle ('Pelaksanaan Anggaran');                               
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:J$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH KAB. BINTAN');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:J$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'REKAPITULASI BELANJA LANGSUNG MENURUT PROGRAM DAN KEGIATAN SATUAN KERJA PERANGKAT DAERAH');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:J$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row","TAHUN ANGGARAN $tahun");                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:N$row")->applyFromArray($styleArray);
                
                $row+=3;
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:A$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'KODE PROGRAM & KEGIATAN');
                
                $this->rpt->getActiveSheet()->mergeCells("B$row:B$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("B$row",'URAIAN');
                
                $this->rpt->getActiveSheet()->mergeCells("C$row:C$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("C$row",'LOKASI KEGIATAN');
                
                $this->rpt->getActiveSheet()->mergeCells("D$row:D$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("D$row",'TARGET KINERJA');
                
                $this->rpt->getActiveSheet()->mergeCells("E$row:E$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("E$row",'SUMBER DANA');
                
                $this->rpt->getActiveSheet()->mergeCells("F$row:I$row");
                $this->rpt->getActiveSheet()->setCellValue("F$row",'TRIWULAN');
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'I');
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'II');
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'III');
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'IV');
                
                $this->rpt->getActiveSheet()->mergeCells("J$row:J$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("J$row",'JUMLAH');
                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');
                $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');
                $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');
                $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10 = 6 + 7 + 8 + 9');            
                
                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(45);                
                $this->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(11);                
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(10);
                $this->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(25);                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->rpt->getActiveSheet()->getStyle("A$row:J$row_akhir")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:J$row_akhir")->getAlignment()->setWrapText(true);
                
                $str_userid = ($userid == 'none' || $userid == '') ?'':" userid=$userid AND";
                if (is_array($listProgram)) {                    
                    $daftar_program=array();
                    $i=1;            
                    
                    $str_pagu = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE$str_userid p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$tahun'";				
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
                    $this->db->setFieldTable (array('idprogram','kode_program','nama_program'));
                    $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$tahun'";
                    //daftar program pada unit
                    $daftar_program=$this->db->getRecord($str);		                                               
                }
                
                $row+=3;
                $row_awal=$row;
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
                    $this->db->setFieldTable(array('idproyek','kode_proyek','nama_proyek','nilai_pagu','tk_hasil','sumber_anggaran','idlok','ket_lok'));			
                    $str =  "SELECT p.idproyek,p.kode_proyek,p.nama_proyek,p.nilai_pagu,tk_hasil,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE$str_userid idprogram='$idprogram'";
                    $daftar_kegiatan = $this->db->getRecord($str);
                    if (isset($daftar_kegiatan[1])) { 
                        $str = "SELECT QUARTER(tanggal_realisasi) AS year_qtr,SUM(realisasi) AS total FROM v_laporan_a WHERE$str_userid idprogram=$idprogram GROUP BY year_qtr";                                                                        
                        $this->db->setFieldTable(array('year_qtr','total'));
                        $realisasi=$this->db->getRecord($str);
                        $totalQ1EachProgram=isset($realisasi[1])?$realisasi[1]['total']:0;
                        $totalQ2EachProgram=isset($realisasi[2])?$realisasi[2]['total']:0;
                        $totalQ3EachProgram=isset($realisasi[3])?$realisasi[3]['total']:0;
                        $totalQ4EachProgram=isset($realisasi[4])?$realisasi[4]['total']:0;
                        $totalQEachProgram=$totalQ1EachProgram+$totalQ2EachProgram+$totalQ3EachProgram+$totalQ4EachProgram;
                        
                        $this->rpt->getActiveSheet()->setCellValue("A$row",$v['kode_program']);  
                        $this->rpt->getActiveSheet()->getStyle("B$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_program']);                          
                        $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$this->finance->toRupiah($totalQ1EachProgram),PHPExcel_Cell_DataType::TYPE_STRING);
                        $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$this->finance->toRupiah($totalQ2EachProgram),PHPExcel_Cell_DataType::TYPE_STRING);
                        $this->rpt->getActiveSheet()->setCellValueExplicit("H$row",$this->finance->toRupiah($totalQ3EachProgram),PHPExcel_Cell_DataType::TYPE_STRING);
                        $this->rpt->getActiveSheet()->setCellValueExplicit("I$row",$this->finance->toRupiah($totalQ4EachProgram),PHPExcel_Cell_DataType::TYPE_STRING);
                        $this->rpt->getActiveSheet()->setCellValueExplicit("J$row",$this->finance->toRupiah($totalQEachProgram),PHPExcel_Cell_DataType::TYPE_STRING);                                                                        
                        $row+=1;
                        $this->rpt->getActiveSheet()->getStyle("B$row")->getFont()->setBold(false);                        
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$n['kode_proyek']);  
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$n['nama_proyek']);                          
                            $tempat=$this->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$tempat);                          
                            $this->rpt->getActiveSheet()->setCellValue("D$row",$n['tk_hasil']);
                            $this->rpt->getActiveSheet()->setCellValue("E$row",$n['sumber_anggaran']);
                            $str = "SELECT QUARTER(tanggal_realisasi) AS year_qtr,SUM(realisasi) AS total FROM v_laporan_a WHERE$str_userid idproyek=$idproyek GROUP BY year_qtr";                                                
                            $this->db->setFieldTable(array('year_qtr','total'));
                            $realisasi=$this->db->getRecord($str);              
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
                            
                            $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_q1,PHPExcel_Cell_DataType::TYPE_STRING);
                            $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_q2,PHPExcel_Cell_DataType::TYPE_STRING);
                            $this->rpt->getActiveSheet()->setCellValueExplicit("H$row",$rp_q3,PHPExcel_Cell_DataType::TYPE_STRING);
                            $this->rpt->getActiveSheet()->setCellValueExplicit("I$row",$rp_q4,PHPExcel_Cell_DataType::TYPE_STRING);
                            $this->rpt->getActiveSheet()->setCellValueExplicit("J$row",$rp_total_q,PHPExcel_Cell_DataType::TYPE_STRING);
                            $row+=1;
                        }                        
                    }
                }
                $this->rpt->getActiveSheet()->mergeCells("A$row:E$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'JUMLAH');
                $rp_totalAllQ1=$this->finance->toRupiah($totalAllQ1);
                $rp_totalAllQ2=$this->finance->toRupiah($totalAllQ2);
                $rp_totalAllQ3=$this->finance->toRupiah($totalAllQ3);
                $rp_totalAllQ4=$this->finance->toRupiah($totalAllQ4);                
                $totalAllQ=$totalAllQ1+$totalAllQ2+$totalAllQ3+$totalAllQ4;
                $rp_totalAllQ=$this->finance->toRupiah($totalAllQ);
                $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_totalAllQ1,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_totalAllQ2,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rpt->getActiveSheet()->setCellValueExplicit("H$row",$rp_totalAllQ3,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rpt->getActiveSheet()->setCellValueExplicit("I$row",$rp_totalAllQ4,PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rpt->getActiveSheet()->setCellValueExplicit("J$row",$rp_totalAllQ,PHPExcel_Cell_DataType::TYPE_STRING);
                $styleArray=array(								
                            'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                               'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                        );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:J$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:J$row")->getAlignment()->setWrapText(true);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("F$row_awal:J$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:C$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:J$row")->getFont()->setBold(true);  
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("A$row")->applyFromArray($styleArray);
                $this->printOut('PelaksanaanAnggaran');
            break;
        }
        $this->setLink($obj_out,'PelaksanaanAnggaran');
    }
    public function printJenisPelaksanaan ($obj_out) {
        $datakegiatan=$this->dataKegiatan;        
        $tahun=$datakegiatan['tahun'];
        $kode=$datakegiatan['kode'];
        $nama_jenis=$datakegiatan['nama_jenis'];
        $userid=$datakegiatan['userid'];
        $idunit=$datakegiatan['idunit'];
        switch ($this->getDriver()) {
            case 'excel2003' :                             
            case 'excel2007' :
                switch ($kode){
                    case 'plfisik' :
                    case 'plperencanaan' :
                    case 'plpengawasan' :
                    case 'plpengadaan' :
                        $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                        $this->rpt->getDefaultStyle()->getFont()->setSize('9');                             
                        $this->rpt->getActiveSheet()->setTitle ($kode);
                        $row=1;                
                        $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");				                
                        $this->rpt->getActiveSheet()->setCellValue("A$row","REKAP KEGIATAN $nama_jenis");
                        $row+=1;
                        $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH KABUPATEN BINTAN');                
                        $row+=1;
                        $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");
                        $this->rpt->getActiveSheet()->setCellValue("A$row","APBD TAHUN ANGGARAN $tahun");                
                        $styleArray=array(
                                        'font' => array('bold' => true),
                                        'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                           'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                        );
                        $this->rpt->getActiveSheet()->getStyle("A1:M$row")->applyFromArray($styleArray);
                        $row+=2;                
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                        $this->rpt->getActiveSheet()->setCellValue("B$row",'KEGIATAN/URAIAN');                
                        $this->rpt->getActiveSheet()->setCellValue("C$row",'LOKASI');                
                        $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');                
                        $this->rpt->getActiveSheet()->setCellValue("E$row",'HPS (Rp.)');                
                        $this->rpt->getActiveSheet()->setCellValue("F$row",'NILAI KONTRAK');                
                        $this->rpt->getActiveSheet()->setCellValue("G$row",'SELISIH');                
                        $this->rpt->getActiveSheet()->setCellValue("H$row",'TANGGAL KONTRAK');                
                        $this->rpt->getActiveSheet()->setCellValue("I$row",'TANGGAL PELAKSANAAN');                
                        $this->rpt->getActiveSheet()->setCellValue("J$row",'NAMA & DIREKUR PELAKSANA/PENYEDIA');                
                        $this->rpt->getActiveSheet()->setCellValue("K$row",'NPWP');                
                        $this->rpt->getActiveSheet()->setCellValue("L$row",'ALAMAT PENYEDIA');                
                        $this->rpt->getActiveSheet()->setCellValue("M$row",'SUMBER ANGGARAN');                                

                        $row_akhir=$row+1;
                        $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                        $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');                
                        $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');                
                        $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');                
                        $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'5');                
                        $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');                
                        $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');                
                        $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');                
                        $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');                
                        $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');                
                        $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');                
                        $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');                
                        $this->rpt->getActiveSheet()->setCellValue("M$row_akhir",'13');

                        $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                        $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                        $this->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                        $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                        $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(17);                
                        $this->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(17);                
                        $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(17);
                        $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(15);                
                        $this->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(15);                
                        $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(30);
                        $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(15);                
                        $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(30);
                        $this->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(12);                

                        $styleArray=array(
                                        'font' => array('bold' => true),
                                        'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                           'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                    );
                        $this->rpt->getActiveSheet()->getStyle("A$row:M$row_akhir")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("A$row:M$row_akhir")->getAlignment()->setWrapText(true);
                        $str_userid = $userid == ''?'':" AND p.userid=$userid";
                        $str_unit = $idunit == '' || $idunit == 0 ?'':" AND pr.idunit=$idunit";
                        $str =  "SELECT u.iduraian,p.nama_proyek ,p.nilai_pagu, u.nama_uraian,u.nilai AS nilai_uraian,hps,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,CONCAT (u.nama_perusahaan,' (',u.nama_direktur,') ') AS penyedia,u.npwp,u.alamat_perusahaan,p.sumber_anggaran,u.idlok,u.ket_lok FROM program pr,proyek p,uraian u WHERE pr.idprogram=p.idprogram AND u.idproyek=p.idproyek$str_unit AND p.tahun_anggaran=$tahun AND u.jp='$kode' AND status_lelang=1$str_userid";
                        $this->db->setFieldTable(array('iduraian','nama_proyek','nama_uraian','nilai_pagu','nilai_uraian','hps','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','penyedia','npwp','alamat_perusahaan','sumber_anggaran','idlok','ket_lok'));
                        $result = $this->db->getRecord($str);	                
                        
                        $totalNilaiUraian=0;
                        $totalNilaiKontrak=0;
                        $totalNilaiSelisih=0;
                        $totalHPS=0;
                        $row+=2;
                        $row_awal=$row;
                        while (list($k,$v)=each($result)) {
                            $tempat=$this->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);
                            $nilai_uraian=$v['nilai_uraian'];
                            $rp_nilai_pagu=$this->finance->toRupiah($nilai_uraian);
                            $hps=$v['hps'];
                            $rp_nilai_hps=$this->finance->toRupiah($hps);
                            $nilai_kontrak=$v['penawaran'];
                            $rp_nilai_kontrak=$this->finance->toRupiah($nilai_kontrak);
                            
                            $selisih=$nilai_uraian-$nilai_kontrak;
                            $rp_nilai_selisih=$this->finance->toRupiah($selisih);
                            $tanggal_kontrak=$this->tgl->tanggal('d F Y',$v['tgl_kontrak']);
                            $waktupelaksanaan=$this->tgl->tanggal('d F Y',$v['tgl_mulai_pelaksanaan']). ' s.d '.$this->tgl->tanggal('d F Y',$v['tgl_selesai_pelaksanaan']);
                            
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$v['no']);                
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_proyek'] . ' ['.$v['nama_uraian'].']');                
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$tempat);                                            
                            $this->rpt->getActiveSheet()->setCellValueExplicit("D$row",$rp_nilai_pagu,PHPExcel_Cell_DataType::TYPE_STRING);                
                            $this->rpt->getActiveSheet()->setCellValueExplicit("E$row",$rp_nilai_hps,PHPExcel_Cell_DataType::TYPE_STRING);                
                            $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_nilai_kontrak,PHPExcel_Cell_DataType::TYPE_STRING);                
                            $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_selisih,PHPExcel_Cell_DataType::TYPE_STRING);                
                            $this->rpt->getActiveSheet()->setCellValue("H$row",$tanggal_kontrak);                
                            $this->rpt->getActiveSheet()->setCellValue("I$row",$waktupelaksanaan);                
                            $this->rpt->getActiveSheet()->setCellValue("J$row",$v['penyedia']);                
                            $this->rpt->getActiveSheet()->setCellValue("K$row",$v['npwp']);                
                            $this->rpt->getActiveSheet()->setCellValue("L$row",$v['alamat_perusahaan']);                
                            $this->rpt->getActiveSheet()->setCellValue("M$row",$v['sumber_anggaran']);
                            
                            $totalNilaiUraian+=$nilai_uraian;
                            $totalNilaiKontrak+=$nilai_kontrak;
                            $totalNilaiSelisih+=$selisih;
                            $totalHPS+=$hps;
                            $row+=1;
                        }
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                        $this->rpt->getActiveSheet()->getStyle("A$row_awal:M$row")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("A$row_awal:M$row")->getAlignment()->setWrapText(true);  
                        
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                        $this->rpt->getActiveSheet()->getStyle("B$row_awal:C$row")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("J$row_awal:L$row")->applyFromArray($styleArray);
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																				
                        $this->rpt->getActiveSheet()->getStyle("D$row_awal:G$row")->applyFromArray($styleArray);
                        $rp_nilai_uraian=$this->finance->toRupiah($totalNilaiUraian);
                        $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak);
                        $rp_nilai_selisih=$this->finance->toRupiah($totalNilaiSelisih);
                        $rp_totalHPS=$this->finance->toRupiah($totalHPS);
                        $this->rpt->getActiveSheet()->getStyle("A$row:M$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->mergeCells("A$row:C$row");                
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                        $this->rpt->getActiveSheet()->setCellValueExplicit("D$row",$rp_nilai_uraian,PHPExcel_Cell_DataType::TYPE_STRING);                
                        $this->rpt->getActiveSheet()->setCellValueExplicit("E$row",$rp_totalHPS,PHPExcel_Cell_DataType::TYPE_STRING);                
                        $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_nilai_kontrak,PHPExcel_Cell_DataType::TYPE_STRING);                
                        $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_selisih1,PHPExcel_Cell_DataType::TYPE_STRING);                
                    break;
                    case 'lelangfisik' :                    
                    case 'lelangperencanaan' :                        
                    case 'lelangpengawasan' :                        
                    case 'lelangpengadaan' :
                        $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                        $this->rpt->getDefaultStyle()->getFont()->setSize('9');                             
                        $this->rpt->getActiveSheet()->setTitle ($kode);
                        $row=1;                
                        $this->rpt->getActiveSheet()->mergeCells("A$row:L$row");				                
                        $this->rpt->getActiveSheet()->setCellValue("A$row","REKAP KEGIATAN $nama_jenis");
                        $row+=1;
                        $this->rpt->getActiveSheet()->mergeCells("A$row:L$row");
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH KABUPATEN BINTAN');                
                        $row+=1;
                        $this->rpt->getActiveSheet()->mergeCells("A$row:L$row");
                        $this->rpt->getActiveSheet()->setCellValue("A$row","APBD TAHUN ANGGARAN $tahun");                
                        $styleArray=array(
                                        'font' => array('bold' => true),
                                        'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                           'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                        );
                        $this->rpt->getActiveSheet()->getStyle("A1:L$row")->applyFromArray($styleArray);
                        $row+=2;                                           
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                        $this->rpt->getActiveSheet()->setCellValue("B$row",'KEGIATAN/URAIAN');                
                        $this->rpt->getActiveSheet()->setCellValue("C$row",'LOKASI KEGIATAN');                
                        $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');                
                        $this->rpt->getActiveSheet()->setCellValue("E$row",'BELANJA KONSTRUKSI');                
                        $this->rpt->getActiveSheet()->setCellValue("F$row",'NAMA PPTK');                
                        $this->rpt->getActiveSheet()->setCellValue("G$row",'NAMA PERUSAHAAN PEMENANG (PT/CV)');                
                        $this->rpt->getActiveSheet()->setCellValue("H$row",'NAMA DIREKTUR/YANG DIKUASAKAN');                
                        $this->rpt->getActiveSheet()->setCellValue("I$row",'ALAMAT PERUSAHAAN - NOMOR TELEPON');                
                        $this->rpt->getActiveSheet()->setCellValue("J$row",'NILAI KONTRAK');                
                        $this->rpt->getActiveSheet()->setCellValue("K$row",'REALISASI FISIK (%)');                
                        $this->rpt->getActiveSheet()->setCellValue("L$row",'KETERANGAN');                                        

                        $row_akhir=$row+1;
                        $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                        $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');                
                        $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');                
                        $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');                
                        $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'5');                
                        $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');                
                        $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');                
                        $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');                
                        $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');                
                        $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');                
                        $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');                
                        $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');                                        

                        $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                        $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                        $this->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                        $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                        $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(17);                
                        $this->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(25);                
                        $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                        $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(25);                
                        $this->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(30);                
                        $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(17);
                        $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(10);                
                        $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(15);                                

                        $styleArray=array(
                                        'font' => array('bold' => true),
                                        'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                           'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                    );
                        $this->rpt->getActiveSheet()->getStyle("A$row:L$row_akhir")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("A$row:L$row_akhir")->getAlignment()->setWrapText(true);
                        
                        $str = "SELECT u.iduraian,p.nama_proyek,p.nilai_pagu,u.nama_uraian,u.nilai AS nilai_uraian,penawaran,u.idlok,u.ket_lok,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,nama_perusahaan,u.nama_direktur,CONCAT (u.alamat_perusahaan,' / ',u.no_telepon) AS alamat_perusahaan,nama_pptk FROM uraian u JOIN proyek p ON (u.idproyek=p.idproyek) LEFT JOIN pptk ON (p.nip_pptk=pptk.nip_pptk) WHERE p.tahun_anggaran=$tahun AND u.jp='$kode' AND status_lelang=1";        
                        $this->db->setFieldTable(array('iduraian','nama_proyek','nilai_pagu','nama_uraian','nilai_uraian','penawaran','idlok','ket_lok','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','nama_perusahaan','nama_direktur','alamat_perusahaan','nama_pptk'));
                        $result = $this->db->getRecord($str);	
                        
                        $totalNilaiPagu=0;
                        $totalNilaiUraian=0;
                        $totalNilaiKontrak=0;
                        $jumlah_uraian=0;
                        $row+=2;
                        $row_awal=$row;
                        while (list($k,$v)=each($result)) {                
                            $tempat=$this->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);                    
                            $nilai_pagu=$v['nilai_pagu'];
                            $nilai_uraian=$v['nilai_uraian'];
                            $nilai_penawaran=$v['penawaran'];
                            $rp_nilai_pagu=$this->finance->toRupiah($nilai_pagu);               
                            $rp_uraian=$this->finance->toRupiah($nilai_uraian);                                               
                            $rp_penawaran=$this->finance->toRupiah($nilai_penawaran);                                                               
                            $totalfisik=$this->db->getSumRowsOfTable('fisik',"penggunaan WHERE iduraian={$v['iduraian']}");                                             
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$v['no']);                
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_proyek'] .' ['.$v['nama_uraian'].']');                
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$tempat);                
                            $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_nilai_pagu);                
                            $this->rpt->getActiveSheet()->setCellValue("E$row",$rp_uraian);                
                            $this->rpt->getActiveSheet()->setCellValue("F$row",$v['nama_pptk']);                
                            $this->rpt->getActiveSheet()->setCellValue("G$row",$v['nama_perusahaan']);                
                            $this->rpt->getActiveSheet()->setCellValue("H$row",$v['nama_direktur']);                
                            $this->rpt->getActiveSheet()->setCellValue("I$row",$v['alamat_perusahaan']);                
                            $this->rpt->getActiveSheet()->setCellValue("J$row",$rp_penawaran);                
                            $this->rpt->getActiveSheet()->setCellValue("K$row",$totalfisik);                                
                            $jumlah_uraian+=1;
                            $totalNilaiPagu+= $nilai_pagu;
                            $totalNilaiUraian+=$nilai_uraian;
                            $totalNilaiKontrak+=$nilai_penawaran;       
                            $row+=1;
                        }
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                        $this->rpt->getActiveSheet()->getStyle("A$row_awal:L$row")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("A$row_awal:L$row")->getAlignment()->setWrapText(true);
                        
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                        $this->rpt->getActiveSheet()->getStyle("B$row_awal:C$row")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("F$row_awal:I$row")->applyFromArray($styleArray);
                        
                        $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																				
                        $this->rpt->getActiveSheet()->getStyle("D$row_awal:E$row")->applyFromArray($styleArray);
                        $this->rpt->getActiveSheet()->getStyle("J$row_awal:J$row")->applyFromArray($styleArray);
                        
                        $rp_total_pagu=$this->finance->toRupiah($totalNilaiPagu);
                        $rp_nilai_uraian=$this->finance->toRupiah($totalNilaiUraian);
                        $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak);         
                        $this->rpt->getActiveSheet()->getStyle("A$row:L$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->mergeCells("A$row:C$row");                
                        $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                        $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_total_pagu);                
                        $this->rpt->getActiveSheet()->setCellValue("E$row",$rp_nilai_uraian);                
                        $this->rpt->getActiveSheet()->setCellValue("J$row",$rp_nilai_kontrak);                        
                    break;
                }             
                $this->printOut("JenisPelaksanaan$kode");
            break;
        }        
        $this->setLink($obj_out,"Laporan Jenis Pelaksanaan $nama_jenis");
    }   
    public function printJenisPembangunan ($obj_out) {
        $datakegiatan=$this->dataKegiatan;        
        $tahun=$datakegiatan['tahun'];
        $idjenis_pembangunan=$datakegiatan['idjenis_pembangunan'];
        $nama_jenis=$datakegiatan['nama_jenis'];
        $userid=$datakegiatan['userid'];
        switch ($this->getDriver()) {
            case 'excel2003' :                             
            case 'excel2007' :                                   
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                                             
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row","REKAP KEGIATAN BERDASARKAN $nama_jenis");
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH KABUPATEN BINTAN');                
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:M$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row","APBD TAHUN ANGGARAN $tahun");                
                $styleArray=array(
                                'font' => array('bold' => true),
                                'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:M$row")->applyFromArray($styleArray);
                $row+=2;                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                $this->rpt->getActiveSheet()->setCellValue("B$row",'KEGIATAN/URAIAN');                
                $this->rpt->getActiveSheet()->setCellValue("C$row",'LOKASI');                
                $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');                
                $this->rpt->getActiveSheet()->setCellValue("E$row",'HPS (Rp.)');                
                $this->rpt->getActiveSheet()->setCellValue("F$row",'NILAI KONTRAK');                
                $this->rpt->getActiveSheet()->setCellValue("G$row",'SELISIH');                
                $this->rpt->getActiveSheet()->setCellValue("H$row",'TANGGAL KONTRAK');                
                $this->rpt->getActiveSheet()->setCellValue("I$row",'TANGGAL PELAKSANAAN');                
                $this->rpt->getActiveSheet()->setCellValue("J$row",'NAMA & DIREKUR PELAKSANA/PENYEDIA');                
                $this->rpt->getActiveSheet()->setCellValue("K$row",'NPWP');                
                $this->rpt->getActiveSheet()->setCellValue("L$row",'ALAMAT PENYEDIA');                
                $this->rpt->getActiveSheet()->setCellValue("M$row",'SUMBER ANGGARAN');                                

                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');                
                $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');                
                $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');                
                $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'5');                
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');                
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');                
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');                
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');                
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');                
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');                
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');                
                $this->rpt->getActiveSheet()->setCellValue("M$row_akhir",'13');

                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                $this->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(15);                
                $this->rpt->getActiveSheet()->getColumnDimension('I')->setWidth(15);                
                $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(30);
                $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(15);                
                $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(30);
                $this->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(12);                

                $styleArray=array(
                                'font' => array('bold' => true),
                                'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                            );
                $this->rpt->getActiveSheet()->getStyle("A$row:M$row_akhir")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:M$row_akhir")->getAlignment()->setWrapText(true);
                $str_userid = $userid == ''?'':" AND p.userid=$userid";
                $str =  "SELECT u.iduraian,p.nama_proyek ,p.nilai_pagu, u.nama_uraian,u.nilai AS nilai_uraian,hps,penawaran,tgl_kontrak,tgl_mulai_pelaksanaan,tgl_selesai_pelaksanaan,CONCAT (u.nama_perusahaan,' (',u.nama_direktur,') ') AS penyedia,u.npwp,u.alamat_perusahaan,p.sumber_anggaran,u.idlok,u.ket_lok FROM proyek p,uraian u WHERE u.idproyek=p.idproyek AND p.tahun_anggaran=$tahun AND u.idjenis_pembangunan='$idjenis_pembangunan' AND status_lelang=1$str_userid";
                $this->db->setFieldTable(array('iduraian','nama_proyek','nama_uraian','nilai_pagu','nilai_uraian','hps','penawaran','tgl_kontrak','tgl_mulai_pelaksanaan','tgl_selesai_pelaksanaan','penyedia','npwp','alamat_perusahaan','sumber_anggaran','idlok','ket_lok'));
                $result = $this->db->getRecord($str);	                

                $totalNilaiUraian=0;
                $totalNilaiKontrak=0;
                $totalNilaiSelisih=0;
                $totalHPS=0;
                $row+=2;
                $row_awal=$row;
                while (list($k,$v)=each($result)) {
                    $tempat=$this->getLokasiProyek(null,'lokasi',$v['idlok'],$v['ket_lok']);
                    $nilai_uraian=$v['nilai_uraian'];
                    $rp_nilai_pagu=$this->finance->toRupiah($nilai_uraian);
                    $hps=$v['hps'];
                    $rp_nilai_hps=$this->finance->toRupiah($hps);
                    $nilai_kontrak=$v['penawaran'];
                    $rp_nilai_kontrak=$this->finance->toRupiah($nilai_kontrak);

                    $selisih=$nilai_uraian-$nilai_kontrak;
                    $rp_nilai_selisih=$this->finance->toRupiah($selisih);
                    $tanggal_kontrak=$this->tgl->tanggal('d F Y',$v['tgl_kontrak']);
                    $waktupelaksanaan=$this->tgl->tanggal('d F Y',$v['tgl_mulai_pelaksanaan']). ' s.d '.$this->tgl->tanggal('d F Y',$v['tgl_selesai_pelaksanaan']);

                    $this->rpt->getActiveSheet()->setCellValue("A$row",$v['no']);                
                    $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_proyek'] . ' ['.$v['nama_uraian'].']');                
                    $this->rpt->getActiveSheet()->setCellValue("C$row",$tempat);                                            
                    $this->rpt->getActiveSheet()->setCellValueExplicit("D$row",$rp_nilai_pagu,PHPExcel_Cell_DataType::TYPE_STRING);                
                    $this->rpt->getActiveSheet()->setCellValueExplicit("E$row",$rp_nilai_hps,PHPExcel_Cell_DataType::TYPE_STRING);                
                    $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_nilai_kontrak,PHPExcel_Cell_DataType::TYPE_STRING);                
                    $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_selisih,PHPExcel_Cell_DataType::TYPE_STRING);                
                    $this->rpt->getActiveSheet()->setCellValue("H$row",$tanggal_kontrak);                
                    $this->rpt->getActiveSheet()->setCellValue("I$row",$waktupelaksanaan);                
                    $this->rpt->getActiveSheet()->setCellValue("J$row",$v['penyedia']);                
                    $this->rpt->getActiveSheet()->setCellValue("K$row",$v['npwp']);                
                    $this->rpt->getActiveSheet()->setCellValue("L$row",$v['alamat_perusahaan']);                
                    $this->rpt->getActiveSheet()->setCellValue("M$row",$v['sumber_anggaran']);

                    $totalNilaiUraian+=$nilai_uraian;
                    $totalNilaiKontrak+=$nilai_kontrak;
                    $totalNilaiSelisih+=$selisih;
                    $totalHPS+=$hps;
                    $row+=1;
                }
                $styleArray=array(								
                            'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                               'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                        );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:M$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:M$row")->getAlignment()->setWrapText(true);  

                $styleArray=array(								
                            'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                        );																					 
                $this->rpt->getActiveSheet()->getStyle("B$row_awal:C$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("J$row_awal:L$row")->applyFromArray($styleArray);
                $styleArray=array(								
                            'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                        );																				
                $this->rpt->getActiveSheet()->getStyle("D$row_awal:G$row")->applyFromArray($styleArray);
                $rp_nilai_uraian=$this->finance->toRupiah($totalNilaiUraian);
                $rp_nilai_kontrak=$this->finance->toRupiah($totalNilaiKontrak);
                $rp_nilai_selisih=$this->finance->toRupiah($totalNilaiSelisih);
                $rp_totalHPS=$this->finance->toRupiah($totalHPS);
                $this->rpt->getActiveSheet()->getStyle("A$row:M$row")->getFont()->setBold(true);
                $this->rpt->getActiveSheet()->mergeCells("A$row:C$row");                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $this->rpt->getActiveSheet()->setCellValueExplicit("D$row",$rp_nilai_uraian,PHPExcel_Cell_DataType::TYPE_STRING);                
                $this->rpt->getActiveSheet()->setCellValueExplicit("E$row",$rp_totalHPS,PHPExcel_Cell_DataType::TYPE_STRING);                
                $this->rpt->getActiveSheet()->setCellValueExplicit("F$row",$rp_nilai_kontrak,PHPExcel_Cell_DataType::TYPE_STRING);                
                $this->rpt->getActiveSheet()->setCellValueExplicit("G$row",$rp_nilai_selisih,PHPExcel_Cell_DataType::TYPE_STRING);                

                $this->printOut("JenisPembangunan$idjenis_pembangunan");
            break;
        }        
        $this->setLink($obj_out,"Laporan Jenis Pelaksanaan $nama_jenis");
    }
    public function printRekapPPTK ($obj_out) {
        $datakegiatan=$this->dataKegiatan; 
        $tahun=$datakegiatan['tahun'];
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                     
                $this->rpt->getActiveSheet()->setTitle ("Laporan Pejabat");
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:F$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:F$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row","KABUPATEN BINTAN TA. $tahun");                
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:F$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row","LAPORAN PER PPTK");                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:F$row")->applyFromArray($styleArray);
                $row+=2;
                $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');
                $this->rpt->getActiveSheet()->setCellValue("B$row",'NIP');
                $this->rpt->getActiveSheet()->setCellValue("C$row",'NAMA');
                $this->rpt->getActiveSheet()->setCellValue("D$row",'JUMLAH KEGIATAN');
                $this->rpt->getActiveSheet()->setCellValue("E$row",'TOTAL PAGU KEGIATAN');
                $this->rpt->getActiveSheet()->setCellValue("F$row",'REALISASI');
                
                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(24);                
                $this->rpt->getActiveSheet()->getColumnDimension('C')->setWidth(40);
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(10);                
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(24);                
                $this->rpt->getActiveSheet()->getColumnDimension('F')->setWidth(24);                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->rpt->getActiveSheet()->getStyle("A$row:F$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:F$row")->getAlignment()->setWrapText(true);
                
                $str = 'SELECT nip_pptk,nama_pptk FROM pptk ORDER BY nip_pptk ASC';        
                $this->db->setFieldTable(array('nip_pptk','nama_pptk'));
                $r=$this->db->getRecord($str);                    
                $str = "SELECT nip_pptk,COUNT(idproyek) AS jumlah_proyek,SUM(nilai_pagu) AS nilai_pagu FROM proyek WHERE tahun_anggaran=$tahun GROUP BY nip_pptk ORDER BY nip_pptk ASC";
                $this->db->setFieldTable(array('nip_pptk','jumlah_proyek','nilai_pagu'));
                $kegiatan=$this->db->getRecord($str);                    
                $row+=1;
                $row_awal=$row;
                $all_kegiatan=0;
                $all_nilai_pagu=0;
                $all_realisasi=0;
                while (list($k,$v)=each($r)) {
                    $jumlah_kegiatan=0;
                    $nilai_pagu=0;
                    $realisasi=0;
                    foreach ($kegiatan as $pejabat) {                
                        if ($pejabat['nip_pptk']==$v['nip_pptk']) {
                            $nip=$pejabat['nip_pptk'];
                            $jumlah_kegiatan=$pejabat['jumlah_proyek'];
                            $nilai_pagu=$pejabat['nilai_pagu'];                    
                            $realisasi=$this->db->getSumRowsOfTable("realisasi","proyek pr,uraian u,penggunaan p WHERE pr.idproyek=u.idproyek AND u.iduraian=p.iduraian AND nip_pptk=$nip");
                            break;
                        }                
                    }
                    $this->rpt->getActiveSheet()->setCellValue("A$row",$v['no']);
                    $this->rpt->getActiveSheet()->setCellValue("B$row",$this->nipFormat($v['nip_pptk']));
                    $this->rpt->getActiveSheet()->setCellValue("C$row",$v['nama_pptk']);
                    $this->rpt->getActiveSheet()->setCellValue("D$row",$jumlah_kegiatan);
                    $this->rpt->getActiveSheet()->setCellValue("E$row",$this->finance->toRupiah($nilai_pagu));
                    $this->rpt->getActiveSheet()->setCellValue("F$row",$this->finance->toRupiah($realisasi));
                    $all_kegiatan+=$jumlah_kegiatan;
                    $all_nilai_pagu+=$nilai_pagu;
                    $all_realisasi+=$realisasi;
                    $row+=1;
                }                          
                $this->rpt->getActiveSheet()->mergeCells("A$row:C$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $this->rpt->getActiveSheet()->setCellValue("D$row",$all_kegiatan);                
                $this->rpt->getActiveSheet()->setCellValue("E$row",$this->finance->toRupiah($all_nilai_pagu));
                $this->rpt->getActiveSheet()->setCellValue("F$row",$this->finance->toRupiah($all_realisasi));
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:F$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:F$row")->getAlignment()->setWrapText(true);
                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("C$row_awal:C$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("E$row_awal:F$row")->applyFromArray($styleArray);
                $this->printOut('LaporanRekapPPTK');
            break;
        }
        $this->setLink($obj_out,"Laporan Rekapitulasi PPTK");
    }
    public function printJabatan($obj_out) {
        $datakegiatan=$this->dataKegiatan;                
        $idunit=$datakegiatan['idunit'];
        $tahun=$datakegiatan['tahun'];  
        $userid=$datakegiatan['userid'];  
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                switch ($datakegiatan['tipe_pejabat']) {
                    case 'pengguna_anggaran' :
                        $nip=$datakegiatan['nip_pengguna_anggaran'];
                        $nama_pejabat=$datakegiatan['nama_pengguna_anggaran'];
                        $param_clausa="nip_pengguna_anggaran='$nip'";
                    break;
                    case 'kuasa_pengguna' :
                        $nip=$datakegiatan['nip_kuasa_pengguna'];
                        $nama_pejabat=$datakegiatan['nama_kuasa_pengguna'];
                        $param_clausa="nip_kuasa_pengguna='$nip'";
                    break;
                    case 'ppk' :
                        $nip=$datakegiatan['nip_ppk'];
                        $nama_pejabat=$datakegiatan['nama_ppk'];
                        $param_clausa="nip_ppk='$nip'";
                    break;
                    case 'pptk' :
                        $nip=$datakegiatan['nip_pptk'];
                        $nama_pejabat=$datakegiatan['nama_pptk'];
                        $param_clausa="nip_pptk='$nip'";
                    break;
                }
                $str_userid = $userid == ''?'':" AND userid=$userid";           
                $param_clausa="$param_clausa $str_userid";
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                     
                $this->rpt->getActiveSheet()->setTitle ("Laporan Pejabat");
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row","KABUPATEN BINTAN TA. $tahun");                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:N$row")->applyFromArray($styleArray);
                $row+=2;
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("A$row:A$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                $this->rpt->getActiveSheet()->mergeCells("B$row:B$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("B$row",'PROGRAM/KEGIATAN');
                $this->rpt->getActiveSheet()->mergeCells("C$row:C$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("C$row",'SUMBER DANA');
                $this->rpt->getActiveSheet()->mergeCells("D$row:D$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');
                $this->rpt->getActiveSheet()->mergeCells("E$row:E$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("E$row",'BOBOT');
                $this->rpt->getActiveSheet()->mergeCells("F$row:J$row");
                $this->rpt->getActiveSheet()->setCellValue("F$row",'REALISASI');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("F$row_akhir:G$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'FISIK');                
                $this->rpt->getActiveSheet()->mergeCells("H$row_akhir:J$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'KEUANGAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'% SPPD');                
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'RP');                
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'% SPPD');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("K$row:L$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("K$row",'SISA ANGGARAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'Rp.');                
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'(%)');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("M$row:M$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("M$row",'LOKASI');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("N$row:N$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("N$row",'KET.');                
                $row_akhir=$row+3;
                $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');
                $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');
                $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');
                $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');
                $this->rpt->getActiveSheet()->setCellValue("M$row_akhir",'13');
                $this->rpt->getActiveSheet()->setCellValue("N$row_akhir",'14');
                
                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(7);                
                $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                $this->rpt->getActiveSheet()->getColumnDimension('N')->setWidth(20);                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->getAlignment()->setWrapText(true);
                
                $this->db->setFieldTable (array('idprogram','kode_program','nama_program'));
                $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$tahun'";                
                $daftar_program=$this->db->getRecord($str);	
                $str = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$tahun' AND $param_clausa";				
                $this->db->setFieldTable (array('total'));
                $r=$this->db->getRecord($str);
                //inisialisasi variabel                
                $totalnilaipagu=$r[1]['total'];
                $no_huruf=ord('a');
                $totalRealisasiKeseluruhan=0;                
                $totalPersenRealisasiPerSPPD='0.00';                
                $totalSisaAnggaran=0;
                $jumlah_kegiatan=0;
                $row+=4;
                $row_awal=$row;
                while (list($k,$v)=each($daftar_program)) {
                    $idprogram=$v['idprogram'];
                    $this->db->setFieldTable(array('idproyek','nama_proyek','nilai_pagu','sumber_anggaran','idlok','ket_lok'));			
                    $str =  "SELECT p.idproyek,p.nama_proyek,p.nilai_pagu,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE idprogram='$idprogram' AND $param_clausa";
                    $daftar_kegiatan = $this->db->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {
                        $totalpagueachprogram=0;
                        foreach ($daftar_kegiatan as $eachprogram) {
                            $totalpagueachprogram+=$eachprogram['nilai_pagu'];
                        }
                        $totalpagueachprogram=$this->finance->toRupiah($totalpagueachprogram);
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("A$row",chr($no_huruf));                                                  
                        $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_program']);  
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("D$row",$totalpagueachprogram);                         
                        $row+=1;
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(false);
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(false);
                        $no=1;                   
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$n['no']);  
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$n['nama_proyek']);  
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$n['sumber_anggaran']);
                            $nilai_pagu_proyek=$n['nilai_pagu'];					
                            $rp_nilai_pagu_proyek=$this->finance->toRupiah($nilai_pagu_proyek,'tanpa_rp');
                            $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_nilai_pagu_proyek);
                            $persen_bobot=number_format(($nilai_pagu_proyek/$totalnilaipagu)*100,2);
                            $totalPersenBobot+=$persen_bobot;
                            $this->rpt->getActiveSheet()->setCellValue("E$row",$persen_bobot);
                            $str = "SELECT SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek AND tahun_penggunaan='$tahun'";                                                
                            $this->db->setFieldTable(array('total'));
                            $realisasi=$this->db->getRecord($str);                        
                            $persen_fisik='0.00';
                            $persenFisikPerSPPD='0.00';
                            $totalrealisasi=0;                        
                            $persen_realisasi='0.00';
                            $persenRealisasiPerSPPD='0.00';
                            $sisa_anggaran=0;
                            $persen_sisa_anggaran='0.00';
                            if ($realisasi[1]['total'] > 0 ){
                                //fisik
                                $str = "SELECT SUM(fisik) AS total FROM v_laporan_a WHERE  tahun_penggunaan='$tahun' AND idproyek='$idproyek'";				
                                $this->db->setFieldTable (array('total'));
                                $r=$this->db->getRecord($str);
                                $totalFisikSatuProyek=$r[1]['total'];
                                
                                $str = "SELECT COUNT(realisasi) AS total FROM v_laporan_a WHERE tahun_penggunaan='$tahun'  AND idproyek='$idproyek'";				                                
                                $r=$this->db->getRecord($str);
                                $jumlahRealisasiFisikSatuProyek=$r[1]['total'];    				
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
                            $this->rpt->getActiveSheet()->setCellValue("F$row",$persen_fisik);
                            $this->rpt->getActiveSheet()->setCellValue("G$row",$persenFisikPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("H$row",$this->finance->toRupiah($totalrealisasi));
                            $this->rpt->getActiveSheet()->setCellValue("I$row",$persen_realisasi);
                            $this->rpt->getActiveSheet()->setCellValue("J$row",$persenRealisasiPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("K$row",$this->finance->toRupiah($sisa_anggaran));
                            $this->rpt->getActiveSheet()->setCellValue("L$row",$persen_sisa_anggaran);
                            $tempat=$this->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $this->rpt->getActiveSheet()->setCellValue("M$row",$tempat);
                            $no+=1;
                            $row+=1;
                            $jumlah_kegiatan+=1;
                        }
                        $no_huruf+=1;                             
                    }                
                }
                
                $this->rpt->getActiveSheet()->mergeCells("A$row:B$row");                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $rp_total_pagu_unit=$this->finance->toRupiah($totalnilaipagu);                                
                $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_total_pagu_unit);
                $this->rpt->getActiveSheet()->setCellValue("E$row",$totalPersenBobot);
                if ($totalPersenRealisasi > 0) 
                    $totalPersenRealisasi=number_format(($totalPersenRealisasi/$jumlah_kegiatan),2);                
                if ($totalPersenSisaAnggaran > 0) 
                    $totalPersenSisaAnggaran=number_format(($totalPersenSisaAnggaran/$jumlah_kegiatan),2);               
                $totalPersenFisik=number_format($totalPersenFisik/$jumlah_kegiatan,2);
                $this->rpt->getActiveSheet()->setCellValue("F$row",$totalPersenFisik);
                $this->rpt->getActiveSheet()->setCellValue("G$row",$totalPersenFisikPerSPPD);
                $rp_total_realisasi_keseluruhan=$this->finance->toRupiah($totalRealisasiKeseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("H$row",$rp_total_realisasi_keseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("I$row",$totalPersenRealisasi);
                $this->rpt->getActiveSheet()->setCellValue("J$row",$totalPersenRealisasiPerSPPD);
                $rp_total_sisa_anggaran=$this->finance->toRupiah($totalSisaAnggaran,'tanpa_rp');
                $this->rpt->getActiveSheet()->setCellValue("K$row",$rp_total_sisa_anggaran);
                $this->rpt->getActiveSheet()->setCellValue("L$row",$totalPersenSisaAnggaran);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->getAlignment()->setWrapText(true);                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("B$row_awal:B$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("M$row_awal:M$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("D$row_awal:D$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("H$row_awal:H$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("K$row_awal:K$row")->applyFromArray($styleArray);                              
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row")->getFont()->setBold(true);
                
                $this->printOut('LaporanBerdasarkanJabatan');
            break;
        }
        $this->setLink($obj_out,"Laporan {$datakegiatan['tipe_pejabat']}");
    }
    public function printLokasi($obj_out) {
        $datakegiatan=$this->dataKegiatan;                
        $idunit=$datakegiatan['idunit'];
        $tahun=$datakegiatan['tahun'];       
        $userid=$datakegiatan['userid'];  
        switch ($this->getDriver()) {
            case 'excel2003' :               
            case 'excel2007' :
                switch ($datakegiatan['tipe_lokasi']) {
                    case 'dt1' :
                        $ket_lok='dt1';
                        $idlok=$datakegiatan['iddt1'];
                        $param_clausa="ket_lok='$ket_lok' AND idlok='$idlok'";
                    break;
                    case 'dt2' :
                        $ket_lok='dt2';
                        $idlok=$datakegiatan['iddt2'];
                        $param_clausa="ket_lok='$ket_lok' AND idlok='$idlok'";
                    break; 
                    case 'kec' :
                        $ket_lok='kec';
                        $idlok=$datakegiatan['idkecamatan'];
                        $param_clausa="ket_lok='$ket_lok' AND idlok='$idlok'";
                    break;
                }
                $str_userid = $userid == ''?'':" AND userid=$userid";
                $param_clausa="$param_clausa $str_userid";
                $this->rpt->getDefaultStyle()->getFont()->setName('Arial');                
                $this->rpt->getDefaultStyle()->getFont()->setSize('9');                     
                $this->rpt->getActiveSheet()->setTitle ("Laporan Lokasi $ket_lok");
                $row=1;                
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");				                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'BADAN PERENCANAAN DAN PEMBANGUNAN DAERAH');
                $row+=1;
                $this->rpt->getActiveSheet()->mergeCells("A$row:N$row");
                $this->rpt->getActiveSheet()->setCellValue("A$row"," KABUPATEN BINTAN TA. $tahun");                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                );
                $this->rpt->getActiveSheet()->getStyle("A1:N$row")->applyFromArray($styleArray);
                $row+=2;
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("A$row:A$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("A$row",'NO');                
                $this->rpt->getActiveSheet()->mergeCells("B$row:B$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("B$row",'PROGRAM/KEGIATAN');
                $this->rpt->getActiveSheet()->mergeCells("C$row:C$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("C$row",'SUMBER DANA');
                $this->rpt->getActiveSheet()->mergeCells("D$row:D$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("D$row",'PAGU DANA');
                $this->rpt->getActiveSheet()->mergeCells("E$row:E$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("E$row",'BOBOT');
                $this->rpt->getActiveSheet()->mergeCells("F$row:J$row");
                $this->rpt->getActiveSheet()->setCellValue("F$row",'REALISASI');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("F$row_akhir:G$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'FISIK');                
                $this->rpt->getActiveSheet()->mergeCells("H$row_akhir:J$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'KEUANGAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'% SPPD');                
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'RP');                
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'% KEGIATAN');                
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'% SPPD');                
                $row_akhir=$row+1;
                $this->rpt->getActiveSheet()->mergeCells("K$row:L$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("K$row",'SISA ANGGARAN');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'Rp.');                
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'(%)');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("M$row:M$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("M$row",'LOKASI');                
                $row_akhir=$row+2;
                $this->rpt->getActiveSheet()->mergeCells("N$row:N$row_akhir");
                $this->rpt->getActiveSheet()->setCellValue("N$row",'KET.');                
                $row_akhir=$row+3;
                $this->rpt->getActiveSheet()->setCellValue("A$row_akhir",'1');                
                $this->rpt->getActiveSheet()->setCellValue("B$row_akhir",'2');
                $this->rpt->getActiveSheet()->setCellValue("C$row_akhir",'3');
                $this->rpt->getActiveSheet()->setCellValue("D$row_akhir",'4');
                $this->rpt->getActiveSheet()->setCellValue("E$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("F$row_akhir",'6');
                $this->rpt->getActiveSheet()->setCellValue("G$row_akhir",'7');
                $this->rpt->getActiveSheet()->setCellValue("H$row_akhir",'8');
                $this->rpt->getActiveSheet()->setCellValue("I$row_akhir",'9');
                $this->rpt->getActiveSheet()->setCellValue("J$row_akhir",'10');
                $this->rpt->getActiveSheet()->setCellValue("K$row_akhir",'11');
                $this->rpt->getActiveSheet()->setCellValue("L$row_akhir",'12');
                $this->rpt->getActiveSheet()->setCellValue("M$row_akhir",'13');
                $this->rpt->getActiveSheet()->setCellValue("N$row_akhir",'14');
                
                $this->rpt->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('B')->setWidth(50);                
                $this->rpt->getActiveSheet()->getColumnDimension('D')->setWidth(17);
                $this->rpt->getActiveSheet()->getColumnDimension('E')->setWidth(7);                
                $this->rpt->getActiveSheet()->getColumnDimension('G')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('H')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('J')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('K')->setWidth(17);                
                $this->rpt->getActiveSheet()->getColumnDimension('L')->setWidth(6);
                $this->rpt->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                $this->rpt->getActiveSheet()->getColumnDimension('N')->setWidth(20);                
                
                $styleArray=array(
								'font' => array('bold' => true),
								'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												   'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
								'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
							);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row_akhir")->getAlignment()->setWrapText(true);
                
                $this->db->setFieldTable (array('idprogram','kode_program','nama_program'));
                $str = "SELECT idprogram,kode_program,nama_program FROM program WHERE idunit='$idunit' AND tahun='$tahun'";                
                $daftar_program=$this->db->getRecord($str);	
                $str = "SELECT SUM(p.nilai_pagu) AS total FROM proyek p,program pr WHERE p.idprogram=pr.idprogram AND pr.idunit='$idunit' AND pr.tahun='$tahun' AND $param_clausa";				
                $this->db->setFieldTable (array('total'));
                $r=$this->db->getRecord($str);
                //inisialisasi variabel
                $totalnilaipagu=$r[1]['total'];
                $no_huruf=ord('a');
                $totalRealisasiKeseluruhan=0;                
                $totalPersenRealisasiPerSPPD='0.00';                
                $totalSisaAnggaran=0;
                $jumlah_kegiatan=0;
                $row+=4;
                $row_awal=$row;
                while (list($k,$v)=each($daftar_program)) {
                    $idprogram=$v['idprogram'];
                    $this->db->setFieldTable(array('idproyek','nama_proyek','nilai_pagu','sumber_anggaran','idlok','ket_lok'));			
                    $str =  "SELECT p.idproyek,p.nama_proyek,p.nilai_pagu,p.sumber_anggaran,idlok,ket_lok FROM proyek p WHERE idprogram='$idprogram' AND $param_clausa";
                    $daftar_kegiatan = $this->db->getRecord($str);
                    if (isset($daftar_kegiatan[1])) {
                        $totalpagueachprogram=0;
                        foreach ($daftar_kegiatan as $eachprogram) {
                            $totalpagueachprogram+=$eachprogram['nilai_pagu'];
                        }
                        $totalpagueachprogram=$this->finance->toRupiah($totalpagueachprogram);
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("A$row",chr($no_huruf));                                                  
                        $this->rpt->getActiveSheet()->setCellValue("B$row",$v['nama_program']);  
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(true);
                        $this->rpt->getActiveSheet()->setCellValue("D$row",$totalpagueachprogram);                         
                        $row+=1;
                        $this->rpt->getActiveSheet()->getStyle("A$row:B$row")->getFont()->setBold(false);
                        $this->rpt->getActiveSheet()->getStyle("D$row:D$row")->getFont()->setBold(false);
                        $no=1;                   
                        while (list($m,$n)=each($daftar_kegiatan)) {
                            $idproyek=$n['idproyek'];
                            $this->rpt->getActiveSheet()->setCellValue("A$row",$n['no']);  
                            $this->rpt->getActiveSheet()->setCellValue("B$row",$n['nama_proyek']);  
                            $this->rpt->getActiveSheet()->setCellValue("C$row",$n['sumber_anggaran']);
                            $nilai_pagu_proyek=$n['nilai_pagu'];					
                            $rp_nilai_pagu_proyek=$this->finance->toRupiah($nilai_pagu_proyek,'tanpa_rp');
                            $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_nilai_pagu_proyek);
                            $persen_bobot=number_format(($nilai_pagu_proyek/$totalnilaipagu)*100,2);
                            $totalPersenBobot+=$persen_bobot;
                            $this->rpt->getActiveSheet()->setCellValue("E$row",$persen_bobot);
                            $str = "SELECT SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek AND tahun_penggunaan='$tahun'";                                                
                            $this->db->setFieldTable(array('total'));
                            $realisasi=$this->db->getRecord($str);                        
                            $persen_fisik='0.00';
                            $persenFisikPerSPPD='0.00';
                            $totalrealisasi=0;                        
                            $persen_realisasi='0.00';
                            $persenRealisasiPerSPPD='0.00';
                            $sisa_anggaran=0;
                            $persen_sisa_anggaran='0.00';
                            if ($realisasi[1]['total'] > 0 ){
                                //fisik
                                $str = "SELECT SUM(fisik) AS total FROM v_laporan_a WHERE  tahun_penggunaan='$tahun' AND idproyek='$idproyek'";				
                                $this->db->setFieldTable (array('total'));
                                $r=$this->db->getRecord($str);
                                $totalFisikSatuProyek=$r[1]['total'];
                                
                                $str = "SELECT COUNT(realisasi) AS total FROM v_laporan_a WHERE tahun_penggunaan='$tahun'  AND idproyek='$idproyek'";				                                
                                $r=$this->db->getRecord($str);
                                $jumlahRealisasiFisikSatuProyek=$r[1]['total'];    				
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
                            $this->rpt->getActiveSheet()->setCellValue("F$row",$persen_fisik);
                            $this->rpt->getActiveSheet()->setCellValue("G$row",$persenFisikPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("H$row",$this->finance->toRupiah($totalrealisasi));
                            $this->rpt->getActiveSheet()->setCellValue("I$row",$persen_realisasi);
                            $this->rpt->getActiveSheet()->setCellValue("J$row",$persenRealisasiPerSPPD);
                            $this->rpt->getActiveSheet()->setCellValue("K$row",$this->finance->toRupiah($sisa_anggaran));
                            $this->rpt->getActiveSheet()->setCellValue("L$row",$persen_sisa_anggaran);
                            $tempat=$this->getLokasiProyek($idproyek,'lokasi',$n['idlok'],$n['ket_lok']);
                            $this->rpt->getActiveSheet()->setCellValue("M$row",$tempat);
                            $no+=1;
                            $row+=1;
                            $jumlah_kegiatan+=1;
                        }
                        $no_huruf+=1;                             
                    }                
                }
                
                $this->rpt->getActiveSheet()->mergeCells("A$row:B$row");                
                $this->rpt->getActiveSheet()->setCellValue("A$row",'Jumlah');
                $rp_total_pagu_unit=$this->finance->toRupiah($totalnilaipagu);                                
                $this->rpt->getActiveSheet()->setCellValue("D$row",$rp_total_pagu_unit);
                $this->rpt->getActiveSheet()->setCellValue("E$row",$totalPersenBobot);
                if ($totalPersenRealisasi > 0) 
                    $totalPersenRealisasi=number_format(($totalPersenRealisasi/$jumlah_kegiatan),2);                
                if ($totalPersenSisaAnggaran > 0) 
                    $totalPersenSisaAnggaran=number_format(($totalPersenSisaAnggaran/$jumlah_kegiatan),2);               
                $totalPersenFisik=number_format($totalPersenFisik/$jumlah_kegiatan,2);
                $this->rpt->getActiveSheet()->setCellValue("F$row",$totalPersenFisik);
                $this->rpt->getActiveSheet()->setCellValue("G$row",$totalPersenFisikPerSPPD);
                $rp_total_realisasi_keseluruhan=$this->finance->toRupiah($totalRealisasiKeseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("H$row",$rp_total_realisasi_keseluruhan);
                $this->rpt->getActiveSheet()->setCellValue("I$row",$totalPersenRealisasi);
                $this->rpt->getActiveSheet()->setCellValue("J$row",$totalPersenRealisasiPerSPPD);
                $rp_total_sisa_anggaran=$this->finance->toRupiah($totalSisaAnggaran,'tanpa_rp');
                $this->rpt->getActiveSheet()->setCellValue("K$row",$rp_total_sisa_anggaran);
                $this->rpt->getActiveSheet()->setCellValue("L$row",$totalPersenSisaAnggaran);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                       'vertical'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
                                );
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("A$row_awal:N$row")->getAlignment()->setWrapText(true);                
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("B$row_awal:B$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("M$row_awal:M$row")->applyFromArray($styleArray);
                $styleArray=array(								
                                    'alignment' => array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
                                );																					 
                $this->rpt->getActiveSheet()->getStyle("D$row_awal:D$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("H$row_awal:H$row")->applyFromArray($styleArray);
                $this->rpt->getActiveSheet()->getStyle("K$row_awal:K$row")->applyFromArray($styleArray);                              
                $this->rpt->getActiveSheet()->getStyle("A$row:N$row")->getFont()->setBold(true);
                
                $this->printOut('LaporanBerdasarkanLokasi');
            break;
        }
        $this->setLink($obj_out,"Laporan {$datakegiatan['tipe_lokasi']}");
    }
}
?>