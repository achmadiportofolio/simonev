<h3 class="tit">Data Uraian</h3>		
<table class="list">
    <tr>				
        <td width="150" class="left">Kode Kegiatan</td>
        <td class="left"><%=$this->session['currentPageRealisasi']['dataUraian']['kode_proyek']%></td>
    </tr>
    <tr>				
        <td class="left">Nama Kegiatan</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['nama_proyek']%></td>
    </tr>
    <tr>				
        <td class="left">No Rekening</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['rekening']%></td>
    </tr>
    <tr>				
        <td class="left">Nama Uraian</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['nama_uraian']%></td>
    </tr>
    <tr>				
        <td class="left">Volume/Satuan</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['volume']%> (<%=$this->session['currentPageRealisasi']['dataUraian']['satuan']%>)</td>
    </tr>
    <tr>				
        <td class="left">Harga Satuan</td>
        <td><%=$this->finance->toRupiah($this->session['currentPageRealisasi']['dataUraian']['harga_satuan'])%></td>
    </tr>
    <tr>				
        <td class="left">Jumlah Pagu Uraian</td>
        <td><%=$this->finance->toRupiah($this->session['currentPageRealisasi']['dataUraian']['nilai'])%></td>
    </tr>
    <tr>				
        <td class="left">Tahun Anggaran</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['tahun_anggaran']%></td>
    </tr>
    <tr>
        <td class="left">Perusahaan</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['nama_perusahaan']%></td>
    </tr>
    <tr>
        <td class="left">Tanggal Kontrak</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['tgl_kontrak']%></td>
    </tr>
    <tr>
        <td class="left">Waktu Pelaksanaan</td>
        <td><%=$this->session['currentPageRealisasi']['dataUraian']['tgl_mulai_pelaksanaan']%> s.d <%=$this->session['currentPageRealisasi']['dataUraian']['tgl_selesai_pelaksanaan']%></td>
    </tr>
</table>		