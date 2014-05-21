<?php

class MainPageDashboard extends MainPage {	    
    /**
     *
     * object rekening
     */
    public $rekening;   
	public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->showDashboard=true;
        $this->createObjKegiatan();
        $this->createObjfinance();
        $this->rekening=$this->getLogic('Rekening');
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
	}
}
?>