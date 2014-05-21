<?php
prado::using('Application.ChartPage');
prado::using ('Application.lib.jpgraph.jpgraph_line');
class formAChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	                    
        $this->generateImage();        
    }  
    private function generateImage () {        
        $idproyek=$this->session['currentPageFormA']['dataKegiatan']['idproyek'];
        $str="SELECT bulan_penggunaan,SUM(realisasi) AS total FROM v_laporan_a WHERE idproyek=$idproyek GROUP BY bulan_penggunaan ORDER BY bulan_penggunaan ASC";
        $this->DB->setFieldTable(array('bulan_penggunaan','total'));
        $r=$this->DB->getRecord($str);     
        $bulan=array('01'=>0,'02'=>0,'03'=>0,'04'=>0,'05'=>0,'06'=>0,'07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0);     
        
        while (list($m,$n)=each($r)) {
            $bulan[$n['bulan_penggunaan']]=$n['total'];                
        }       
        $total=0;
        while (list($m,$n)=each($bulan)) {
            $total+=$n;
            $datarealisasi[]=($total > 0)?$total/1000000:0;              
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
        $graph->title->Set ('Realisasi Anggaran Kegiatan (dalam juta)');
        $graph->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->SetShadow();       

        $graph->xaxis->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->xaxis->SetTickLabels(array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Des'));
        $graph->yaxis->title->SetFont (FF_FONT1,FS_BOLD);
        $graph->yaxis->SetColor('blue');
        if ($total > 1000000) $graph->yaxis->SetLabelFormatCallback(array('FormattingGraph','toRupiah'));
        
        // Create the linear plot
        $lineplot = new LinePlot($datarealisasi);
        $lineplot->setColor('blue');        
        $lineplot->SetWeight(30);        
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
