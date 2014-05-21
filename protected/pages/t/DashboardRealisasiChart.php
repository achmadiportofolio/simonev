<?php
prado::using('Application.ChartPage');
$pdraw=prado::getPathOfNamespace('Application.lib.pChart.class');
require_once "$pdraw/pDraw.class.php";
require_once "$pdraw/pImage.class.php";
class DashboardRealisasiChart extends ChartPage {    
    public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->createObjKegiatan();        
        $this->generateImage();        
    }
    private function populateDataset () {
        $bulan=$this->TGL->getMonth(3);
        $tahun=$this->session['ta'];
        $str = "SELECT bulan,SUM(realisasi) AS realisasi FROM penggunaan WHERE tahun=$tahun GROUP BY bulan ORDER BY bulan";
        $this->DB->setFieldTable(array('bulan','realisasi'));
        $r=$this->DB->getRecord($str);        
        $newmonth=array();
        $points=array();        
        foreach ($bulan as $k=>$v) {            
            foreach ($r as $n) {                                
                if ($k==$n['bulan']) {
                    $nilai+=$n['realisasi'];                    
                    break;
                }
            }
            $points[$k]=$nilai;
            $newmonth[]=$v;
        } 
        $this->dataSet->addPoints($points,"Serie1");
        $this->dataSet->setSerieDescription("Serie1","Serapan");
        $this->dataSet->setSerieOnAxis("Serie1",0);

        $this->dataSet->addPoints($newmonth,"Absissa");
        $this->dataSet->setAbscissa("Absissa");
        
        $this->dataSet->setAxisPosition(0,AXIS_POSITION_LEFT);
        $this->dataSet->setAxisName(0,"Nilai Anggaran");
        $this->dataSet->setAxisUnit(0,"");
    }
    private function generateImage () {
         /* Add data in your dataset */         
        $this->populateDataset();       

        $myPicture = new pImage(900,230,$this->dataSet);
        $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
        $myPicture->drawFilledRectangle(0,0,900,230,$Settings);

        $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
        $myPicture->drawGradientArea(0,0,900,230,DIRECTION_VERTICAL,$Settings);

        $myPicture->drawRectangle(0,0,899,229,array("R"=>0,"G"=>0,"B"=>0));

        $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

        $myPicture->setFontProperties(array("FontName"=>$this->libFont.'/Forgotte.ttf',"FontSize"=>14));
        $TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE
                            , "R"=>255, "G"=>255, "B"=>255);
        $myPicture->drawText(450,25,"Realisasi Anggaran",$TextSettings);

        $myPicture->setShadow(FALSE);
        $myPicture->setGraphArea(50,50,875,190);
        $myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>$this->libFont.'/pf_arma_five.ttf',"FontSize"=>6));

        $Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
        , "Mode"=>SCALE_MODE_FLOATING
        , "LabelingMethod"=>LABELING_ALL
        , "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
        $myPicture->drawScale($Settings);

        $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

        $Config = "";
        $myPicture->drawSplineChart($Config);
        
        $Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>$this->libFont.'/pf_arma_five.ttf', "FontSize"=>6, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>LEGEND_NOBORDER
        , "Mode"=>LEGEND_HORIZONTAL
        );
        $myPicture->drawLegend(797,16,$Config);
        /* Render the picture (choose the best way) */ 
        $myPicture->autoOutput('DashboardRealisasiChart.png');
    }
}
?>
