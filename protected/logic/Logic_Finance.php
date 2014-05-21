<?php

class Logic_Finance extends Logic_Global {	
    private $dasar = array(1 => 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam','tujuh', 'delapan', 'sembilan');
    private $angka = array(1000000000000,1000000000, 1000000, 1000, 100, 10, 1);
    private $satuan = array('triliun','milyar', 'juta', 'ribu', 'ratus', 'puluh', '');
	public function __construct ($db) {
		parent::__construct ($db);	
	}		
	/**
	* casting ke integer	
	*/
	public function toInteger ($stringNumeric) {
		return str_replace('.','',$stringNumeric);
	}
	/**
	* Untuk mendapatkan uang dalam format rupiah
	* @param angka	
	* @return string dalam rupiah
	*/
	public function toRupiah($angka,$tanpa_rp=true)  {
		if ($angka == '') {
			$angka=0;
		}
		$rupiah='';
		$rp=strlen($angka);
		while ($rp>3){
			$rupiah = ".". substr($angka,-3). $rupiah;
			$s=strlen($angka) - 3;
			$angka=substr($angka,0,$s);
			$rp=strlen($angka);
		}
		if ($tanpa_rp) {
			$rupiah = $angka . $rupiah;
		}else {
			$rupiah = "Rp. " . $angka . $rupiah;
		}
		return $rupiah;
	}
    public function baca($n) {
        $this->dasar = array(1 => 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam','tujuh', 'delapan', 'sembilan');
        $this->angka = array(1000000000, 1000000, 1000, 100, 10, 1);
        $this->satuan = array('milyar', 'juta', 'ribu', 'ratus', 'puluh', '');

        $i = 0;
        if($n==0){
            $str = "nol";
        }else{
            while ($n != 0) {
                $count = (int)($n/$this->angka[$i]);
                if ($count >= 10) {
                    $str .= $this->baca($count). " ".$this->satuan[$i]." ";
                }else if($count > 0 && $count < 10){
                    $str .= $this->dasar[$count] . " ".$this->satuan[$i]." ";
                }
                $n -= $this->angka[$i] * $count;
                $i++;
            }
            $str = preg_replace("/satu puluh (\w+)/i", "\\1 belas", $str);
            $str = preg_replace("/satu (ribu|ratus|puluh|belas)/i", "se\\1", $str);
        }
        return $str;
    }
}
?>
		