<?php

class Logout extends MainPage {		
	public function onLoad ($param) {	
        parent::onLoad($param);	  
        echo 'sd';
		if (!$this->User->isGuest) {			
			$this->Application->getModule ('auth')->logout();			
			$url=$this->Service->constructUrl('Login');
			$this->Response->redirect($url);
		}else {
			$url=$this->Service->constructUrl('Login');
			$this->Response->redirect($url);
		}
	}	
}
?>