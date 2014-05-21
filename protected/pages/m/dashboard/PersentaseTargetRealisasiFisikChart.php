<?php
prado::using('Application.ChartPage');
prado::using ('Application.lib.jpgraph');
prado::using ('Application.lib.jpgraph.jpgraph_line');
class PersentaseTargetRealisasiFisikChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	   
        $this->generateImage();        
    }    
    private function generateImage () {
        $bulan=$this->TGL->getMonth(3);
        $tahun=$this->session['ta'];
        
        //data
        $jumlah_kegiatan=$this->DB->getCountRowsOfTable ("proyek p WHERE tahun_anggaran='$tahun'",'idproyek');
        $str = "SELECT bulan,SUM(fisik) AS jumlah_fisik FROM target_uraian WHERE tahun=$tahun GROUP BY bulan ORDER BY bulan ASC";
        $this->DB->setFieldTable(array('bulan','jumlah_fisik'));
        $r=$this->DB->getRecord($str);        
        $ydata=array('','','','','','','','','','','','');        
        $i=0;           
        $persen=0;
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {
                    $persen+=round($n['jumlah_fisik']/$jumlah_kegiatan,2);                    
                    break;
                }
            }
            $ydata[$i]=$persen;            
            $i+=1;
        }     
        
        $str = "SELECT bulan,SUM(fisik) AS jumlah_fisik,COUNT(idpenggunaan) AS jumlah_baris_realisasi FROM penggunaan pe,uraian u,proyek p,program pr WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND p.idprogram=pr.idprogram AND tahun_anggaran='$tahun' GROUP BY pe.bulan ORDER BY bulan ASC";            
        $this->DB->setFieldTable(array('bulan','jumlah_fisik','jumlah_baris_realisasi'));
        $r=$this->DB->getRecord($str);  
        $ydata2=array('','','','','','','','','','','','');
        $i=0; 
        $persen=0;
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {
                    $persen+=round(($n['jumlah_fisik']/$n['jumlah_baris_realisasi']),2);                    
                    break;
                }
            }
            $ydata2[$i]=$persen;            
            $i+=1;
        }     
        
        $graph = new Graph(600,250);
        $graph->setScale ('textlin');
        $graph->SetFrame(false);
        $graph->SetShadow();
        
        //setup margin and titles
        $graph->SetMargin(40, 20, 20, 40);
        $graph->title->set("Target dan Realisasi Fisik Tahun $tahun");
        $graph->yaxis->title->set('Persen');
        $graph->xaxis->title->set('Bulan');
        
        $graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
        $graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
        $graph->xaxis->SetTickLabels(array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Des'));
        
        $lineplot = new LinePlot($ydata);
        $lineplot->SetWeight(2);//two pixel  
        $lineplot->mark->SetType (MARK_FILLEDCIRCLE);
        $lineplot->SetLegend('Target Fisik');        
        $lineplot->value->setMargin(14);
        $lineplot->SetCenter();
        
        // Add the plot to the graph
        $graph->Add($lineplot);
        
        $lineplot2 = new LinePlot($ydata2);
        $lineplot2->SetWeight(2);//two pixel  
        $lineplot2->SetLegend('Realisasi Fisik');
        $lineplot2->mark->SetType (MARK_STAR);
        $lineplot2->value->setMargin(14);
        $lineplot2->SetCenter();
        
        // Add the plot to the graph
        $graph->Add($lineplot2);
        
        $graph->Stroke();
        
    }
}
?>
