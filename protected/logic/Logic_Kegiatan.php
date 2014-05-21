<?php
/**
*
* digunakan untuk memproses Data Master urusan,satuan kerja, 
* dan program
*
*/
prado::using ('Application.logic.Logic_Global');
class Logic_Kegiatan extends Logic_Global {	    
	/**
	*idproyek 
	*/
	private $idproyek;	
    /**
	*userid
	*/
	private $userid=null;
	/**
	* data kegiatan
	*
	*/
	public $dataKegiatan=array();
    /**
	* rekening beserta nilai data kegiatan
	*
	*/
	protected $dataRekeningProyek=array();
	/**
     * Jenis kegiatan      
     */
    private $SifatKegiatan=array('baru'=>'BARU','lanjutan'=>'LANJUTAN');  
    
    private $JenisPelaksanaan=array('none'=>' ','plfisik'=>'PL Fisik','plperencanaan'=>'PL Perencanaan','plpengawasan'=>'PL Pengawasan','plpengadaan'=>'PL Pengadaan','lelangfisik'=>'Lelang Fisik','lelangperencanaan'=>'Lelang Perencanaan','lelangpengawasan'=>'Lelang Pengawasan','lelangpengadaan'=>'Lelang Pengadaan');
    
	public function __construct ($db) {
		parent::__construct ($db);	        
	}
    public function setUserid ($userid) {
        $this->userid = $userid;
    }
    /**
	* digunakan untuk mendapatkan sifat-sifat kegiatan
	*/
    public function getSifatKegiatan () {
        return $this->SifatKegiatan;
    }
    /**
	* digunakan untuk mendapatkan jenis kegiatan
	*/
    public function getJenisKegiatan () {
        $result=$this->getList('jeniskegiatan',array('idjenis_kegiatan','nama_jenis'),'nama_jenis',null,5);
        return $result;
    }
    /**
	* digunakan untuk mendapatkan jenis pelaksanaan
	*/
    public function getJenisPelaksanaan($id=null) {
        if ($id === null)
            return $this->JenisPelaksanaan;
        else
            return $this->JenisPelaksanaan[$id];
    }
    /**
	* digunakan untuk mendapatkan jenis pembangunan
	*/
    public function getJenisPembangunan($id=null,$mode=5) {
        if ($id === null) {
            return $this->getList('jenispembangunan',array('idjenis_pembangunan','nama_jenis'),'nama_jenis',null,$mode);
        }else{
            $data=$this->getList("jenispembangunan WHERE idjenis_pembangunan=$id",array('nama_jenis'),'nama_jenis',null,0);                        
            return $data[1]['nama_jenis'];
        }
        
    }
	/**
	* digunakan untuk mendapatkan kode program
	*/
	public function getKodeProgramByID ($idprogram) {
		$str = "SELECT kode_program FROM program WHERE idprogram='$idprogram'";
		$this->db->setFieldTable(array('kode_program'));				
		$result = $this->db->getRecord($str);
		return $result[1]['kode_program'];
	}
	/**
	* id proyek 
	* @param idproyek	
	*/
	public function setIdProyek ($idproyek,$sisa_pagu=false,$mode=1) {
        $str_userid = $this->userid===null ?'':" AND userid={$this->userid}";
		$this->idproyek = $idproyek;
        switch($mode) {
            case 1 :            
                $str = "SELECT idproyek,kode_proyek,nama_proyek,nilai_pagu,userid,enabled FROM proyek WHERE idproyek='".$this->idproyek."'$str_userid";
                $this->db->setFieldTable(array('idproyek','kode_proyek','nama_proyek','nilai_pagu','userid','enabled'));				
                $r=$this->db->getRecord($str);
                if (isset($r[1])) {
                    $this->dataKegiatan=$r[1];
                    if ($sisa_pagu) {
                        $this->dataKegiatan['sisa_nilai_pagu']=$this->getSisaNilaiPagu ();
                    }			
                }
            break;
            case 2 :
                $str = "SELECT idproyek,nama_unit,nama_program,nama_proyek,keluaran,hasil,sifat_kegiatan,waktu_pelaksanaan,nilai_pagu,userid,enabled FROM v_datamaster WHERE idproyek='".$this->idproyek."'$str_userid";
                $this->db->setFieldTable(array('idproyek','nama_unit','nama_program','nama_proyek','keluaran','hasil','sifat_kegiatan','waktu_pelaksanaan','nilai_pagu','userid','enabled'));				
                $r=$this->db->getRecord($str);
                if (isset($r[1])) {
                    $this->dataKegiatan=$r[1];
                    if ($sisa_pagu) {
                        $this->dataKegiatan['sisa_nilai_pagu']=$this->getSisaNilaiPagu ();
                    }			
                }
            break;
        }		
	}
    /**
     * digunakna untuk mendapatkan data proyek
     * @param type $id
     * @return type
     */
	public function getDataProyek ($id=null) {
        if ($id===null) 
            return $this->dataKegiatan;
        else
            return $this->dataKegiatan[$id];
    }
	/**
	* dapatkan lokasi proyek berdasarkan jenjang lokasi beserta id dari tiap lokasi=> kec=>dt2=>dt1=>negara
	*
	*/
	public function getAllLokasiOnlyId () {
        $idproyek=isset($this->dataKegiatan['idproyek'])?$this->dataKegiatan['idproyek']:$this->idProyek;
		$str = "SELECT idlok,ket_lok FROM proyek WHERE idproyek='$idproyek'";
		$this->db->setFieldTable(array('idlok','ket_lok'));
		$r=$this->db->getRecord($str);
		$r=$r[1];			
		
		$idlok=$r['idlok'];
		switch ($r['ket_lok']) {
			case 'negara' :
				$sql = "SELECT idnegara FROM negara WHERE idnegara='$idlok'";
				$field=array('idnegara');				
			break;
			case 'dt1' :
				$field=array('idnegara','iddt1');
				$sql="SELECT dt1.idnegara,dt1.iddt1 FROM dt1 WHERE dt1.iddt1='$idlok'";								
			break;
			case 'dt2' :
				$field=array('idnegara','iddt1','iddt2');
				$sql="SELECT dt1.idnegara,dt1.iddt1,dt2.iddt2 FROM dt2,dt1 WHERE dt2.iddt1=dt1.iddt1 AND dt2.iddt2='$idlok'";	
			break;
			case 'kec' :
				$field=array('idnegara','iddt1','iddt2','idkecamatan');
				$sql="SELECT dt1.idnegara,dt1.iddt1,dt2.iddt2,kec.idkecamatan FROM kecamatan kec,dt2,dt1 WHERE kec.iddt2=dt2.iddt2 AND dt2.iddt1=dt1.iddt1 AND kec.idkecamatan='$idlok'";	
			break;
		}	
		$this->db->setFieldTable($field);	
		$r=$this->db->getRecord($sql);
		return $r[1];
	}
    /**
	* dapatkan lokasi uraian berdasarkan jenjang lokasi beserta id dari tiap lokasi=> kec=>dt2=>dt1=>negara
	*
	*/
	public function getAllLokasiOnlyId2 ($idlok,$ket_lok) {        
		switch ($ket_lok) {
			case 'negara' :
				$sql = "SELECT idnegara FROM negara WHERE idnegara='$idlok'";
				$field=array('idnegara');				
			break;
			case 'dt1' :
				$field=array('idnegara','iddt1');
				$sql="SELECT dt1.idnegara,dt1.iddt1 FROM dt1 WHERE dt1.iddt1='$idlok'";								
			break;
			case 'dt2' :
				$field=array('idnegara','iddt1','iddt2');
				$sql="SELECT dt1.idnegara,dt1.iddt1,dt2.iddt2 FROM dt2,dt1 WHERE dt2.iddt1=dt1.iddt1 AND dt2.iddt2='$idlok'";	
			break;
			case 'kec' :
				$field=array('idnegara','iddt1','iddt2','idkecamatan');
				$sql="SELECT dt1.idnegara,dt1.iddt1,dt2.iddt2,kec.idkecamatan FROM kecamatan kec,dt2,dt1 WHERE kec.iddt2=dt2.iddt2 AND dt2.iddt1=dt1.iddt1 AND kec.idkecamatan='$idlok'";	
			break;
		}	
		$this->db->setFieldTable($field);	
		$r=$this->db->getRecord($sql);
		return $r[1];
	}
    private function getKeteranganLokasi ($ket_lok,$idlok) {
		switch ($ket_lok) {
			case 'negara' :
			case 'neg' :
				$this->db->setFieldTable(array('nama_negara'));
				$sql="SELECT nama_negara FROM negara WHERE idnegara='$idlok'";				
			break;
			case 'dt1' :
				$this->db->setFieldTable(array('nama_dt1','nama_negara'));
				$sql="SELECT dt1.nama_dt1,n.nama_negara FROM dt1,negara n WHERE dt1.idnegara=n.idnegara AND dt1.iddt1='$idlok'";	
			break;
			case 'dt2' :
				$this->db->setFieldTable(array('nama_dt2','nama_dt1','nama_negara'));
				$sql="SELECT dt2.nama_dt2,dt1.nama_dt1,n.nama_negara FROM dt2,dt1,negara n WHERE dt2.iddt1=dt1.iddt1 AND dt1.idnegara=n.idnegara AND dt2.iddt2='$idlok'";	
			break;
			case 'kecamatan' :
			case 'kec' :
				$this->db->setFieldTable(array('nama_kecamatan','nama_dt2','nama_dt1','nama_negara'));
				$sql="SELECT kec.nama_kecamatan,dt2.nama_dt2,dt1.nama_dt1,n.nama_negara FROM kecamatan kec,dt2,dt1,negara n WHERE kec.iddt2=dt2.iddt2 AND dt2.iddt1=dt1.iddt1 AND dt1.idnegara=n.idnegara AND kec.idkecamatan='$idlok'";	
			break;
		}
		return 	$sql;
	}    
    /**
	* digunakan untuk mendapatkan lokasi dari sebuah proyek	
	*/
	public function getLokasiProyek ($idproyek=null,$mode='proyek',$idlok=null,$ket_lok=null) {		
		if (($idproyek != '' &&  $idproyek != 'none')||$idproyek===null) {
			switch ($mode) {
				case 'proyek' :
					$str = "SELECT idlok,ket_lok FROM proyek WHERE idproyek='$idproyek'";
					$this->db->setFieldTable(array('idlok','ket_lok'));
					$r=$this->db->getRecord($str);
					$sql = $this->getKeteranganLokasi($r[1]['ket_lok'],$r[1]['idlok']);
					$r = $this->db->getRecord($sql);				
				break;
				case 'lokasi' :
					$sql = $this->getKeteranganLokasi($ket_lok,$idlok);
					$r = $this->db->getRecord($sql);					
				break;		
			}
			if (isset($r[1])){
				$r=$r[1];
				$jumlah=count($r)-1;
				$i=0;
				foreach ($r as $k=>$v) {				
					if ($k != 'no') {
// 						echo $v . "<Br>";
						if ($jumlah == $i)
							$lokasi .= $v; 
						else
							$lokasi .= $v . ','; 
					}
					$i++;
				}
				return $lokasi;
			}else {
				return ' ';
			}
		}else {
			return ' ';
		}
	}
	/**
     * 
     * @param type $iduraian
     * @param type $mode sudah atau belum (null=sudah)
     * @return type
     */
	public function getMonthRealisasi($iduraian,$mode=null) {
		$bulan=$this->getLogic('Penanggalan')->getMonth(3);
        $str = "SELECT bulan FROM penggunaan WHERE iduraian=$iduraian ORDER BY bulan ASC";
        $this->db->setFieldTable(array('bulan'));	
		$r=$this->db->getRecord($str);        
        $month=$bulan;
        if (isset($r[1])) {                  
            if ($mode === null) {                
                while (list($k,$v)=each($r)) {
                    $temp[$v['no_bulan']]=$month[$v['no_bulan']];                    
                }
                $month=$temp;
            }else {                
                foreach ($r as $n) {                        
                    $bulan_temp[$n['bulan']]=$n['bulan'];
                }                 
                while (list($k,$v)=each($bulan)) {                    
                    if (!in_array($k,$bulan_temp)) {                    
                        $temp[$k]=$v;
                    }
                }
                $month=$temp;                
            }
        }        
        
        return $month;
		
	}
	/**
     * sisa nilai pagu
     * @return type
     */
	
	public function getSisaNilaiPagu () {		
        $idproyek=$this->dataKegiatan['idproyek'];
		$str = "SELECT SUM(nilai) AS sisa FROM uraian WHERE idproyek='$idproyek'";
		$this->db->setFieldTable(array('sisa'));	
		$r=$this->db->getRecord($str);		
		$sisa=$this->dataKegiatan['nilai_pagu']-$r[1]['sisa'];
		return $sisa;		
	}
    /**
     * total target
     * @param type $iduraian
     * @param type $mode
     */
    public function getTotalTarget($iduraian) {
        $str="SELECT SUM(target) AS totalTarget FROM penggunaan p WHERE p.iduraian='$iduraian'";
        $this->db->setFieldTable(array('totalTarget'));		
        $result=$this->db->getRecord($str);
        return $result[1]['totalTarget'];
    }
    /**
     * total target
     * @param type $iduraian
     * @param type $mode
     */
    public function getTotalFisik($iduraian) {
        $str="SELECT SUM(fisik) AS totalfisik FROM penggunaan p WHERE p.iduraian='$iduraian'";
        $this->db->setFieldTable(array('totalfisik'));		
        $result=$this->db->getRecord($str);
        return $result[1]['totalfisik'];
    }   
    /**
     * total realisasi
     * @param type $iduraian
     * @param type $mode
     */
    public function getTotalRealisasi($iduraian=null,$idproyek=null,$bulan=null,$mode='uraian') {
        $totalrealisasi=0;
        switch ($mode) {
            case 'uraian' :
                $str="SELECT SUM(realisasi) AS totalRealisasi FROM penggunaan p WHERE p.iduraian='$iduraian'";
                $this->db->setFieldTable(array('totalRealisasi'));		
                $result=$this->db->getRecord($str);                
                if (isset($result[1])) $totalrealisasi=$result[1]['totalRealisasi'];                
            break;
            case 'proyek' :
                $str = "SELECT (SELECT SUM(realisasi) FROM v_laporan_a WHERE idproyek=$idproyek AND bulan_penggunaan='$bulan') AS realisasi_bulan_ini,(SELECT SUM(realisasi) FROM v_laporan_a WHERE idproyek=$idproyek AND bulan_penggunaan<='$bulan') AS total_realisasi_satu_proyek";
                $this->db->setFieldTable(array('realisasi_bulan_ini','total_realisasi_satu_proyek'));
                $result=$this->db->getRecord($str);                        
                if (isset($result[1])) {
                    $totalrealisasi=$result[1];
                }
                    
            break;
        }
        return $totalrealisasi;
    } 
    /**
	* digunakan untuk mendapatkan rekening beserta nilai realisasinya dari sebuah proyek
	*	
	*/	
	public function getRekeningDataProyek () {
		$no_bulan=$this->dataKegiatan['bulanrealisasi'];
		$tahun=$this->dataKegiatan['tahun'];
		$idproyek=$this->dataKegiatan['idproyek'];			
		$this->db->setFieldTable (array('no_rek1','nama_rek1','no_rek2','nama_rek2','no_rek3','nama_rek3','no_rek4','nama_rek4','no_rek5','nama_rek5','idproyek','kode_proyek','nama_proyek','nilai_pagu','iduraian','nama_uraian','satuan','nilai','realisasi','target','bulan_penggunaan','tahun_penggunaan','tahun_anggaran'));
		$str = "SELECT no_rek1,nama_rek1,no_rek2,nama_rek2,no_rek3,nama_rek3,no_rek4,nama_rek4,no_rek5,nama_rek5,idproyek,kode_proyek,nama_proyek,nilai_pagu,iduraian,nama_uraian,satuan,nilai,realisasi,target,bulan_penggunaan,tahun_penggunaan,tahun_anggaran FROM v_laporan_a WHERE bulan_penggunaan='$no_bulan' AND tahun_penggunaan='$tahun' AND idproyek='$idproyek' ORDER BY no_rek5 ASC";
		$result = $this->db->getRecord ($str); 			
		$dataAkhir=array();		
		if (isset($result[1])) {														
			foreach ($result as $de) {
				$no_rek5=trim($de['no_rek5']);											
				if (array_key_exists ($no_rek5,$dataAkhir) ) {											
					$dataAkhir[$no_rek5]['nilai']+=$de['nilai'];					
					$dataAkhir[$no_rek5]['target']+=$de['target'];
					$dataAkhir[$no_rek5]['realisasi']+=$de['realisasi'];										
				}else {											
					$dataAkhir[$no_rek5]=array ("no_rek1"=>$de['no_rek1'],
	 											"nama_rek1"=>$de['nama_rek1'],
				 								"no_rek2"=>$de['no_rek2'],
												"nama_rek2"=>$de['nama_rek2'],
				 								"no_rek3"=>$de['no_rek3'],
												"nama_rek3"=>$de['nama_rek3'],
			 									"no_rek4"=>$de['no_rek4'],
												"nama_rek4"=>$de['nama_rek4'],
			 									"no_rek5"=>$de['no_rek5'],
												"nama_rek5"=>$de['nama_rek5'],
												"nilai"=>$de['nilai'],												
												"realisasi"=>$de['realisasi'],
												"target"=>$de['target']);
				}
			}			
		}
        $this->dataRekeningProyek = $dataAkhir;			
        return $dataAkhir;
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
     * digunakan untuk mendapatkan daftar unit kerja
     */
    public function getListUnitKerja () {
        if ($this->Application->Cache) {            
            $dataitem=$this->Application->Cache->get('listunitkerja');            
            if (!isset($dataitem['none'])) {
                $dataitem=$this->getList('unit',array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);
                $dataitem['none']='-------------- Seluruh Unit Kerja --------------';    
                $this->Application->Cache->set('listunitkerja',$dataitem);
            }
        }else {                        
            $dataitem=$this->getList('unit',array('idunit','kode_unit','nama_unit'),'kode_unit',null,2);
            $dataitem['none']='-------------- Seluruh Unit Kerja --------------';            
        }
        return $dataitem;        
    }
}
?>