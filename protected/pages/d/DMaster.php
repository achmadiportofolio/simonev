<?php
prado::using ('Application.pages.d.dmaster.MainPageDMaster');
class DMaster extends MainPageDMaster {
	public function onLoad($param) {		
		parent::onLoad($param);				
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
		}
	}
}
		