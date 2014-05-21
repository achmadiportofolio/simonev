<?php
prado::using ('Application.pages.m.dmaster.MainPageDMaster');
class DMaster extends MainPageDMaster {
	public function onLoad($param) {		
		parent::onLoad($param);				
		if (!$this->IsPostBack&&!$this->IsCallBack) {
            
		}
	}
}
		