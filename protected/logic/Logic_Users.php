<?php

prado::using ('Application.logic.Logic_Global');
class Logic_Users extends Logic_Global {
	/**
	* object Themes
	*/
	private $themes;	
	/**
	* object Users
	*/
	private $u;	
	/**
	* Roles
	*/
	private $roles;
	/**
	* Data User
	*/
	private $dataUser;	
	
	public function __construct ($db) {
		parent::__construct ($db);	
		$this->u = $this->User;
		if (method_exists($this->u,'getRoles')) {
			$dataUser=$this->u->getName();	
			if ($dataUser != 'Guest') {
				$this->roles=$this->u->getRoles();			
				$this->dataUser=$dataUser;
			}		
		}				
	}
	/**
	* digunakan untuk membuat hash password
	* @return array
	*/
	public function createHashPassword($password,$salt='',$new=true) {
		if ($new) {
			$salt = substr(md5(uniqid(rand(), true)), 0, 6);	
			$password = hash('sha256', $salt . hash('sha256', $password));
			$data =array('salt'=>$salt,'password'=>$password);			
		}else {
			$data = hash('sha256', $salt . hash('sha256', $password));	
			$data =array('salt'=>$salt,'password'=>$password);		
		}
		return $data;
	}
	/**
	* digunakan untuk mengeset object themes	
	*/	
	public function setThemesObject($themes) {
		$this->themes = $themes;	
	}	
	/**
	* digunakan untuk mendapatkan tipe user
	*/		
	public function getTipeUser () {
		return $this->dataUser['page'];
	}
		
	/**
	* digunakan untuk mendapatkan data user
	*
	* @return datauser
	*/
	public function getDataUser ($id='all') {						
		if ($id=='all')
			return $this->dataUser;
		else
			return $this->dataUser[$id];
	}	
	/**
	* untuk mendapatkan userid dari user
	*
	*/
	public function getUserid () {			
		return $this->dataUser['userid'];		
	}		
	/**
	* untuk mendapatkan username dari user
	*
	*/
	public function getUsername () {		
		return $this->dataUser['username'];
	}
    /**
	* untuk mendapatkan role name dari user
	*
	*/
	public function getRolename ($p=null) {		
        $roles='';
        $page = $p == null ?$this->dataUser['page']:$p;   
        switch ($page) {
            case 'm' :
                $roles='Super Admin';
            break;
            case 'd' :
                $roles='Kasubag Program/Admin';
            break;
            case 's' :
                $roles='User/PPTK';
            break;
            case 't' :
                $roles='Pejabat';
            break;
        }
        return $roles;
	}
	/**
	*
	* digunakan untuk mengecek pages user, bila tidak sesuai maka akan diredirect ke yang tepat
	*
	*/
	public function checkPageUser ($currentPage) {						
		if (isset($this->dataUser['page'])) {										
            if ($currentPage != 'Logout') {
                $page=$this->dataUser['page'];
                $currentPage=explode('.',$currentPage);								                
                if ($currentPage[0] != $page) {					                    
                    $this->redirect($page.'.Home');
                }
            }
		}
	}
	/**
	* digunakan untuk mendapatkan page user.	
	*/
	public function getPageUser () {
		return $this->dataUser['page'];
	}	
	/**
	* digunakan untuk username berdasarkan tipe user
	*/
	public function getUsernameByTipeUser($userid,$page) {		
		$str = "SELECT username FROM user WHERE userid=$userid AND page='$page'";		
		$this->db->setFieldTable(array('username'));
		$r=$this->db->getRecord($str);		
		return $r[1]['username'];
		
	}
}
?>