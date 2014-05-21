<?php
class Setting extends MainPage {
	public function onLoad ($param) {		
		parent::onLoad ($param);       
        $this->showSetting=true;
		if (!$this->IsPostBack&&!$this->IsCallBack) {	
            if (!isset($_SESSION['currentPageSetting'])||$_SESSION['currentPageSetting']['page_name']!='m.Setting') {
                $_SESSION['currentPageSetting']=array('page_name'=>'m.Setting','page_num'=>0);												
			}         
            $tanggal=$this->TGL->getDay();           
            $this->setup->getSettingValue('config_tanggal_mulai_input');
            $this->cmbTanggalMulai->DataSource=$tanggal;
            $this->cmbTanggalMulai->Text=$this->setup->getSettingValue('config_tanggal_mulai_input');
            $this->cmbTanggalMulai->dataBind();
            $this->cmbTanggalSelesai->DataSource=$tanggal;
            $this->cmbTanggalSelesai->Text=$this->setup->getSettingValue('config_tanggal_selesai_input');
            $this->cmbTanggalSelesai->dataBind();
		}
	}    
    public function saveWaktuInput ($sender,$param) {
        if ($this->IsValid) {
            $tanggalmulai=$this->cmbTanggalMulai->Text;
            $tanggalselesai=$this->cmbTanggalSelesai->Text;
            $this->DB->updateRecord("UPDATE setting SET value='$tanggalmulai' WHERE setting_id=1");
            $this->DB->updateRecord("UPDATE setting SET value='$tanggalselesai' WHERE setting_id=2");
            $this->setup->loadSetting(true); 
            $this->redirect('m.Setting');
        }
    }
    public function clearCache ($sender,$param) {
        if ($this->Application->Cache) {
            $this->Application->Cache->flush();           
            $this->message->Text='Cache cleared';            
        }
    }   
}

?>