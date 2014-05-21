<?php
class Profiles extends MainPage {
	public function onLoad ($param) {		
		parent::onLoad ($param);        
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageProfiles'])||$_SESSION['currentPageProfiles']['page_name']!='d.Profiles') {
                $_SESSION['currentPageProfiles']=array('page_name'=>'d.Profiles','page_num'=>0,'roles'=>'d','search'=>false);												
			}                             
			$this->populateData ();			            
		}
	}    
	protected function populateData ($search=false) {
       $userid=$this->Pengguna->getDataUser('userid');
       $nophoto=$this->setup->getAddress().'/media/user/no_photos.jpg';
       $this->imgProfile->ImageUrl=$this->setup->getAddress()."/media/user/$userid.jpg";
       $this->imgProfile->Attributes->onerror="no_photo(this,'$nophoto')";
	}  
    public function saveConfigurationPassword ($sender,$param) {
        if ($this->IsValid) {
            $data=$this->Pengguna->createHashPassword($this->txtPassword->Text);
            $salt=$data['salt'];
            $password=$data['password'];
            $id=$this->Pengguna->getDataUser('userid');
            $str = "UPDATE user SET userpassword='$password',salt='$salt' WHERE userid=$id";
            $this->DB->updateRecord($str);           
            $this->redirect('d.Profiles');
        }
    }
    public function fileUpload($sender,$param)    {           
        if($sender->HasFile) {
            try {
                if ($sender->FileType!='image/jpeg')
                    throw new Exception ("Type File salah, hanya menerima file JPG");
                $userid=$this->Pengguna->getDataUser('userid');                
                $nama_file=BASEPATH."/media/user/$userid.jpg";                
                $sender->saveAs($nama_file);                                    
                $nophoto=$this->setup->getAddress().'/media/user/no_photos.jpg';
               $this->imgProfile->ImageUrl=$this->setup->getAddress()."/media/user/$userid.jpg";
               $this->imgProfile->Attributes->onerror="no_photo(this,'$nophoto')";       
            }catch (Exception $e) {
                $this->Result->Text = $e->getMessage();
            }
        }
	}
}

?>