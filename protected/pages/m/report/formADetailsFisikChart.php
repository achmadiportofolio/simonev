<?php
prado::using('Application.ChartPage');
prado::using ('Application.lib.jpgraph.jpgraph_line');
class formADetailsFisikChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->createObjKegiatan();        
        $this->generateImage();        
    }    
    private function generateImage () {
        $bulan=$this->TGL->getMonth(3);
        $iduraian=$this->session['currentPageFormADetails']['iduraian'];
        $realisasi=$this->kegiatan->getList("penggunaan WHERE iduraian=$iduraian",array('bulan','fisik'),'bulan',null,5);                
        $fisik=array();
        $nilai=0;
        foreach ($bulan as $k=>$v) {            
            if (isset($realisasi[$k])) {
                $nilai+=$realisasi[$k];
            }
            $fisik[]=$nilai;            
        }
        // Size of the overall graph
        $width=800;
        $height=300;

        // Create the graph and set a scale.
        // These two calls are always required
        $graph = new Graph($width,$height);
        $graph->SetScale('textlin');
        $graph->SetMargin(100,20,60,20);
        $graph->title->Set ('Realisasi Fisik');
        $graph->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->SetShadow();       

        $graph->xaxis->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->xaxis->setTitleMargin(13);
        $graph->xaxis->SetTickLabels(array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Des'));

        $graph->yaxis->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->yaxis->setTitleMargin(80);
        $graph->yaxis->SetColor('blue');        
        
        // Create the linear plot
        $lineplot = new LinePlot($fisik);
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
