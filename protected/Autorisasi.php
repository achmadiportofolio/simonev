<?php
prado::using('Application.UserManager');
class Autorisasi extends TModule implements IUserManager {	
	/**
	* @return string name for a guest user	
	*/		
	public function getGuestName () {
		return 'Guest';
	}
	
	/**
	* returns a user instance given the username
	* @param string username, null if it is a guest
	* @return TUser the user instance, null if the specified username is not the user database
	*
	*/
	public function getUser ($username=null) {				
		if ($username === null) {
			$user = new TUser ($this);
			$user->setIsGuest(true);
			return $user;
		}else {			
			$user = new TUser ($this);						
			$um = new UserManager();
			$um->setUser($username);			
			$user->setIsGuest (false);						
			$datauser=$um->getDataUser();		            
            switch ($datauser['page']) {
                case 'm' :
                    $roles='manajemen';
                break;
                case 'd' :
                    $roles='dinas';
                break;
                case 's' :
                    $roles='staff';
                break;
                case 't' :
                    $roles='toplevel';
                break;
            }
			$user->setRoles($roles);						
			$user->setName ($datauser);									
			return $user;		
		}
	}
	
	/**
	* validate if the username and password is correct
	* @param string username
	* @param string password
	* @return boolean true if validation is sucessful, false otherwise
	*
	*/
	public function validateUser ($username,$password) {		
		$um = new UserManager();
		$um->setUser($username);
		$result = $um->getUser();        
		$password=hash('sha256', $result['salt'] . hash('sha256', $password));		        
		if (($result['userpassword']==$password))
			return true;
		else
			return false;
	}	
	/**
	* Dua method dibawah ini tidak dipakai. Tetapi harus tetap Ada.
	*
	*/			
	public function saveUserToCookie($cookie) { }
	
	public function getUserFromCookie($cookie) { }
	
	
}
?>