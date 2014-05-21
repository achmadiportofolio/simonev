<?php

class MainPage extends TPage {
    /**
	* session
	*/
	public $session;	
	/**
	* id process
	*/
	public $idProcess;	
    /**
	* kode skpd
	*/
	public $idunit;	
	/**
	* Object Variable "Database"
	*
	*/
	public $DB;	
	/**
	* Object Variable "DMaster"
	*
	*/
	public $DMaster;
    
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
     * object class kegiatan
     */
    public $kegiatan;
	/**
	* Object Variable "User"
	*
	*/
	public $Pengguna;
    /**
     *
     * Object report
     */
    public $report;
     /**
	* tab dashboard
	*/
	public $showDashboard=false;
    /**
	* tab dmaster
	*/
	public $showDMaster=false;	
    /**
	* tab pendapatan
	*/
	public $showPendapatan=false;	
    /**
	* tab belanja
	*/
	public $showBelanja=false;	
    /**
	* tab reports
	*/
	public $showReports=false;	
    /**
	* tab users
	*/
	public $showUsers=false;	
    /**
	* tab setting
	*/
	public $showSetting=false;
     /**
	* tab pejabat
	*/
	public $showPejabat=false;
    /**
	* tab Pengguna Anggaran
	*/
	public $showPenggunaAnggaran=false;	
    /**
	* tab Kuasa Pengguna
	*/
	public $showKuasaPengguna=false;	
    /**
	* tab PPK
	*/
	public $showPPK=false;	
    /**
	* tab PPTK
	*/
	public $showPPTK=false;	        
    /**
	* tab jenis kegiatan
	*/
	public $showJenisPembangunan=false;	
    /**
	* tab dmaster dan report lokasi
	*/
	public $showLokasi=false;	
    /**
	* tab lokasi
	*/
	public $showNegara=false;	
    /**
	* tab DT 2
	*/
	public $showDT1=false;	
    /**
	* tab DT 2
	*/
	public $showDT2=false;	
    /**
	* tab Kecamatan
	*/
	public $showKecamatan=false;	
    /**
	* userid
	*/
	public $userid;	
     /**
	* data kegiatan
	*/
	public $dataKegiatan=array();	
	public function OnPreInit ($param) {	
		parent::onPreInit ($param);
		//instantiasi database		
		$this->DB = $this->Application->getModule ('db')->getLink();		
        $this->MasterClass="Application.layouts.Main";		
		$this->Theme="default";
	}
	public function onLoad ($param) {		
		parent::onLoad($param);		
		$this->session = new THttpSession;
		$this->session->open();			
		
		//instantiasi user
		$this->Pengguna = $this->getLogic('Users');
		$this->Pengguna->checkPageUser($this->Page->getPagePath());						
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
	/**
	* id proses tambah, delete, update,show
	*/
	protected function setIdProcess ($sender,$param) {		
		$this->idProcess=$sender->getId();
	}
	
	/**
	* add panel
	* @return boolean
	*/
	protected function getAddProcess ($disabletoolbars=true) {
		if ($this->idProcess == 'add') {			
			if ($disabletoolbars)$this->disableToolbars();
			return true;
		}else {
			return false;
		}
	}
	
	/**
	* edit panel
	* @return boolean
	*/
	protected function getEditProcess ($disabletoolbars=true) {
		if ($this->idProcess == 'edit') {			
			if ($disabletoolbars)$this->disableToolbars();
			return true;
		}else {
			return false;
		}

	}
	
	/**
	* view panel
	* @return boolean
	*/
	protected function getViewProcess ($disabletoolbars=true) {
		if ($this->idProcess == 'view') {
			if ($disabletoolbars)$this->disableToolbars();			
			return true;
		}else {
			return false;
		}

	}
	
	/**
	* default panel
	* @return boolean
	*/
	protected function getDefaultProcess () {
		if ($this->idProcess == 'add' || $this->idProcess == 'edit'|| $this->idProcess == 'view') {
			return false;
		}else {
			return true;
		}
	}	
    /**
	* disable toolbars
	* @return void
	*/
    protected function disableToolbars() {
        $this->toolbarOptionsTahunAnggaran->Enabled=false;    
        $this->toolbarOptionsBulanRealisasi->Enabled=false;    
    }
	/**
	* digunakan untuk mendapatkan sebuah data key dari repeater
	* @return data key
	*/
	protected function getDataKeyField($sender,$repeater) {
		$item=$sender->getNamingContainer();
		return $repeater->DataKeys[$item->getItemIndex()];
	}
    /**
     * Format NIP
     * @param type $nip integer
    */
    public function nipFormat ($nip) {        
        $formatnip=$nip;        
        if (isset($nip[17])) {             
            $tgllahir=  substr($nip, 0, 8);
            $tmtcpns=  substr($nip, 8, 6);
            $jk=  substr($nip, 14, 1);
            $nourut=substr($nip, 15, 3);
            $formatnip = "$tgllahir $tmtcpns $jk $nourut";
        }       
        return $formatnip;
    }
    /**
	* Redirect
	*/
	protected function redirect ($page,$param=array()) {
		$this->Response->Redirect($this->Service->ConstructUrl($page,$param));	
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
?>