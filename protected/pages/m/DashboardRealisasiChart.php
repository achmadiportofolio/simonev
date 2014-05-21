<?php
prado::using('Application.ChartPage');
prado::using ('Application.lib.jpgraph.jpgraph_line');
class DashboardRealisasiChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->createObjKegiatan();        
        $this->generateImage();        
    }    
    private function generateImage () {
        $bulan=$this->TGL->getMonth(3);
        $tahun=$this->session['ta'];
        $str = "SELECT bulan,SUM(realisasi) AS realisasi FROM penggunaan WHERE tahun=$tahun GROUP BY bulan ORDER BY bulan";
        $this->DB->setFieldTable(array('bulan','realisasi'));
        $r=$this->DB->getRecord($str);        
        $points=array();        
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {
                    $nilai+=$n['realisasi'];                    
                    break;
                }
            }
            $points[]=($nilai > 0)?$nilai/1000000:0;               
        }        
        // Size of the overall graph
        $width=800;
        $height=300;

        // Create the graph and set a scale.
        // These two calls are always required
        $graph = new Graph($width,$height);
        $graph->SetScale('textlin');
        $graph->SetFrame(false);
        $graph->SetMargin(100,20,60,20);
        $graph->title->Set ('Realisasi Anggaran (Dalam Juta)');
        $graph->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->SetShadow();
        $graph->xaxis->title->SetFont (FF_FONT1,FS_BOLD);

        $graph->xaxis->SetTickLabels(array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Des'));

        $graph->yaxis->title->SetFont (FF_FONT1,FS_BOLD);

        $graph->yaxis->SetColor('blue');
        if ($nilai > 1000000) $graph->yaxis->SetLabelFormatCallback(array('FormattingGraph','toRupiah'));
        
        // Create the linear plot
        $lineplot = new LinePlot($points);
        $lineplot->setColor('blue');
        $lineplot->SetWeight(2);        
        $lineplot->mark->SetType (MARK_STAR);
        $lineplot->mark->SetColor ('red');
        $lineplot->value->setMargin(14);
        $lineplot->SetCenter();
        // Add the plot to the graph
        $graph->Add($lineplot);
        // Display the graph
        $graph->Stroke();
    }
}
?>
