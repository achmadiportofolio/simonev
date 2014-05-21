<?php

class Home extends MainPage {
	public function onLoad($param) {		
		parent::onLoad($param);	
        $this->showDashboard=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
		}
	}
}
		