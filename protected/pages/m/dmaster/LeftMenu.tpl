<ul class="box">
    <li<%=$this->Page->showBagian==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('m.dmaster.Bagian')%>">Bagian / Urusan</a></li>    
    <li<%=$this->Page->showUnitKerja==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('m.dmaster.UnitKerja')%>">Unit Kerja</a></li>    
    <li<%=$this->Page->showProgram==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('m.dmaster.Program')%>">Program</a></li>    
    <li<%=$this->Page->showRekening==true?' id="submenu-active"':''%>><a href="#">Rekening</a> <!-- Active -->
        <ul>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Transaksi')%>"<%=$this->Page->showTransaksi==true?' style="color:#E01850"':''%>>Transaksi</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Kelompok')%>"<%=$this->Page->showKelompok==true?' style="color:#E01850"':''%>>Kelompok</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Jenis')%>"<%=$this->Page->showJenis==true?' style="color:#E01850"':''%>>Jenis</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Objek')%>"<%=$this->Page->showObjek==true?' style="color:#E01850"':''%>>Objek</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Rincian')%>"<%=$this->Page->showRincian==true?' style="color:#E01850"':''%>>Rincian</a></li>
        </ul>
    </li>   
    <li<%=$this->Page->showPejabat==true?' id="submenu-active"':''%>><a href="#">Pejabat</a>
        <ul>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.PenggunaAnggaran')%>"<%=$this->Page->showPenggunaAnggaran==true?' style="color:#E01850"':''%>>Pengguna Anggaran</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.KuasaPengguna')%>"<%=$this->Page->showKuasaPengguna==true?' style="color:#E01850"':''%>>Kuasa Pengguna Anggaran</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.PPK')%>"<%=$this->Page->showPPK==true?' style="color:#E01850"':''%>>PPK</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.PPTK')%>"<%=$this->Page->showPPTK==true?' style="color:#E01850"':''%>>PPTK</a></li>
        </ul>
    </li>
   <li<%=$this->Page->showLokasi==true?' id="submenu-active"':''%>><a href="#">Lokasi</a>
        <ul>
            <!--<li><a href="<%=$this->Service->constructUrl('m.dmaster.Negara')%>"<%=$this->Page->showNegara==true?' style="color:#E01850"':''%>>Negara</a></li>-->
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.DT1')%>"<%=$this->Page->showDT1==true?' style="color:#E01850"':''%>>Daerah Tingkat I</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.DT2')%>"<%=$this->Page->showDT2==true?' style="color:#E01850"':''%>>Daerah Tingkat II</a></li>
            <li><a href="<%=$this->Service->constructUrl('m.dmaster.Kecamatan')%>"<%=$this->Page->showKecamatan==true?' style="color:#E01850"':''%>>Kecamatan</a></li>
        </ul>
    </li>
   <li<%=$this->Page->showJenisPembangunan==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('m.dmaster.JenisPembangunan')%>">Jenis Pembangunan</a></li>
</ul>