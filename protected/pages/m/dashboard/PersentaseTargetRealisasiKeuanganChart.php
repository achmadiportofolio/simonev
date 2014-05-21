<?php
prado::using('Application.ChartPage');
prado::using ('Application.lib.jpgraph');
prado::using ('Application.lib.jpgraph.jpgraph_line');
class PersentaseTargetRealisasiKeuanganChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	   
        $this->generateImage();        
    }    
    private function generateImage () {
        $bulan=$this->TGL->getMonth(3);
        $tahun=$this->session['ta'];
        
        //data
        $jumlah_sp2d=$this->DB->getSumRowsOfTable ('target_sp2d',"target_uraian WHERE tahun=$tahun");
        $str = "SELECT bulan,SUM(target_sp2d) AS jumlah_sp2d FROM target_uraian WHERE tahun=$tahun GROUP BY bulan ORDER BY bulan ASC";
        $this->DB->setFieldTable(array('bulan','jumlah_sp2d'));
        $r=$this->DB->getRecord($str);                
        $ydata=array('','','','','','','','','','','','');        
        $i=0;           
        $persen=0;
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {                    
                    $persen+=round($n['jumlah_sp2d']/$jumlah_sp2d,2);                    
                    break;
                }
            }
            
            $ydata[$i]=$persen;            
            $i+=1;
        }        
        
        $jumlah_realisasi=$this->DB->getSumRowsOfTable ('realisasi',"penggunaan pe,uraian u,proyek p WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND tahun_anggaran='$tahun'");
        $str = "SELECT bulan,SUM(realisasi) AS jumlah_realisasi FROM penggunaan pe,uraian u,proyek p WHERE pe.iduraian=u.iduraian AND p.idproyek=u.idproyek AND tahun_anggaran='$tahun' GROUP BY pe.bulan ORDER BY pe.bulan ASC";
        $this->DB->setFieldTable(array('bulan','jumlah_realisasi'));
        $r=$this->DB->getRecord($str);       
                
        $ydata2=array('','','','','','','','','','','','');
        $i=0; 
        $persen=0;
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {
                    $persen+=round(($n['jumlah_realisasi']/$jumlah_realisasi),2);                    
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
        $graph->title->set("Target dan Realisasi Keuangan Tahun $tahun");
        $graph->yaxis->title->set('Persen');
        $graph->xaxis->title->set('Bulan');
        
        $graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
        $graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
        $graph->xaxis->SetTickLabels(array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Des'));
        
               
        $lineplot2 = new LinePlot($ydata2);
        $lineplot2->SetWeight(2);//two pixel  
        $lineplot2->SetLegend('Target Keuangan');
        $lineplot2->mark->SetType (MARK_FILLEDCIRCLE);
        $lineplot2->value->setMargin(14);
        $lineplot2->SetCenter();
        
        // Add the plot to the graph
        $graph->Add($lineplot2);
        
        $lineplot = new LinePlot($ydata);
        $lineplot->SetWeight(2);//two pixel  
        $lineplot->mark->SetType (MARK_STAR);
        $lineplot->SetLegend('Realisasi Keuangan');        
        $lineplot->value->setMargin(14);
        $lineplot->SetCenter();
        
        // Add the plot to the graph
        $graph->Add($lineplot);
        
        $graph->Stroke();
        
    }
}
?>
