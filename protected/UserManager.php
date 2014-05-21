<?php
class UserManager extends TAuthManager {
	/**
	* Obj DB
	*/
	private $db;		
	/**
	* Username
	*/
	private $username;				
	/**
	* page
	*/
	private $page;
	/**
	* data user
	*/
	private $dataUser=array('data_user'=>array(),'hak_akses'=>array());
	
	public function __construct () {
		$this->db = $this->Application->getModule('db')->getLink();						
	}
		
	/**
	* digunakan untuk mengeset username serta mensplit username dan page	
	*/
	public function setUser ($username) {
		$username = explode('/',$username);
		$this->username=$username[0];
		$this->page=$username[1];				
	}
	/**
	* get roles username	
	*/
	public function getDataUser () {				
		$username=$this->username;		
        $str = "SELECT userid,username,page,user.idunit,kode_unit,nama_unit,active FROM user LEFT JOIN unit ON (unit.idunit=user.idunit) WHERE username='$username'";
        $this->db->setFieldTable (array('userid','username','page','idunit','kode_unit','nama_unit','active'));							
        $r = $this->db->getRecord($str);				
        $dataUser=$r[1];			        
        $this->dataUser['data_user']=$dataUser;			
		return $dataUser;
	}
	/**
	* digunakan untuk mendapatkan data user	
	*/
	public function getUser () {				
        $str = "SELECT userpassword,page,salt FROM user WHERE username='{$this->username}' AND active=1";
        $this->db->setFieldTable (array('userpassword','salt','page'));							
        $r = $this->db->getRecord($str);				
        $result=isset($r[1])?$r[1]:array();		
        return $result;
	}	
}

?>