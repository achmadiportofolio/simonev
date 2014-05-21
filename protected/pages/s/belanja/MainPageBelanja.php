<?php

class MainPageBelanja extends MainPage {	
    /**
	* tab kegiatan
	*/
	public $showKegiatan=false;	
    /**
	* tab uraian
	*/
	public $showUraian=false;	
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
        $this->showBelanja = true;
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
	}
}
?>