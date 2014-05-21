<?php
/**
*
* digunakan untuk memproses tanggal
*
*/
prado::using ('Application.logic.Logic_Global');
class Logic_Penanggalan extends Logic_Global {	
	protected $dayName, $namaHari, $monthName, $namaBulan;

	public function __construct ($db) {	
		parent::__construct ($db);	
		$this->dayName = array('Sunday', 'Monday', 'Tuesday',
							   'Wednesday', 'Thursday', 'Friday', 'Saturday');
		$this->namaHari = array('Minggu', 'Senin', 'Selasa',
								'Rabu', 'Kamis', 'Jumat', 'Sabtu');
		$this->monthName = array('January', 'February', 'March', 'April', 'May',
								 'June', 'July', 'August', 'September', 'October', 'November' , 'December');
		$this->namaBulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei',
								 '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember');
	}	
	/**
	*
	* tanggal('d/m/y') 03/03/09, 
		tanggal('l, j F Y') selasa, 3 maret 2009
		echo $time->tanggal("l, j F Y");                // Selasa, 3 Maret 2009
		echo $time->tanggal("F j, Y, g:i a");           // Maret 3, 2009, 5:16 pm
		echo $time->tanggal("m.d.y");                   // 03.03.09
		echo $time->tanggal("Ymd");                     // 20090303
		echo $time->tanggal('H:m:s \j\a\m \l\a\h\i\r'); // 17:03:18 jam lahir
	*/
	public function tanggal($format, $date=null) {
        if (is_object($date)){            
            $tgl=$date;
        }else {
            if ($date === null)
                $tgl = new DateTime ('now',new DateTimeZone('Asia/Jakarta'));
            else
                $tgl = new DateTime ($date,new DateTimeZone('Asia/Jakarta'));
        }		
        $result = str_replace($this->dayName, $this->namaHari, $tgl->format ($format));
        return str_replace($this->monthName, $this->namaBulan, $result);
	}   
	/**	
	* dapatkan daftar bulan
	* @return daftar bulan
	*/
	public function getMonth ($mode=0,$no_bulan='') {
		switch ($mode) {
			case 1 :
				$bulan['none']=' ';	
                foreach ($this->namaBulan as $k=>$v) {			
                    $bulan[$k]=$v;
                }
			break;
			case 2 :
				$bulan['all']='All';	
                foreach ($this->namaBulan as $k=>$v) {			
                    $bulan[$k]=$v;
                }
			break;
            case 3 :
                $bulan=$this->namaBulan;
            break;
            case 4 :
                $bulan=$this->namaBulan[$no_bulan];
            break;
		}		
		return $bulan;
	}
	/**	
	* dapatkan daftar tahun
	* @return daftar tahun
	*/
	public function getYear ($mode=0) {
		if ($mode == 1) {
			$year['none']=' ';
		}
		for ($i=2000;$i <= date ('Y');$i++) {
			$year[$i]=$i;
		}			
		return $year;
	}
	
	/**	
	* dapatkan daftar bulan
	* @return daftar bulan
	*/
	public function getYearTA ($tahun,$mode=0) {
		switch ($mode) {
			case 1 :
				$year['none']=' ';	
			break;
			case 2 :
				$year['all']='All';	
			break;
		}
		$year[$tahun]=$tahun;
		$year[$tahun+1]=$tahun+1;
// 		print_r($year);
		return $year;		
	}
	/**
	* mendapatkan jumlah hari pada bulan tertentu
	* 
	* 
	*/	
	public function getDay ($option='') {
		$jumlah_hari = 31;
		if (strtolower($option) == 'all') {
			$no_hari['all']='All';			
		}
		for ($i =  1; $i <= $jumlah_hari; $i++) {
			if ($i < 10) {
				$no_='0'.$i;
			}else {
				$no_=$i;
			}
			$no_hari[$no_]=$no_;
		}
		return $no_hari;
	}	
	/**
	* memformat tanggal indonesia-ingris
	* untuk mysql
	*
	*/
	public function tukarTanggal ($id,$direction='idtoen') {		
		if ($direction == 'entoid') {						
			$id=explode('-',$id);
			$tgl=$id[2].'-'.$id[1].'-'.$id[0];
			return $tgl;
		}else {						
			$id=explode('-',$id);
			$tgl=$id[2].'-'.$id[1].'-'.$id[0];
			return $tgl;
		}
	}
	/**
	* digunakan untuk mendapatkan nama hari
	*/
	public function getNamaHari ($id='all') {
		if ($id=='all') 
			return $this->namaHari;
		else
			return $this->namaHari[$id];
	}
    private function pluralize( $count, $text )  {
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}" ) );
    }
	/**
	* digunakan untuk mengetahui perbedaan waktu ke waktu
	*/
	public function relativeTimeBasic($date1,$date2,$mode='all')	{		      
        $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Jakarta'));
        $datetime2 = new DateTime($date2, new DateTimeZone('Asia/Jakarta'));
        $interval = $datetime1->diff($datetime2);   
		$tanggal=array();
		switch ($mode) {
			case 'tahun' :
				$tanggal=$interval->y;
			break;						
			case 'bulan' :
				$tanggal=$interval->m;
			break;
			case 'tahunbulan' :
				$tanggal=array('tahun'=>$interval->y,'bulan'=>$interval->m);
			break;
		}        
        return $tanggal;
	}
	/**
	* digunakan untuk mengetahui perbedaan waktu
	*/
	public function relativeTime($date1,$date2,$mode='all')	{		              
        $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Jakarta'));
        $datetime2 = new DateTime($date2, new DateTimeZone('Asia/Jakarta'));
        $interval = $datetime1->diff($datetime2);   
		$tanggal='';
		switch ($mode) {
			case 'tahunbulan' :
				if ($interval->y >= 1 || $interval->m >= 1) {
					if ($interval->y >= 1 ) 
						$tanggal=$this->pluralize( $interval->y, 'Tahun ');
					if ($interval->m >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->m, 'Bulan ');
				}
			break;			
            case 'lastlogin' :                
                if ($interval->y > 1) {
                    $tanggal='Lebih dari 1 Tahun';
                }elseif ($interval->i > 1) {
                    if ($interval->m > 1) 
                        $tanggal=$this->pluralize( $interval->m, 'Bulan ');
                    if ($interval->d >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->d, 'Hari ' );
                    if ($interval->h >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->h, 'Jam ' );
                    if ($interval->i >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->i, 'Menit ' );
                    
                }else {
                    $tanggal='Kurang dari 1 Menit';
                }
            break;
            case 'lasttweet' :                
                if ($interval->i > 1) {                                                            
                    if ($interval->d <= 1) {
                        if ($interval->h >= 1 ) 
                            $tanggal=$tanggal.$this->pluralize( $interval->h, 'Jam ' );
                        if ($interval->i >= 1 ) 
                            $tanggal=$tanggal.$this->pluralize( $interval->i, 'Menit ' );                    
                    }else {
                        $result = str_replace($this->dayName, $this->namaHari, $datetime2->format ('d F Y'));
                        return str_replace($this->monthName, $this->namaBulan, $result);
                    }
                }else {
                    $tanggal='Kurang dari 1 Menit';
                }
            break;
			default :
				if ($interval->y >= 1 || $interval->m >= 1 || $interval->d >= 1 ) {          
					if ($interval->y >= 1 ) 
						$tanggal=$this->pluralize( $interval->y, 'Tahun ');
					if ($interval->m >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->m, 'Bulan ');
					if ($interval->d >= 1 ) 
						$tanggal=$tanggal.$this->pluralize( $interval->d, 'Hari' );
				}else {
					$tanggal='Kurang dari 1 Hari';
				}
		}
        
        return $tanggal;
	}   
}
?>