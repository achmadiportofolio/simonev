<h3 class="tit">Data Umum</h3>        
<table class="list">
    <tr>
        <td class="left" width="200">Program</td>
        <td><%=$this->dataKegiatan['nama_program']%></td>
    </tr>
    <tr>
        <td class="left">Kegiatan</td>
        <td><%=$this->dataKegiatan['nama_proyek']%></td>
    </tr>		
    <tr>
        <td class="left"><strong>Tolak Ukur</strong> => Keluaran</td>
        <td><%=$this->dataKegiatan['keluaran']%></td>
    </tr>
    <tr>
        <td class="left"><strong>Tolak Ukur</strong> => Hasil</td>
        <td><%=$this->dataKegiatan['hasil']%></td>
    </tr>
    <tr>
        <td class="left">Sifat Kegiatan</td>
        <td><%=$this->dataKegiatan['sifat_kegiatan']%></td>
    </tr>		
    <tr>
        <td class="left">Pagu Kegiatan</td>
        <td><%=$this->finance->toRupiah($this->dataKegiatan['nilai_pagu'])%></td>
    </tr>		
    <tr>
        <td class="left">Waktu Pelaksanaan</td>
        <td><%=$this->dataKegiatan['waktu_pelaksanaan']%></td>
    </tr>
</table> 