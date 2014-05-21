<?php
prado::using('Application.ChartPage');
prado::using('Application.lib.jpgraph.jpgraph_pie');
prado::using('Application.lib.jpgraph.jpgraph_pie3d');
class ReportChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	           
        $this->generateImage();        
    }
    private function checkNilai ($datareport=array()) {
        $value=0;
        while (list($k,$v)=each($datareport)) {            
            $value+=$v;            
        }
        return $value;
    }
    private function generateImage () {
        $datareport=$_SESSION['currentPageReports']['dataReport'];        
        $graph=new PieGraph(1024,250);
        $graph->SetShadow();       
        $graph->SetMarginColor('azure3');        
        $piesize=0.40;
        $rekapkegiatan=array($datareport['rekapkegiatan']['lelangfisik'],
                     $datareport['rekapkegiatan']['plfisik'],
                     $datareport['rekapkegiatan']['lelangpengadaan'],
                     $datareport['rekapkegiatan']['plpengadaan'],
                     $datareport['rekapkegiatan']['lelangperencanaan'],
                     $datareport['rekapkegiatan']['plperencanaan'],
                     $datareport['rekapkegiatan']['lelangpengawasan'],
                     $datareport['rekapkegiatan']['plpengawasan']);
        
        if ($this->checkNilai($rekapkegiatan) > 0) {
            $plotRekapKegiatan = new PiePlot3D($rekapkegiatan); 
            $plotRekapKegiatan->title->Set ('Rekap Kegiatan');
            $plotRekapKegiatan->SetLabelPos(0.6);
            $plotRekapKegiatan->SetLegends(array('Lelang Fisik','PL Fisik','Lelang Pengadaan','PL Pengadaan','Lelang Perencanaan','PL Perencanaan','Lelang Pengawasan','PL Pengawasan',''));
        }else {
            $plotRekapKegiatan = new PiePlot3D(array(100)); 
            $plotRekapKegiatan->value->show(false);
            $plotRekapKegiatan->SetTheme('sand');
            $plotRekapKegiatan->title->Set ('Rekap Kegiatan (Tidak Ada Data)');
            $plotRekapKegiatan->value->SetColor('red');            
            $plotRekapKegiatan->SetLabelPos(0.6);
        }
        $plotRekapKegiatan->SetLabelType(PIE_VALUE_ADJPERCENTAGE);        
        $plotRekapKegiatan->SetCenter(0.11,0.4); 
        $plotRekapKegiatan->setSize($piesize);        
        
        $sudahdilelang=array($datareport['sudahdilelang']['lelangfisik'],
                     $datareport['sudahdilelang']['plfisik'],
                     $datareport['sudahdilelang']['lelangpengadaan'],
                     $datareport['sudahdilelang']['plpengadaan'],
                     $datareport['sudahdilelang']['lelangperencanaan'],
                     $datareport['sudahdilelang']['plperencanaan'],
                     $datareport['sudahdilelang']['lelangpengawasan'],
                     $datareport['sudahdilelang']['plpengawasan']);
        
        if ($this->checkNilai($sudahdilelang) > 0) {
            $plotSudahDilelang = new PiePlot3D($sudahdilelang); 
            $plotSudahDilelang->title->Set ('Sudah Dilelang');
            $plotSudahDilelang->SetLabelPos(0.6);
        }else {
            $plotSudahDilelang = new PiePlot3D(array(100)); 
            $plotSudahDilelang->value->show(false);
            $plotSudahDilelang->SetTheme('sand');
            $plotSudahDilelang->title->Set ('Sudah Dilelang (Tidak Ada Data)');            
            $plotSudahDilelang->SetLabelPos(0.6);
        }
               
        $plotSudahDilelang->SetLabelType(PIE_VALUE_ADJPERCENTAGE);      
        $plotSudahDilelang->SetCenter(0.36,0.4);       
        $plotSudahDilelang->setSize($piesize);
        
        $proseslelang=array($datareport['proseslelang']['lelangfisik'],
                             $datareport['proseslelang']['plfisik'],
                             $datareport['proseslelang']['lelangpengadaan'],
                             $datareport['proseslelang']['plpengadaan'],
                             $datareport['proseslelang']['lelangperencanaan'],
                             $datareport['proseslelang']['plperencanaan'],
                             $datareport['proseslelang']['lelangpengawasan'],
                             $datareport['proseslelang']['plpengawasan']);
        
        if ($this->checkNilai($proseslelang) > 0) {
            $plotProseslelang = new PiePlot3D($proseslelang);
            $plotProseslelang->title->Set ('Proses Lelang');
            $plotProseslelang->SetLabelPos(0.6);
        }else {
            $plotProseslelang = new PiePlot3D(array(100));        
            $plotProseslelang->value->show(false);
            $plotProseslelang->SetTheme('sand');
            $plotProseslelang->title->Set ('Proses Lelang (Tidak Ada Data)');                        
            $plotProseslelang->SetLabelPos(0.6);
        }
        $plotProseslelang->SetLabelType(PIE_VALUE_ADJPERCENTAGE);        
        $plotProseslelang->SetCenter(0.61,0.4);       
        $plotProseslelang->setSize($piesize);
        
        $belumdilelang=array($datareport['belumdilelang']['lelangfisik'],
                     $datareport['belumdilelang']['plfisik'],
                     $datareport['belumdilelang']['lelangpengadaan'],
                     $datareport['belumdilelang']['plpengadaan'],
                     $datareport['belumdilelang']['lelangperencanaan'],
                     $datareport['belumdilelang']['plperencanaan'],
                     $datareport['belumdilelang']['lelangpengawasan'],
                     $datareport['belumdilelang']['plpengawasan']);
        
        if ($this->checkNilai($belumdilelang) > 0) {
            $plotBelumDilelang = new PiePlot3D($belumdilelang);                    
            $plotBelumDilelang->title->Set ('Belum Dilelang');
            $plotBelumDilelang->SetLabelPos(0.6);
        }else {
            $plotBelumDilelang = new PiePlot3D(array(100));        
            $plotBelumDilelang->value->show(false);
            $plotBelumDilelang->SetTheme('sand');
            $plotBelumDilelang->title->Set ('Belum Dilelang (Tidak Ada Data)');                        
            $plotBelumDilelang->SetLabelPos(0.6);
        }        
        $plotBelumDilelang->SetLabelType(PIE_VALUE_ADJPERCENTAGE);        
        $plotBelumDilelang->SetCenter(0.86,0.4);       
        $plotBelumDilelang->setSize($piesize);       
        
        $graph->legend->Pos(0.25,0.77);
        $graph->Add($plotRekapKegiatan);
        $graph->Add($plotSudahDilelang);
        $graph->Add($plotProseslelang);
        $graph->Add($plotBelumDilelang);
        $graph->Stroke();
    }
}
?>
