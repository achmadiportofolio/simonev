<?php
prado::using('Application.lib.jpgraph.jpgraph');
class ChartPage extends TPage {
    /* Create dataset object 
     * 
    */ 
    protected $dataSet;
    /**
	* session
	*/
	public $session;
    /**
	* Object Variable "Database"
	*
	*/
	public $DB;
    /**
	* Object Variable "Setup"
	*
	*/
	public $setup;		
	/**
	* Object Variable "Tanggal"
	*
	*/
	public $TGL;	
    /**
	* Object Variable "Finance"
	*
	*/
	public $finance;
    /**
	* Object Variable "User"
	*
	*/
	public $Pengguna;
    /**     
     * object class kegiatan
     */
    public $kegiatan;
    /**
	* kode skpd
	*/
	public $idunit;
    /**
	* userid
	*/
	public $userid;	 
    public function OnPreInit ($param) {	
		parent::onPreInit ($param);              
        //instantiasi database		
		$this->DB = $this->Application->getModule ('db')->getLink();
    }
    public function onLoad ($param) {		
		parent::onLoad($param);		
		$this->session = new THttpSession;
		$this->session->open();  
        
        //instantiasi user
		$this->Pengguna = $this->getLogic('Users');
        //instantiasi fungsi tanggal
		$this->TGL = $this->getLogic ('Penanggalan');
        //instantiasi fungsi setup        
        $this->setup = $this->getLogic('Setup');        
        //kode skpd setup
        $this->idunit=$this->Pengguna->getDataUser('idunit');        
        //userid
        $this->userid = $this->Pengguna->getDataUser ('userid');
    }
    /**
	* mendapatkan lo object
	* @return obj	
	*/
	public function getLogic ($_class=null) {
		if ($_class === null)
			return $this->Application->getModule ('logic');
		else 
			return $this->Application->getModule ('logic')->getInstanceOfClass($_class);	
	}
    public function createObjFinance () {
		$this->finance = $this->getLogic('Finance');
	}
    public function createObjKegiatan() {
		$this->kegiatan = $this->getLogic('Kegiatan');
	}
    public function createObjReport() {
		$this->report=$this->getLogic('Report');
	}
}

class FormattingGraph {
    static public function toRupiah ($number) {
        return number_format($number);
    }
}
?>
