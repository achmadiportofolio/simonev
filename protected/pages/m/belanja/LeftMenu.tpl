<ul class="box">
    <li<%=$this->Page->showKegiatan==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('m.belanja.Kegiatan')%>">Kegiatan</a></li>            
    <li<%=$this->Page->showUraian==true?' id="submenu-active"':''%>><com:THyperLink ID="uraianAnchor" Text="Uraian"/></li>            
    <li<%=$this->Page->showRealisasi==true?' id="submenu-active"':''%>><com:THyperLink ID="realisasiAnchor" Text="Realisasi"/></li> 
</ul>