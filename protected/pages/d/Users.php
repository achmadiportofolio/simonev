<?php
class Users extends MainPage {
	public function onLoad ($param) {		
		parent::onLoad ($param);        
        $this->showUsers=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageUsers'])||$_SESSION['currentPageUsers']['page_name']!='d.Users') {
                $_SESSION['currentPageUsers']=array('page_name'=>'d.Users','page_num'=>0,'search'=>false);												
			}     
            $_SESSION['currentPageUsers']['search']=false;
			$this->populateData ();			
		}
	}
    public function renderCallback ($sender,$param) {
		$this->RepeaterS->render($param->NewWriter);	
	}	
	public function Page_Changed ($sender,$param) {
		$_SESSION['currentPageUsers']['page_num']=$param->NewPageIndex;
		$this->populateData();
	} 
    public function filterRecord ($sender,$param) {
        $_SESSION['currentPageUsers']['search']=true;
        $this->populateData($_SESSION['currentPageUsers']['search']);
    }
    public function changeRoles ($sender,$param) {
        $_SESSION['currentPageUsers']['roles']=$this->cmbRoles->Text;
        $this->populateData();
    }
	protected function populateData ($search=false) {
        $idunit=$this->idunit;
        $this->RepeaterS->CurrentPageIndex=$_SESSION['currentPageUsers']['page_num'];                        
        if ($search) {
            $kriteria=$this->txtKriteria->Text;
            $str = "SELECT userid,username,email,active FROM user WHERE idunit='$idunit' AND username LIKE '%$kriteria%'";                                      
            $jumlah_baris=$this->DB->getCountRowsOfTable ("user WHERE idunit='$idunit' AND username LIKE '%$kriteria%'",'userid');
        }else {            
            $str = "SELECT userid,username,email,active FROM user WHERE idunit='$idunit'"; 
            $jumlah_baris=$this->DB->getCountRowsOfTable ("user WHERE idunit='$idunit'",'userid');	
        }
		$this->RepeaterS->VirtualItemCount=$jumlah_baris;
		$currentPage=$this->RepeaterS->CurrentPageIndex;
		$offset=$currentPage*$this->RepeaterS->PageSize;		
		$itemcount=$this->RepeaterS->VirtualItemCount;
		$limit=$this->RepeaterS->PageSize;
		if (($offset+$limit)>$itemcount) {
			$limit=$itemcount-$offset;
		}
		if ($limit < 0) {$offset=0;$limit=10;$_SESSION['currentPageUsers']['page_num']=0;}
        $str = "$str LIMIT $offset,$limit";        
		$this->DB->setFieldTable(array('userid','username','email','active'));
        $r=$this->DB->getRecord($str);             
       
		$this->RepeaterS->DataSource=$r;
		$this->RepeaterS->dataBind();      		
	}  
    public function checkUsername ($sender,$param) {
		$this->idProcess=$sender->getId()=='addUsername'?'add':'edit';
        $username=$param->Value;		
        if ($username != '') {
            try {   
                if ($this->hiddenusername->Value!=$username) {                    
                    if ($this->DB->checkRecordIsExist('username','user',$username)) {                                
                        throw new Exception ("<span class='error'>Username ($username) sudah tidak tersedia silahkan ganti dengan yang lain.</span>");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function checkEmail ($sender,$param) {
		$this->idProcess=$sender->getId()=='addEmail'?'add':'edit';
        $email=$param->Value;		
        if ($email != '') {
            try {   
                if ($this->hiddenemail->Value!=$email) {                    
                    if ($this->DB->checkRecordIsExist('email','user',$email)) {                                
                        throw new Exception ("<span class='error'>Email ($email) sudah tidak tersedia silahkan ganti dengan yang lain.</span>");		
                    }                               
                }                
            }catch (Exception $e) {
                $param->IsValid=false;
                $sender->ErrorMessage=$e->getMessage();
            }	
        }	
    }
    public function saveData($sender,$param) {		
        if ($this->Page->IsValid) {		
            $username=$this->txtAddUsername->Text;            
            $alamatemail=$this->txtAddAlamatEmail->Text;            
            $page='d';
            $idunit=$this->idunit;
            $data=$this->Pengguna->createHashPassword($this->txtAddPassword->Text);
            $salt=$data['salt'];
            $password=$data['password'];
            $str = "INSERT INTO user (userid,username,userpassword,salt,page,email,idunit,active) VALUES (NULL,'$username','$password','$salt','$page','$alamatemail','$idunit',1)";
            $this->DB->insertRecord($str);
            $this->redirect('d.Users');
        }
	}
    public function editRecord ($sender,$param) {		
		$this->idProcess='edit';
		$id=$this->getDataKeyField($sender,$this->RepeaterS);        
		$this->hiddenuserid->Value=$id;
        $str = "SELECT username,email FROM user WHERE userid='$id'";
        $this->DB->setFieldTable(array('username','email'));
        $r=$this->DB->getRecord($str);    
		$result = $r[1];        				
        $this->hiddenusername->Value=$result['username'];
        $this->hiddenemail->Value=$result['email'];
		$this->txtEditUsername->Text=$result['username'];		
		$this->txtEditAlamatEmail->Text=$result['email'];		                
	}
    public function updateData($sender,$param) {		
        if ($this->Page->IsValid) {		
            $id=$this->hiddenuserid->Value;
            $username=$this->txtEditUsername->Text;            
            $alamatemail=$this->txtEditAlamatEmail->Text;            
            if ($this->txtEditPassword->Text == '') {
                $str = "UPDATE user SET username='$username',email='$alamatemail',active=1 WHERE userid=$id";
            }else {
                $data=$this->Pengguna->createHashPassword($this->txtEditPassword->Text);
                $salt=$data['salt'];
                $password=$data['password'];
                $str = "UPDATE user SET username='$username',userpassword='$password',salt='$salt',email='$alamatemail',active=1 WHERE userid=$id";
            }
            $this->DB->updateRecord($str);           
            $this->redirect('d.Users');
        }
	}
    public function deleteRecord ($sender,$param) {
		$id=$this->getDataKeyField($sender,$this->RepeaterS);
		$this->DB->deleteRecord("user WHERE userid=$id");		
        $this->DB->updateRecord("UPDATE proyek SET userid=0 WHERE userid=$id");
		$this->populateData();		
        $this->redirect('d.Users');
	}
}

?>