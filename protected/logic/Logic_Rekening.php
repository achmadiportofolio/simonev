<?php
prado::using ('Application.Logic.Logic_Global');
class Logic_Rekening extends Logic_Global {			
	public function __construct ($db) {
		parent::__construct ($db);	
	}	
    /**
	* digunakan untuk mendapatkan daftar kelompok [rek2]
	*
	*/
	public function getListKelompok ($no_rek1=null,$mode=7) {
		$fieldTable = array('no_rek2','nama_rek2');
		if ($no_rek1 !== null) {
			$result = $this->getList("rek2 WHERE no_rek1='$no_rek1'",$fieldTable,'no_rek2',null,$mode);
			return $result;
		}else {
			$result = $this->getList("rek2",$fieldTable,'no_rek2',null,$mode);
			return $result;
		}
	}
	
	/**
	* digunakan untuk mendapatkan daftar Jenis [rek3]
	*
	*/
	public function getListJenis ($no_rek2=null,$mode=7) {
		$fieldTable = array('no_rek3','nama_rek3');
		if ($no_rek2 !== null) {
			$result = $this->getList("rek3 WHERE no_rek2='$no_rek2'",$fieldTable,'no_rek3',null,$mode);
			return $result;
		}else {
			$result = $this->getList("rek3",$fieldTable,'no_rek3',null,$mode);
			return $result;
		}
	}
	
	/**
	* digunakan untuk mendapatkan daftar Objek [rek4]
	*
	*/
	public function getListObjek ($no_rek3=null,$mode=7) {
		$fieldTable = array('no_rek4','nama_rek4');
		if ($no_rek3 !== null) {
			$result = $this->getList("rek4 WHERE no_rek3='$no_rek3'",$fieldTable,'no_rek4',null,$mode);
			return $result;
		}else {
			$result = $this->getList("rek4",$fieldTable,'no_rek4',null,$mode);
			return $result;
		}
	}
	
	/**
	* digunakan untuk mendapatkan daftar Rincian [rek5]
	*
	*/
	public function getListRincian ($no_rek4=null,$mode=7) {
		$fieldTable = array('no_rek5','nama_rek5');
		if ($no_rek4 !== null) {
			$result = $this->getList("rek5 WHERE no_rek4='$no_rek4'",$fieldTable,'no_rek5',null,$mode);
			return $result;
		}else {
			
		}
	}
    /**
     * digunakna untuk mendapatkan kode rekening terakhir
     */
    public function getKodeRekeningTerakhir($rek) {
        $rekening=explode('.',$rek);
        $countarray=count ($rekening);
        $account=false;
        if ($countarray > 0)
            $account=$rekening[$countarray-1];
        return $account;
    }
    
}
?>