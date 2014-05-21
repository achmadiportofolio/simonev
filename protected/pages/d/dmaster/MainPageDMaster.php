<?php

class MainPageDMaster extends MainPage {	
    
    /**
     *
     * object dmaster
     */
    public $dmaster;    
    /**
	* tab program
	*/
	public $showProgram=false;	    
    /**
	* tab pagu dana
	*/
	public $showPaguDana=false;	
	public function onLoad ($param) {		
		parent::onLoad($param);	
        $this->showDMaster=true;      
        $this->dmaster=$this->getLogic('DMaster');
        if (!$this->IsPostBack&&!$this->IsCallBack) {
            
        }
	}   
}
?>