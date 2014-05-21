<ul class="box">
    <li<%=$this->Page->showTarget==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('s.pendapatan.Target')%>">Target</a></li>            
    <li<%=$this->Page->showRealisasi==true?' id="submenu-active"':''%>><a href="<%=$this->Service->constructUrl('s.pendapatan.Realisasi')%>">Realisasi</a></li>                
</ul>