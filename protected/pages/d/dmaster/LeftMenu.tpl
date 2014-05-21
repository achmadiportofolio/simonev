<ul class="box">       
    <li<%=$this->Page->showProgram==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('d.dmaster.Program')%>">Program</a></li>        
    <li<%=$this->Page->showPejabat==true?' id="submenu-active"':''%>><a href="#">Pejabat</a>
        <ul>
            <li><a href="<%=$this->Service->constructUrl('d.dmaster.PenggunaAnggaran')%>"<%=$this->Page->showPenggunaAnggaran==true?' style="color:#E01850"':''%>>Pengguna Anggaran</a></li>
            <li><a href="<%=$this->Service->constructUrl('d.dmaster.KuasaPengguna')%>"<%=$this->Page->showKuasaPengguna==true?' style="color:#E01850"':''%>>Kuasa Pengguna Anggaran</a></li>
            <li><a href="<%=$this->Service->constructUrl('d.dmaster.PPK')%>"<%=$this->Page->showPPK==true?' style="color:#E01850"':''%>>PPK</a></li>
            <li><a href="<%=$this->Service->constructUrl('d.dmaster.PPTK')%>"<%=$this->Page->showPPTK==true?' style="color:#E01850"':''%>>PPTK</a></li>
        </ul>
    </li>
    <li<%=$this->Page->showPaguDana==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('d.dmaster.PaguDana')%>">Pagu Dana Unit Kerja</a></li>        
</ul>