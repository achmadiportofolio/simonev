<?php

class LogicFactory extends TModule {	
	/**
	*
	* objek db
	*/
	private $db;
	
	public function init ($config) {
		$this->db = $this->Application->getModule ('db')->getLink();	
	}			
	
	/**
	* get instance of 
	*
	*/
	public function getInstanceOfClass ($className) {		
		switch ($className) {
			case 'Users' :
				prado::using ('Application.logic.Logic_Users');
				return new Logic_Users ($this->db);
			break;				
			case 'Penanggalan' :
				prado::using ('Application.logic.Logic_Penanggalan');
				return new Logic_Penanggalan ($this->db);
			break;
            case 'DMaster' :
				prado::using ('Application.logic.Logic_DMaster');
				return new Logic_DMaster ($this->db);
			break;
            case 'Finance' :
				prado::using ('Application.logic.Logic_Finance');
				return new Logic_Finance ($this->db);
			break;
            case "Kegiatan" :
				prado::using ('Application.logic.Logic_Kegiatan');
				return new Logic_Kegiatan ($this->db);
			break;
            case "Rekening" :
				prado::using ('Application.logic.Logic_Rekening');
				return new Logic_Rekening($this->db);
			break;
            case 'Report' :
				prado::using ('Application.logic.Logic_Report');
				return new Logic_Report ($this->db);
			break;
			case 'Setup' :
				prado::using ('Application.logic.Logic_Setup');
				return new Logic_Setup ($this->db);
			break;
			default :
				throw new Exception ("Logic_Factory.php :: $className tidak di ketahui");
		}
	}
}
?>