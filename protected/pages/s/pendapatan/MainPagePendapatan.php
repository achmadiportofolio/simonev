<?php

class MainPagePendapatan extends MainPage {	
    /**
	* tab target
	*/
	public $showTarget=false;	    
    /**
	* tab uraian
	*/
	public $showRealisasi=false;	
    /**
     *
     * object rekening
     */
    public $rekening;   
	public function onLoad ($param) {		
		parent::onLoad($param);	        
        $this->createObjKegiatan();
        $this->createObjfinance();
        $this->rekening=$this->getLogic('Rekening');
        $this->showPendapatan = true;
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
	}
}
?>