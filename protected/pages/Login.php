<?php
class Login extends MainPage {
    public function onLoad ($param) {		
		parent::onLoad($param);	                
    }
    private function checkUsernameAndPassword($username,$userpassword) {		
		$auth = $this->Application->getModule ('auth');					
		if ($auth->login ($username,$userpassword)){			
			return true;			
		}else {
			throw new Exception ('<br />Username atau password salah!.Silahkan ulangi kembali');						
		}
	}
    public function doLogin ($sender,$param) {
        if ($this->IsValid) {
            try {
                $username=addslashes(trim($this->txtUsername->Text));
                $userpassword=addslashes(trim($this->txtPassword->Text));
                $this->checkUsernameAndPassword($username,$userpassword);
                $pengguna=$this->getLogic('Users');	                
                $_SESSION['ta']=date('Y');
                $_SESSION['bulanrealisasi']=date('m');               
                $page=$pengguna->getDataUser('page');
                $pengguna->redirect("$page.Home");
            }catch (Exception $e) {				
				$this->errormessage->Text=$e->getMessage();					
				$param->IsValid=false;		
			}
        }
    }
}

?>
